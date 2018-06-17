<?php
namespace Mill\Compiler\Changelog\Formats;

use Mill\Compiler\Changelog;
use Mill\Compiler\Traits;

class Markdown extends Json
{
    use Traits\Markdown;

    /** @var string */
    protected $output_format = Changelog::FORMAT_MARKDOWN;

    /**
     * Take compiled API documentation and convert it into a Markdown-based changelog over the life of the API.
     *
     * @return array
     */
    public function compile(): array
    {
        $markdown = '';

        $api_name = $this->config->getName();
        if (!empty($api_name)) {
            $markdown .= sprintf('# Changelog: %s', $api_name);
            $markdown .= $this->line(2);
        } else {
            $markdown .= sprintf('# Changelog', $api_name);
            $markdown .= $this->line(2);
        }

        $changelog = parent::compile();
        $changelog = array_shift($changelog);
        $changelog = json_decode($changelog, true);
        foreach ($changelog as $version => $data) {
            $markdown .= sprintf('## %s (%s)', $version, $data['_details']['release_date']);
            $markdown .= $this->line();

            if (isset($data['_details']['description'])) {
                $markdown .= sprintf('%s', $data['_details']['description']);
                $markdown .= $this->line(2);
            }

            $markdown .= '### Reference';
            $markdown .= $this->line();

            foreach ($data as $definition => $changes) {
                if ($definition === '_details') {
                    continue;
                }

                $markdown .= sprintf('#### %s', ucwords($definition));
                $markdown .= $this->line();

                foreach ($changes as $type => $changesets) {
                    $markdown .= sprintf('##### %s', ucwords($type));
                    $markdown .= $this->line();

                    foreach ($changesets as $changeset) {
                        $markdown .= $this->getChangesetMarkdown($changeset);
                    }

                    $markdown .= $this->line();
                }
            }
        }

        return [$markdown];
    }

    /**
     * Get Markdown syntax for a given changeset.
     *
     * @param array|string $changeset
     * @param int $tab
     * @return string
     */
    private function getChangesetMarkdown($changeset, int $tab = 0): string
    {
        $markdown = '';
        if (!is_array($changeset)) {
            $markdown .= $this->tab($tab);
            $markdown .= sprintf('- %s', $changeset);
            $markdown .= $this->line();
            return $markdown;
        }

        foreach ($changeset as $change) {
            if (is_array($change)) {
                foreach ($change as $item) {
                    if (is_array($item)) {
                        $markdown .= $this->getChangesetMarkdown($item, $tab + 1);
                        continue;
                    }

                    $markdown .= $this->tab($tab + 1);
                    $markdown .= sprintf('- %s', $item);
                    $markdown .= $this->line();
                }

                continue;
            }

            $markdown .= $this->tab($tab);
            $markdown .= sprintf('- %s', $change);
            $markdown .= $this->line();
        }

        return $markdown;
    }
}