<?php

namespace Dicker\Validators;

interface ValidatorInterface
{
    /**
     * @return \Closure
     */
    public function callback();
}
