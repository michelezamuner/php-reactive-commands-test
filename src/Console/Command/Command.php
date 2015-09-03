<?php
namespace Update\Console\Command;

use React\EventLoop\LoopInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Update\Process\Runner\Factory as RunnerFactory;

abstract class Command extends SymfonyCommand
{
    protected $loop = null;
    
    public function hasLoop()
    {
        return $this->loop !== null;
    }
    
    public function getLoop()
    {
        return $this->loop;
    }
    
    public function setLoop(LoopInterface $loop)
    {
        $this->loop = $loop;
        return $this;
    }
    
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $runnerFactory = new RunnerFactory();
        $runnerFactory->createParallel()
            ->addProcesses($this->getProcesses($input, $output))
            ->run();
    }
    
    abstract public function getProcesses(InputInterface $input, OutputInterface $output);
    
    protected function configure()
    {
        $this->setName($this->getCommandName());
    }
    
    abstract protected function getCommandName();
}