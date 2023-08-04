<?php

namespace JobMetric\StateMachine\Interfaces;

interface BaseStateMachine
{
    /**
     * before change state in current field model
     *
     * @param mixed $from
     * @param mixed $to
     *
     * @return void
     */
    function before(mixed $from, mixed $to): void;

    /**
     * after change state in current field model
     *
     * @param mixed $from
     * @param mixed $to
     *
     * @return void
     */
    function after(mixed $from, mixed $to): void;
}
