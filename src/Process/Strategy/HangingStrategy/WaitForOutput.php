<?php
namespace Update\Process\Strategy\HangingStrategy;

use Update\Process\Process;

class WaitForOutput extends Wait implements HangingStrategyInterface
{
    protected $outputStarted = false;
    
    public function __construct(Process $process)
    {
        parent::__construct($process);
        $outputStarted = &$this->outputStarted;
        
        // Reset outputStarted since the process could be restarted
        $process->on(Process::EVENT_BEFORE_START, function(Process $process) use(&$outputStarted) {
            $outputStarted = false;
        });
        // As soon as some input comes in, set $outputStarted to true
        $process->on(Process::EVENT_AFTER_START, function(Process $process) use(&$outputStarted) {
            $process->stdout->on('data', function($data) use(&$outputStarted) {
                if (!$outputStarted && trim($data) !== '') $outputStarted = true;
            });
        });
    }
    
    public function isOutputStarted()
    {
        return $this->outputStarted;
    }
    
    public function isHanging()
    {
        return $this->isBasicHanging() && !$this->outputStarted;
    }
}