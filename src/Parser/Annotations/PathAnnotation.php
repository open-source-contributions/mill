<?php
namespace Mill\Parser\Annotations;

use Mill\Container;
use Mill\Parser\Annotation;
use Mill\Parser\Version;

class PathAnnotation extends Annotation
{
    const REQUIRES_VISIBILITY_DECORATOR = true;
    const SUPPORTS_DEPRECATION = true;

    const ARRAYABLE = [
        'path'
    ];

    /** @var string URI path that this annotation represents. */
    protected $path;

    /**
     * {@inheritdoc}
     */
    protected function parser(): array
    {
        return [
            'path' => trim($this->docblock)
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function interpreter(): void
    {
        $this->path = $this->required('path');
    }

    /**
     * {@inheritdoc}
     */
    public static function hydrate(array $data = [], Version $version = null)
    {
        /** @var PathAnnotation $annotation */
        $annotation = parent::hydrate($data, $version);
        $annotation->setPath($data['path']);

        $annotation->setAliased($data['aliased']);

        $aliases = [];
        foreach ($data['aliases'] as $alias) {
            $aliases[] = PathAnnotation::hydrate(array_merge([
                'class' => $data['class'],
                'method' => $data['method']
            ], $alias));
        }

        $annotation->setAliases($aliases);

        return $annotation;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return PathAnnotation
     */
    public function setPath(string $path): self
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return string
     */
    public function getCleanPath(): string
    {
        $path = preg_replace('/[@#+*!~]((\w|_)+)(\/|$)/', '{$1}$3', $this->getPath());

        // If we have any path param translations configured, let's process them.
        $translations = Container::getConfig()->getPathParamTranslations();
        foreach ($translations as $from => $to) {
            $path = str_replace('{' . $from . '}', '{' . $to . '}', $path);
        }

        return $path;
    }

    /**
     * @param PathParamAnnotation $param
     * @return bool
     */
    public function doesPathHaveParam(PathParamAnnotation $param): bool
    {
        return strpos($this->getCleanPath(), '{' . $param->getField() . '}') !== false;
    }

    /**
     * Convert the parsed annotation into an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        $arr = parent::toArray();
        $arr['aliased'] = $this->isAliased();
        $arr['aliases'] = [];

        /** @var Annotation $alias */
        foreach ($this->getAliases() as $alias) {
            $arr['aliases'][] = $alias->toArray();
        }

        ksort($arr);

        return $arr;
    }
}
