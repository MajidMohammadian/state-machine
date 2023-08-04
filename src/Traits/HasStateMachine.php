<?php

namespace JobMetric\StateMachine\Traits;

use Closure;
use JobMetric\StateMachine\Exceptions\StateMachineNotAllowTransitionException;
use JobMetric\StateMachine\Interfaces\BaseStateMachine;
use Str;
use Throwable;

/**
 * @method static retrieved(Closure $param)
 */
trait HasStateMachine
{
    protected array $stateMachines = [];

    /**
     * boot has state machine
     *
     * @return void
     */
    public static function bootHasStateMachine(): void
    {
        static::retrieved(function ($model) {
            $classPart = explode('\\', self::class);
            $selfClass = end($classPart);

            $className = "\\App\\StateMachines\\$selfClass\\Init{$selfClass}StateMachine";
            if (class_exists($className)) {
                $object = resolve($className);
                $object->init($model);
            }
        });

    }

    /**
     * allow transition field
     *
     * @param string $field
     * @param mixed $from
     * @param mixed $to
     * @param callable|string $callable
     *
     * @return void
     */
    public function allowTransition(string $field, mixed $from, mixed $to, callable|string $callable = 'Default'): void
    {
        if ($callable == 'Default') {
            $this->stateMachines[$field][] = [$from, $to];
        } else {
            $this->stateMachines[$field][] = [$from, $to, $callable];
        }
    }

    /**
     * transition to state
     *
     * @param mixed $to
     * @param string $field
     *
     * @return bool
     * @throws Throwable
     */
    public function transitionTo(mixed $to, string $field = 'status'): bool
    {
        $currentState = $this->{$field};

        $checkTransitions = $this->validationTransitions($field, $currentState, $to);

        if ($checkTransitions === false) {
            throw new StateMachineNotAllowTransitionException(self::class, $field, $currentState, $to);
        }

        $classPart = explode('\\', self::class);
        $selfClass = end($classPart);

        /**
         * @var $object BaseStateMachine
         */
        $object = null;

        $className = "\\App\\StateMachines\\$selfClass\\" . $selfClass . Str::studly($field) . $checkTransitions . "StateMachine";
        if (class_exists($className)) {
            $object = resolve($className);
        } else {
            if ($checkTransitions != 'Default') {
                $className = "\\App\\StateMachines\\$selfClass\\" . $selfClass . Str::studly($field) . "DefaultStateMachine";
                if (class_exists($className)) {
                    $object = resolve($className);
                }
            }
        }

        if ($object) {
            $object->before($currentState, $to);
        }

        $this->{$field} = $to;
        $this->save();

        if ($object) {
            $object->after($currentState, $to);
        }

        return true;
    }

    /**
     * check validation transition
     *
     * @param string $field
     * @param mixed $from
     * @param mixed $to
     *
     * @return bool|callable|string
     */
    private function validationTransitions(string $field, mixed $from, mixed $to): bool|callable|string
    {
        foreach ($this->stateMachines[$field] as $transition) {
            if ($transition[0] == $from && $transition[1] == $to) {
                return $transition[2] ?? 'Default';
            }
        }

        return false;
    }
}
