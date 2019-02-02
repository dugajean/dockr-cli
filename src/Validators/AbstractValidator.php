<?php

namespace Dicker\Validators;

abstract class AbstractValidator
{
    /**
     * @var string
     */
    protected $field;

    /**
     * ValidateNotEmpty constructor.
     *
     * @param $field
     *
     * @return void
     */
    public function __construct($field)
    {
        $this->field = $field;
    }

    /**
     * @return \Closure
     */
    abstract public function callback();
}
