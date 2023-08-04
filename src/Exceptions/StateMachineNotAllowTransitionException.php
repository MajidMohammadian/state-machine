<?php

namespace JobMetric\StateMachine\Exceptions;

use Exception;
use Throwable;

class StateMachineNotAllowTransitionException extends Exception
{
    public function __construct(string $className, string $field, mixed $from, mixed $to, int $code = 400, ?Throwable $previous = null)
    {
        if(gettype($from) == 'object') {
            $from = $from->value;
        }

        if(gettype($to) == 'object') {
            $to = $to->value;
        }

        $message = "Transferring field $field in class $className from state $from to state $to is not possible";

        parent::__construct($message, $code, $previous);
    }
}
