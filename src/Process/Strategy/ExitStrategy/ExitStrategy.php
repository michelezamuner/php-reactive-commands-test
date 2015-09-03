<?php
namespace Update\Process\Strategy\ExitStrategy;

use Update\Process\Strategy\Strategy;
use Update\Process\ProcessInterface;

abstract class ExitStrategy extends Strategy
{
    protected function terminate()
    {
        $this->process->removeTimer();
        $this->process->send(ProcessInterface::EVENT_TERMINATED);
    }
}