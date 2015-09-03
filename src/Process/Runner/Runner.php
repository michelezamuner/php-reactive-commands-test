<?php
namespace Update\Process\Runner;

use React\EventLoop\Factory as LoopFactory;
use React\EventLoop\Timer\TimerInterface;
use Update\Process\ProcessInterface;
use Update\Process\DuplicatedProcessException;

abstract class Runner
{
    protected $loop = null;
    protected $processes = array();
    protected $notifiedProcesses = array();
    protected $names = array();
    
    public function __construct()
    {
        $this->loop = LoopFactory::create();
    }
    
    public function getProcesses()
    {
        return $this->processes;
    }
    
    public function addProcess(ProcessInterface $process)
    {
        if (in_array($process->getName(), $this->names))
            throw new DuplicatedProcessException();
        $this->names[] = $process->getName();
        $this->processes[] = $process;
        return $this;
    }
    
    public function addProcesses(array $processes)
    {
        foreach ($processes as $process)
            $this->addProcess($process);
        return $this;
    }
    
    public function run()
    {
        $self = $this;
        
        // Spread all EVENT_ERROR's to every other process
        foreach ($this->processes as $process) {
            $process->on(ProcessInterface::EVENT_ERROR, function(ProcessInterface $sender) use($self) {
                foreach ($self->getProcesses() as $process) {
                    if (!$process->equals($sender) && $process->isRunning())
                        $self->notify($process, $sender);
                }
            });
        }
        
        $this->loop->addTimer(0.001, function(TimerInterface $timer) use($self) {
            $self->doRun();
        });
        $this->loop->run();
    }
    
    public function notify(ProcessInterface $process, ProcessInterface $sender)
    {
        // If process hasn't already been notified, do it now
        if (!in_array($process->getName(), $this->notifiedProcesses)) {
            $this->notifiedProcesses[] = $process->getName();
            $process->emit(ProcessInterface::EVENT_ERROR, array($sender));
        }
    }
}