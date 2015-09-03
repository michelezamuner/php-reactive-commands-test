<?php
namespace Update\Process\Strategy\ExitStrategy;

use Update\Process\Process;

class Retry extends ExitStrategy implements ExitStrategyInterface
{
    protected $retries = 0;
    protected $maxRetries = null;
    
    public function __construct(Process $process, $maxRetries)
    {
        parent::__construct($process);
        $this->maxRetries = $maxRetries;
    }
    
    public function onExit(/*$exitCode, $termSignal*/)
    {
        $p = $this->process;
        if ($p->isHanged() && $this->retries < $this->maxRetries) {
            $p->send(Process::EVENT_RETRY);
            $this->retries++;
            $p->start($p->getLoop());
        } else {
            if ($p->isHanged()) $p->send(Process::EVENT_MAX_RETRY);
            $this->terminate();
//             $p->removeTimer();
//             $p->send(Process::EVENT_TERMINATED);
        }
    }
}