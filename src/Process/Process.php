<?php
/**
 * TODO: Make Process immutable deleting all setters and
 * passing properties to the constructor
 */
namespace Update\Process;

use Update\Process\Strategy\ExitStrategy\ExitStrategyInterface;
use Update\Process\Strategy\ExitStrategy\NoExitStrategySetException;
use Update\Process\Strategy\HangingStrategy\HangingStrategyInterface;
use Update\Process\Strategy\HangingStrategy\NoHangingStrategySetException;
use React\ChildProcess\Process as ReactProcess;
use React\EventLoop\Timer\TimerInterface;
use React\EventLoop\LoopInterface;

class Process extends ReactProcess implements ProcessInterface
{
    protected $name = '';
    protected $wait = null;
    protected $timer = null;
    protected $elapsed = 0;
    protected $hangingStrategy = null;
    protected $exitStrategy = null;
    protected $hanged = false;
    protected $loop = null;
    protected $signalOnError = true;
    protected $exitCode = null;
    protected $termSignal = null;
    
    public function __construct($name, $cmd, $wait, $signalOnError)
    {
        parent::__construct($cmd);
        $this->name = $name;
        $this->wait = $wait;
        $this->signalOnError = $signalOnError;
        
        // Register callback to be called when process terminates
        $this->on(self::EVENT_EXIT, array($this, 'onExit'));
        
        $this->on(self::EVENT_TERMINATED, array($this, 'onTerminated'));
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getWait()
    {
        return $this->wait;
    }
    
    public function hasTimer()
    {
        return $this->timer !== null;
    }
    
    /**
     * @throws TimerNotSetException
     */
    public function getTimer()
    {
        $this->assertHasTimer();
        return $this->timer;
    }
    
    /**
     * @throws TimerNotSetException
     */
    public function removeTimer()
    {
        $this->assertHasTimer();
        $this->timer->cancel();
        $this->timer = null;
    }
    
    public function getElapsed()
    {
        return $this->elapsed;
    }
        
    public function hasHangingStrategy()
    {
        return $this->hangingStrategy !== null;
    }
    
    public function getHangingStrategy()
    {
        if (!$this->hasHangingStrategy())
            throw new NoHangingStrategySetException();
        return $this->hangingStrategy;
    }
    
    public function setHangingStrategy(HangingStrategyInterface $hangingStrategy)
    {
        $this->hangingStrategy = $hangingStrategy;
        return $this;
    }
    
    public function hasExitStrategy()
    {
        return $this->exitStrategy !== null;
    }
    
    public function getExitStrategy()
    {
        if (!$this->hasExitStrategy())
            throw new NoExitStrategySetException();
        return $this->exitStrategy;
    }
    
    public function setExitStrategy(ExitStrategyInterface $exitStrategy)
    {
        $this->exitStrategy = $exitStrategy;
        return $this;
    }
    
    public function isHanged()
    {
        return $this->hanged;
    }
    
    public function getLoop()
    {
        if ($this->loop === null)
            throw new LoopNotSetException();
        return $this->loop;
    }
    
    /**
     * Start process on the loop passed
     */
    public function start(LoopInterface $loop, $interval = 0.1)
    {
        $this->send(self::EVENT_BEFORE_START);
        
        // Initialize variables
        $this->loop = $loop;
        $this->elapsed = 1; // The first time this is used, one second is elapsed already
        $this->hanged = false;
        
        // Start process
        parent::start($this->loop, $interval);
        
        if (!$this->hasTimer()) {
        	// For every tick of the timer, check if process is hanging,
        	// and update elapsed time
            $self = $this;
            $timer = &$this->timer;
            $elapsed = &$this->elapsed;
            $hanged = &$this->hanged;
            $this->timer = $loop->addPeriodicTimer(1, function(TimerInterface $t) use($self, &$timer, &$elapsed, &$hanged) {
                $self->send($self::EVENT_ELAPSED);
                
                // If process is hanging, interrupt it
                if ($self->isHanging()) {
                    $hanged = true;
                    $self->send($self::EVENT_HANGING);
                    $self->terminate();
                }
                
                // Increment elapsed time
                $elapsed++;
            });
        }
        
        $this->send(self::EVENT_AFTER_START);
    }
    
    /**
     * Tell if process is hanging or not
     */
    public function isHanging()
    {
        $isHanging = $this->hasHangingStrategy()
            ? $this->getHangingStrategy()->isHanging()
            : false;
        return $isHanging;
    }
    
    public function equals(ProcessInterface $process)
    {
        return $process->getName() === $this->name;
    }
    
    public function send($event, array $args = array())
    {
        $args = array_merge(array($this), $args);
        $this->emit($event, $args);
    }
    
    protected function onExit($exitCode, $termSignal)
    {
        $this->exitCode = $exitCode;
        $this->termSignal = $termSignal;
        if ($this->hasExitStrategy()) {
            $this->getExitStrategy()->onExit(/*$exitCode, $termSignal*/);
        } else {
            $this->removeTimer();
            $this->send(self::EVENT_TERMINATED);
        }
    }
    
    protected function onTerminated(ProcessInterface $process)
    {
        if ($this->signalOnError && $this->exitCode !== self::EXIT_OK)
            $this->send(self::EVENT_ERROR);
    }
    
    protected function assertHasTimer()
    {
        if (!$this->hasTimer())
            throw new TimerNotSetException('Timer not set');
    }
}