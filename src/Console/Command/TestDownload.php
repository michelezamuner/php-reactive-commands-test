<?php
namespace Update\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Update\Process\ProcessInterface;
use Update\Process\Runner\Factory as RunnerFactory;
use Update\Process\Builder as ProcessBuilder;
use Update\Helper;

class TestDownload extends Command
{
    public function getProcesses(InputInterface $input, OutputInterface $output)
    {
        $processes = array();
        $conf = $this->getApplication()->getConfiguration();
        $i = 2;
        foreach ($conf->get('download/paths') as $path)
            $processes[] = $this->createProcess($input, $output, $path['remote'], $path['local'], $i++);
        return $processes;
    }
    
    protected function createProcess(InputInterface $input, OutputInterface $output, $remote, $local, $name)
    {
        $conf = $this->getApplication()->getConfiguration();
        $host = $conf->get('remote/host');
        $remoteBase = $conf->get('remote/path');
        $localBase = $conf->get('local/path');
        $excludes = implode(' ', array_map(function($exclude) {
            return "--exclude \"$exclude\"";
        }, $conf->get('download/excludes')));
        
        $remote = Helper::resolvePath("$remoteBase$remote");
        $local = Helper::resolvePath("$localBase$local");
        if (empty($local)) $local = '.';
        
//         $cmd = "rsync -av --update --no-links --dry-run $excludes $host:$remote $local";
        $cmd = "ping www.google.it";
        
        $processBuilder = new ProcessBuilder();
        $process = $processBuilder
            ->setName($name)
            ->setCmd($cmd)
            ->setWait(2)
            ->setWaitForOutput(false)
            ->setRetry(false)
            ->createProcess();
        
        $process->on(ProcessInterface::EVENT_BEFORE_START, function(ProcessInterface $process) use($output, $name) {
            $msg = "[$name:S]";
            $output->write($msg);
        });
        $process->on(ProcessInterface::EVENT_AFTER_START, function(ProcessInterface $process) use($output, $name) {
            $process->stdout->on('data', function($data) use($output, $name) {
                $msg = "[$name]";
                $output->write($msg);
            });
        });
        $process->on(ProcessInterface::EVENT_ELAPSED, function(ProcessInterface $process) use($output, $name){
            $msg = "[$name:{$process->getElapsed()}]";
            $output->write($msg);
        });
        $process->on(ProcessInterface::EVENT_HANGING, function(ProcessInterface $process) use($output, $name) {
            $msg = "[$name:H]";
            $output->write($msg);
        });
        $process->on(ProcessInterface::EVENT_RETRY, function(ProcessInterface $process) use($output, $name) {
            $msg = "[$name:R]";
            $output->write($msg);
        });
        $process->on(ProcessInterface::EVENT_MAX_RETRY, function(ProcessInterface $process) use($output, $name) {
            $msg = "[$name:M]";
            $output->write($msg);
        });
        $process->on(ProcessInterface::EVENT_TERMINATED, function(ProcessInterface $process) use($output, $name) {
            $msg = "[$name:T]";
            $output->write($msg);
        });
        
        $process->on(ProcessInterface::EVENT_ERROR, function(ProcessInterface $sender) use($output, $process) {
            /**
             * TODO: Since this is a test, if another process raises
             * an error, simply interrupt this process as well
             */
            if (!$sender->equals($process)) {
                $process->terminate();
                $msg = "[{$process->getName()}:E]";
                $output->write($msg);
            }
        });
        
        return $process;
    }
    
    protected function getCommandName()
    {
        return 'test-download';
    }
}