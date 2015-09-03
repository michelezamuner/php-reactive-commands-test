<?php
namespace Update\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Update\Process\Runner\Factory as RunnerFactory;

class Server extends Command
{
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getApplication();
        $runnerFactory = new RunnerFactory();
        
        $runnerFactory->createParallel()
            ->addProcesses(
	            $app->find('test-remote-connection')
                    ->getProcesses($input, $output)
            )
            ->addProcesses(
                $app->find('test-download')
                    ->getProcesses($input, $output)
            )
            ->run()
        ;
    }
    
    protected function configure()
    {
        $this->setName('server');
    }
}