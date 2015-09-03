<?php
namespace Update\Process\Strategy;

use Update\Process\Process;

abstract class Strategy
{
    protected $process = null;
    
    public function __construct(Process $process)
    {
        $this->process = $process;
    }
    
    public function getProcess()
    {
        return $this->process;
    }
}