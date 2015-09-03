<?php
namespace Update\Process\Strategy\HangingStrategy;

use Update\Process\Strategy\Strategy;

abstract class Wait extends Strategy
{
    protected function isBasicHanging()
    {
        return $this->process->isRunning()
            && $this->process->getElapsed() >= $this->process->getWait()
        ;
    }
}