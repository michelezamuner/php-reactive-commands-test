<?php
namespace Update\Process;

use InvalidArgumentException;
use Update\Process\CmdNotSetException;
use Update\Process\Strategy\HangingStrategy\WaitForOutput;
use Update\Process\Strategy\HangingStrategy\WaitForTermination;
use Update\Process\Strategy\ExitStrategy\Retry;
use Update\Process\Strategy\ExitStrategy\Terminate;
use Update\Process\ProcessInterface;
use Update\Process\Process;

class Builder
{
    const RETRY_DEFAULT = false;
    const MAX_RETRIES_DEFAULT = 2;
    const MAX_RETRIES_MINIMUM = 1;
    const WAIT_FOR_OUTPUT_DEFAULT = true;
    const WAIT_DEFAULT = 2;
    const WAIT_MINIMUM = 1;
    const SIGNAL_ON_ERROR_DEFAULT = true;
    
    protected $name = '';
    protected $cmd = '';
    protected $wait = self::WAIT_DEFAULT;
    protected $retry = self::RETRY_DEFAULT;
    protected $maxRetries = self::MAX_RETRIES_DEFAULT;
    protected $waitForOutput = self::WAIT_FOR_OUTPUT_DEFAULT;
    protected $signalOnError = self::SIGNAL_ON_ERROR_DEFAULT;
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setName($name)
    {
        $name = trim($name);
        if (empty($name))
            throw new InvalidArgumentException('name cannot be empty');
        $this->name = $name;
        return $this;
    }
    
    public function getCmd()
    {
        return $this->cmd;
    }
    
    public function setCmd($cmd)
    {
        if (!is_string($cmd))
            throw new InvalidArgumentException('cmd must be a string');
        $cmd = trim($cmd);
        if (empty($cmd))
            throw new InvalidArgumentException('cmd cannot be empty');
        $this->cmd = $cmd;
        return $this;
    }
    
    public function getWait()
    {
        return $this->wait;
    }
    
    public function setWait($wait)
    {
        if (!is_numeric($wait) || $wait < self::WAIT_MINIMUM)
            throw new InvalidArgumentException('invalid wait');
        $this->wait = $wait;
        return $this;
    }
    
    public function isRetry()
    {
        return $this->retry;
    }
    
    public function setRetry($retry = true)
    {
        if (!is_bool($retry))
            throw new InvalidArgumentException('retry must be boolean');
        $this->retry = $retry;
        return $this;
    }
    
    public function getMaxRetries()
    {
        return $this->maxRetries;
    }
    
    public function setMaxRetries($maxRetries)
    {
        if (!is_numeric($maxRetries) || $maxRetries < self::MAX_RETRIES_MINIMUM)
            throw new InvalidArgumentException('invalid maxRetries');
        $this->maxRetries = $maxRetries;
        return $this;
    }
    
    public function isWaitForOutput()
    {
        return $this->waitForOutput;
    }
    
    public function setWaitForOutput($waitForOutput = true)
    {
        if (!is_bool($waitForOutput))
            throw new InvalidArgumentException('waitForOutput must be boolean');
        $this->waitForOutput = $waitForOutput;
        return $this;
    }
    
    public function isSignalOnError()
    {
        return $this->signalOnError;
    }
    
    public function setSignalOnError($signalOnError = true)
    {
        $this->signalOnError = $signalOnError;
        return $this;
    }
    
    public function createProcess()
    {
        if ($this->cmd === '')
            throw new CmdNotSetException();
        $process = new Process($this->name, $this->cmd, $this->wait, $this->signalOnError);
        $process->setHangingStrategy($this->createHangingStrategy($process));
        $process->setExitStrategy($this->createExitStrategy($process));
        return $process;
    }
    
    protected function createHangingStrategy(ProcessInterface $process)
    {
        return $this->isWaitForOutput()
            ? new WaitForOutput($process)
            : new WaitForTermination($process)
        ;
    }
    
    protected function createExitStrategy(ProcessInterface $process)
    {
        return $this->isRetry()
            ? new Retry($process, $this->maxRetries)
            : new Terminate($process)
        ;
    }
}