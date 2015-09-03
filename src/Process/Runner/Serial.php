<?php
namespace Update\Process\Runner;

use Update\Process\ProcessInterface;

class Serial extends Runner
{
    public function doRun()
    {
        $tot = count($this->processes);
        // Every process, except for the last one, after termination
        // runs the next one
        for ($i = 0; $i < $tot - 1; $i++) {
            $current = $this->processes[$i];
            $next = $this->processes[$i+1];
            $loop = $this->loop;
            $current->on(ProcessInterface::EVENT_EXIT, function($exitCode, $termSignal) use($next, $loop) {
                $next->start($loop);
            });
        }
        
        // Run first process
        $first = $this->processes[0];
        $first->start($this->loop);
    }
}