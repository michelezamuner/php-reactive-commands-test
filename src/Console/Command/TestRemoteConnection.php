<?php
namespace Update\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Update\Process\ProcessInterface;
use Update\Process\Runner;
use Update\Process\Builder as ProcessBuilder;

class TestRemoteConnection extends Command
{

    public function getProcesses(InputInterface $input, OutputInterface $output)
    {
        return array($this->createProcess($input, $output));
    }
    
    public function createProcess(InputInterface $input, OutputInterface $output)
    {
        $conf = $this->getApplication()->getConfiguration();
        $host = $conf->get('remote/host');
        $dbHost = $conf->get('remote/database/host');
        $dbUser = $conf->get('remote/database/username');
        $dbPass = $conf->get('remote/database/password');
        $dbName = $conf->get('remote/database/name');
    
        $cmd = "ping www.google.it";
//         $cmd = "ssh $host 'mysql -h$dbHost -u$dbUser -p$dbPass --database $dbName -e \"SHOW TABLES;\"'";
//         $cmd = 'rsync -av --update --dry-run myho:/home/web/staging.myho.it/httpdocs/ .';
        
        $processBuilder = new ProcessBuilder();
        $process = $processBuilder
            ->setName($this->getCommandName())
            ->setCmd($cmd)
            ->setWait(1)
            ->setWaitForOutput(false)
            ->setRetry(false)
            ->setMaxRetries(10)
            ->createProcess();
        
        $process->on(ProcessInterface::EVENT_BEFORE_START, function(ProcessInterface $process) use($output) {
            $msg = '[1:S]';
            $output->write($msg);
        });
        $process->on(ProcessInterface::EVENT_AFTER_START, function(ProcessInterface $process) use($output) {
            $process->stdout->on('data', function($data) use($output) {
                $msg = '[1]';
                $output->write($msg);
            });
        });
        $process->on(ProcessInterface::EVENT_ELAPSED, function(ProcessInterface $process) use($output){
            $msg = '[1:'.$process->getElapsed().']';
            $output->write($msg);
        });
        $process->on(ProcessInterface::EVENT_HANGING, function(ProcessInterface $process) use($output) {
            $msg = '[1:H]';
            $output->write($msg);
        });
        $process->on(ProcessInterface::EVENT_RETRY, function(ProcessInterface $process) use($output) {
            $msg = '[1:R]';
            $output->write($msg);
        });
        $process->on(ProcessInterface::EVENT_MAX_RETRY, function(ProcessInterface $process) use($output) {
            $msg = '[1:M]';
            $output->write($msg);
        });
        $process->on(ProcessInterface::EVENT_TERMINATED, function(ProcessInterface $process) use($output) {
            $msg = '[1:T]';
            $output->write($msg);
        });
        
        $process->on(ProcessInterface::EVENT_ERROR, function(ProcessInterface $sender) use($output, $process) {
            /**
             * TODO: Since this is a test, if another process raises
             * an error, simply interrupt this process as well
             */
            if (!$sender->equals($process)) {
                $process->terminate();
                $msg = "[1:E]";
                $output->write($msg);
            }
        });
        
        return $process;
    }
    
    protected function getCommandName()
    {
        return 'test-remote-connection';
    }
}