<?php
namespace Mill\Compiler\Specification;

use Mill\Application;
use Mill\Compiler;
use Mill\Parser\Annotations\ErrorAnnotation;
use Mill\Parser\Annotations\PathParamAnnotation;
use Mill\Parser\Annotations\ReturnAnnotation;
use Mill\Parser\Annotations\ScopeAnnotation;
use Mill\Parser\Representation\Documentation;
use Mill\Parser\Resource\Action;

class ApiBlueprint extends Compiler\Specification
{
    /** @var string */
    protected $current_version;

    /** @var array */
    protected $transposed_actions = [];

    /** @var array */
    protected $transposed_representations = [];

    /**
     * Take compiled API documentation and create a API Blueprint specification.
     *
     * @psalm-suppress PossiblyFalseOperand
     * @psalm-suppress InvalidScalarArgument
     * @psalm-suppress PossiblyUndefinedVariable
     * @psalm-suppress PossiblyUndefinedArrayOffset
     * @throws \Exception
     */
    public function compile(): void
    {
        parent::compile();

        $group_excludes = $this->config->getCompilerGroupExclusions();
        $resources = $this->getResources();

        foreach ($resources as $version => $groups) {
            // Process resource groups.
            /** @var array $data */
            foreach ($groups as $group => $data) {
                // If this group has been designated in the config file to be excluded, then exclude it.
                if (in_array($group, $group_excludes)) {
                    continue;
                }

                $contents = sprintf('# Group %s', $group);
                $contents .= $this->line();

                // Since there are instances where the same resource might be used with multiple endpoints, and on the
                // same group, we need to abstract out the resource and action concatenation so we can compile unique
                // resource and action headers for each resource action.
                //
                // It would be nice to clean this code up at some point as it's a... bit... messy.
                $resource_contents = $this->transposed_actions[$version][$group];
                foreach ($resource_contents as $identifier => $resource) {
                    $contents .= sprintf('## %s', $identifier);
                    $contents .= $this->line();

                    // Sort the resources so they're alphabetical.
                    ksort($resource['actions']);

                    foreach ($resource['actions'] as $action_identifier => $markdown) {
                        $contents .= $this->line();
                        $contents .= sprintf('### %s', $action_identifier);
                        $contents .= $this->line();

                        $contents .= $markdown;
                    }

                    $contents .= $this->line();
                }

                $contents = trim($contents);
                $this->specifications[$version]['groups'][$group] = $contents;
            }

            // Process representation data structures.
            if (isset($this->transposed_representations[$version])) {
                $this->specifications[$version]['structures'] = $this->transposed_representations[$version];
            } else {
                $this->specifications[$version]['structures'] = [];
            }

            // Process the combined file.
            $this->specifications[$version]['combined'] = $this->processCombinedFile(
                $this->specifications[$version]['groups'],
                $this->specifications[$version]['structures']
            );
        }
    }

    /**
     * {{@inheritdoc}}
     */
    protected function transposeAction(
        string $version,
        string $group,
        string $identifier,
        Action\Documentation $action
    ): void {
        $this->current_version = $version;

        $specification = '';

        $description = $action->getDescription();
        if (!empty($description)) {
            $specification .= $description;
            $specification .= $this->line(2);
        }

        $specification .= $this->processScopes($action);
        $specification .= $this->processParameters($action);
        $specification .= $this->processRequest($action);

        $coded_responses = [];
        /** @var ReturnAnnotation|ErrorAnnotation $response */
        foreach ($action->getResponses() as $response) {
            $coded_responses[$response->getHttpCode()][] = $response;
        }

        ksort($coded_responses);

        foreach ($coded_responses as $http_code => $responses) {
            $specification .= $this->processResponses($action, $http_code, $responses);
        }

        $resource_key = sprintf('%s [%s]', $group, $action->getPath()->getCleanPath());
        if (!isset($this->transposed_actions[$version][$group][$resource_key])) {
            $this->transposed_actions[$version][$group][$resource_key] = [
                'actions' => []
            ];
        }

        $action_key = sprintf('%s [%s]', $action->getLabel(), $action->getMethod());
        $this->transposed_actions[$version][$group][$resource_key]['actions'][$action_key] = $specification;
    }

    /**
     * {{@inheritdoc}}
     */
    protected function transposeRepresentation(string $version, Documentation $representation): void
    {
        $representations = $this->getRepresentations($version);
        if (empty($representations)) {
            return;
        }

        $this->current_version = $version;

        foreach ($representations as $representation) {
            $fields = $representation->getExplodedContentDotNotation();
            if (empty($fields)) {
                continue;
            }

            $identifier = $representation->getLabel();

            $specification = sprintf('## %s', $identifier);
            $specification .= $this->line();

            $specification .= $this->processMSON($fields, 0);

            $specification = trim($specification);
            $this->transposed_representations[$version][$identifier] = $specification;
        }
    }

    /**
     * Process an action and compile a scopes description.
     *
     * @param Action\Documentation $action
     * @return string
     */
    protected function processScopes(Action\Documentation $action): string
    {
        $scopes = $action->getScopes();
        if (empty($scopes)) {
            return '';
        }

        $strings = [];
        /** @var ScopeAnnotation $scope */
        foreach ($scopes as $scope) {
            $strings[] = $scope->getScope();
        }

        $blueprint = sprintf(
            'This action requires a bearer token with the %s scope%s.',
            '`' . implode(', ', $strings) . '`',
            (count($strings) > 1) ? 's' : ''
        );

        $blueprint .= $this->line(2);

        return $blueprint;
    }

    /**
     * Process an action and compile a parameters Blueprint.
     *
     * @param Action\Documentation $action
     * @return string
     */
    protected function processParameters(Action\Documentation $action): string
    {
        $params = $action->getPathParameters();
        if (empty($params)) {
            return '';
        }

        $blueprint = '+ Parameters';
        $blueprint .= $this->line();

        /** @var PathParamAnnotation $param */
        foreach ($params as $param) {
            /** @var array $values */
            $values = $param->getValues();
            $type = $this->convertTypeToCompatibleType($param->getType());

            $blueprint .= $this->tab();
            $blueprint .= sprintf(
                '- `%s` (%s, required) - %s',
                $param->getField(),
                $type,
                $param->getDescription()
            );

            $blueprint .= $this->line();

            if (!empty($values)) {
                $blueprint .= $this->tab(2);
                $blueprint .= '+ Members';
                $blueprint .= $this->line();

                foreach ($values as $value => $value_description) {
                    $blueprint .= $this->tab(3);
                    $blueprint .= sprintf(
                        '+ `%s`%s',
                        $value,
                        (!empty($value_description)) ? sprintf(' - %s', $value_description) : ''
                    );

                    $blueprint .= $this->line();
                }
            }
        }

        return $blueprint;
    }

    /**
     * Process an action and compile a Request Blueprint.
     *
     * @param Action\Documentation $action
     * @return string
     */
    protected function processRequest(Action\Documentation $action): string
    {
        $params = $action->getExplodedAllQueryParameterDotNotation();
        if (empty($params)) {
            return '';
        }

        $blueprint = '+ Request';
        $blueprint .= $this->line();

        $blueprint .= $this->tab();
        $blueprint .= '+ Attributes';
        $blueprint .= $this->line();
        $blueprint .= $this->processMSON($params, 2);

        return $blueprint;
    }

    /**
     * Process an action and response array and compile a Response Blueprint.
     *
     * @param Action\Documentation $action
     * @param string $http_code
     * @param array $responses
     * @return string
     * @throws \Exception If a non-200 response is missing a description.
     */
    protected function processResponses(Action\Documentation $action, string $http_code, array $responses = []): string
    {
        $http_code = substr($http_code, 0, 3);

        $blueprint = '+ Response ' . $http_code . ' (' . $action->getContentType($this->current_version) . ')';
        $blueprint .= $this->line();

        $multiple_responses = count($responses) > 1;

        // API Blueprint doesn't have support for multiple responses of the same HTTP code, so let's mash them down
        // together, but document to the developer what's going on.
        if ($multiple_responses) {
            // @todo Blueprint validation doesn't seem to like 200 responses with descriptions. Just skip for now.
            if (!in_array($http_code, [201, 204])) {
                $response_count = (new \NumberFormatter('en', \NumberFormatter::SPELLOUT))->format(count($responses));

                $blueprint .= $this->tab();
                $blueprint .= sprintf('There are %s ways that this status code can be encountered:', $response_count);
                $blueprint .= $this->line();

                /** @var ReturnAnnotation|ErrorAnnotation $response */
                foreach ($responses as $response) {
                    $description = $response->getDescription();
                    $description = (!empty($description)) ? $description : 'Standard request.';
                    $blueprint .= $this->tab(2);
                    $blueprint .= sprintf(' * %s', $description);
                    if ($response instanceof ErrorAnnotation) {
                        $error_code = $response->getErrorCode();
                        if ($error_code) {
                            $blueprint .= sprintf(' Unique error code: %s', $error_code);
                        }
                    }

                    $blueprint .= $this->line();
                }
            }
        }

        /** @var ReturnAnnotation|ErrorAnnotation $response */
        $response = array_shift($responses);
        $representation = $response->getRepresentation();
        $representations = $this->getRepresentations($this->current_version);
        if ($representation && isset($representations[$representation])) {
            /** @var Documentation $docs */
            $docs = $representations[$representation];
            $fields = $docs->getExplodedContentDotNotation();
            if (!empty($fields)) {
                $blueprint .= $this->tab();

                $attribute_type = $docs->getLabel();
                if ($response instanceof ReturnAnnotation) {
                    if ($response->getType() === 'collection') {
                        $attribute_type = sprintf('array[%s]', $attribute_type);
                    }
                }

                $blueprint .= sprintf('+ Attributes (%s)', $attribute_type);
                $blueprint .= $this->line();
            }
        }

        return $blueprint;
    }

    /**
     * Recursively process an array of representation fields.
     *
     * @param array $fields
     * @param int $indent
     * @return string
     */
    private function processMSON(array $fields = [], int $indent = 2): string
    {
        $blueprint = '';

        /** @var array $field */
        foreach ($fields as $field_name => $field) {
            $blueprint .= $this->tab($indent);

            $data = [];
            if (isset($field[Application::DOT_NOTATION_ANNOTATION_DATA_KEY])) {
                /** @var array $data */
                $data = $field[Application::DOT_NOTATION_ANNOTATION_DATA_KEY];
                $type = $this->convertTypeToCompatibleType(
                    $data['type'],
                    (isset($data['subtype'])) ? $data['subtype'] : false
                );

                $sample_data = $this->convertSampleDataToCompatibleDataType($data['sample_data'], $type);

                $description = $data['description'];
                if (!empty($data['scopes'])) {
                    // If this description doesn't end with punctuation, add a period before we display a list of
                    // required authentication scopes.
                    $description .= (!in_array(substr($description, -1), ['.', '!', '?'])) ? '.' : '';

                    $strings = [];
                    foreach ($data['scopes'] as $scope) {
                        $strings[] = $scope['scope'];
                    }

                    $description .= sprintf(
                        ' This data requires a bearer token with the %s scope%s.',
                        '`' . implode(', ', $strings) . '`',
                        (count($strings) > 1) ? 's' : ''
                    );
                }

                $blueprint .= sprintf(
                    '- `%s`%s (%s%s%s) - %s',
                    $field_name,
                    ($sample_data !== false) ? sprintf(': `%s`', (string)$sample_data) : '',
                    $type,
                    (isset($data['required']) && $data['required']) ? ', required' : '',
                    ($data['nullable']) ? ', nullable' : '',
                    $description
                );

                $blueprint .= $this->line();

                // Only enum's support options/members.
                if (($data['type'] === 'enum' || (isset($data['subtype']) && $data['subtype'] === 'enum')) &&
                    !empty($data['values'])
                ) {
                    $blueprint .= $this->tab($indent + 1);
                    $blueprint .= '+ Members';
                    $blueprint .= $this->line();

                    foreach ($data['values'] as $value => $value_description) {
                        $blueprint .= $this->tab($indent + 2);
                        $blueprint .= sprintf(
                            '+ `%s`%s',
                            $value,
                            (!empty($value_description)) ? sprintf(' - %s', $value_description) : ''
                        );

                        $blueprint .= $this->line();
                    }
                }
            } else {
                $blueprint .= sprintf('- `%s` (object)', $field_name);
                $blueprint .= $this->line();
            }

            // Process any exploded dot notation children of this field.
            unset($field[Application::DOT_NOTATION_ANNOTATION_DATA_KEY]);
            if (!empty($field)) {
                // If this is an array, and has a subtype of object, we should indent a bit so we can properly render
                // out the array objects.
                if (!empty($data) && isset($data['subtype']) && $data['subtype'] === 'object') {
                    $blueprint .= $this->tab($indent + 1);
                    $blueprint .= ' - (object)';
                    $blueprint .= $this->line();

                    $blueprint .= $this->processMSON($field, $indent + 2);
                } else {
                    $blueprint .= $this->processMSON($field, $indent + 1);
                }
            }
        }

        return $blueprint;
    }

    /**
     * Given an array of resource groups, and representation structures, build a combined API Blueprint file.
     *
     * @param array $groups
     * @param array $structures
     * @return string
     */
    protected function processCombinedFile(array $groups = [], array $structures = []): string
    {
        $blueprint = 'FORMAT: 1A';
        $blueprint .= $this->line(2);

        $api_name = $this->config->getName();
        if (!empty($api_name)) {
            $blueprint .= sprintf('# %s', $api_name);
            $blueprint .= $this->line();

            $blueprint .= sprintf("This is the API Blueprint file for %s.", $api_name);
            $blueprint .= $this->line(2);
        }

        if (!empty($groups)) {
            $blueprint .= implode($this->line(2), $groups);
        }

        if (!empty($structures)) {
            if (!empty($groups)) {
                $blueprint .= $this->line(2);
            }

            ksort($structures);

            $blueprint .= '# Data Structures';
            $blueprint .= $this->line();

            $blueprint .= implode($this->line(2), $structures);
        }

        $blueprint = trim($blueprint);

        return $blueprint;
    }

    /**
     * Convert a Mill-supported documentation into an API Blueprint-compatible type.
     *
     * @link https://github.com/apiaryio/mson/blob/master/MSON%20Specification.md#2-types
     * @param string $type
     * @param false|string $subtype
     * @return string
     */
    private function convertTypeToCompatibleType(string $type, $subtype = false): string
    {
        switch ($type) {
            case 'enum':
                return 'enum[string]';
                break;

            case 'float':
            case 'integer':
                return 'number';
                break;

            // API Blueprint doesn't have support for dates, timestamps, or paths, but we still want to keep that
            // metadata in our documentation (because they might be useful if you're using Mill as an API to display
            // your documentation in some other format), so just convert these on the fly to strings so they're able
            // pass blueprint validation.
            case 'date':
            case 'datetime':
            case 'timestamp':
            case 'uri':
                return 'string';
                break;

            case 'array':
                if ($subtype) {
                    $representation = $this->getRepresentation($subtype, $this->current_version);
                    if ($representation) {
                        return 'array[' . $representation->getLabel() . ']';
                    } elseif ($subtype !== 'object') {
                        return 'array[' . $this->convertTypeToCompatibleType($subtype) . ']';
                    }
                }

                return 'array';
                break;

            default:
                $representation = $this->getRepresentation($type, $this->current_version);
                if ($representation) {
                    return $representation->getLabel();
                }
                break;
        }

        return $type;
    }
}
