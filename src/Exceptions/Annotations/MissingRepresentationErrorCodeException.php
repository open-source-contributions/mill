<?php
namespace Mill\Exceptions\Annotations;

use Mill\Exceptions\BaseException;

class MissingRepresentationErrorCodeException extends BaseException
{
    use AnnotationExceptionTrait;

    public static function create(
        string $representation,
        string $class,
        string $method
    ): MissingRepresentationErrorCodeException {
        $message = sprintf(
            'The `%s` error representation on `@api-throws %s` in %s::%s is missing an error code, but is required ' .
                'to have one in your config file.',
            $representation,
            $representation,
            $class,
            $method
        );

        $exception = new self($message);
        $exception->docblock = $representation;
        $exception->class = $class;
        $exception->method = $method;

        return $exception;
    }
}
