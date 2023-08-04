<?php

namespace JobMetric\StateMachine\Interfaces;

interface InitStateMachine
{
    /**
     * initialize state machine
     *
     * @param $model
     *
     * @return void
     */
    public function init($model): void;
}
