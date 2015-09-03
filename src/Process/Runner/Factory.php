<?php
namespace Update\Process\Runner;

class Factory
{
    public function createParallel()
    {
        return new Parallel();
    }
    
    public function createSerial()
    {
        return new Serial();
    }
}