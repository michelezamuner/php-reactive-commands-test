<?php
namespace Update\Process\Runner;

class Parallel extends Runner
{
    public function doRun()
    {
        foreach ($this->processes as $process)
            $process->start($this->loop);
    }
}