<?php

namespace Yonna\Validator;

use Closure;

abstract class AbstractValidator
{

    private array $error = [];

    /**
     * @return string
     */
    public function getError(): string
    {
        if (empty($this->error)) {
            return '';
        }
        return implode('; ', $this->error);
    }

    /**
     * @param string $error
     */
    protected function error(string $error): void
    {
        $error && $this->error[] = $error;
    }

    /**
     * build a checker
     * @return $this
     */
    protected static function createChecker()
    {
        return new static();
    }

    /**
     * @param Closure $failCallback
     */
    protected function callback(Closure $failCallback): void
    {
        if (!empty($this->error)) {
            $failCallback(self::getError());
        }
    }


}