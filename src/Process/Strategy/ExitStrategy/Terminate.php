<?php
namespace Update\Process\Strategy\ExitStrategy;

class Terminate extends ExitStrategy implements ExitStrategyInterface
{
    public function onExit(/*$exitCode, $termSignal*/)
    {
        $this->terminate();
//         $this->process->removeTimer();
//         $p = $this->process;
    }
}