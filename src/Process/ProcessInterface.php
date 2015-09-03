<?php
namespace Update\Process;

use React\EventLoop\LoopInterface;
use Update\Process\Strategy\HangingStrategy\HangingStrategyInterface;
use Update\Process\Strategy\ExitStrategy\ExitStrategyInterface;

interface ProcessInterface
{
    const EVENT_BEFORE_START = 'process:before_start';
    const EVENT_AFTER_START = 'process:after_start';
    const EVENT_HANGING = 'process:hanging';
    const EVENT_ELAPSED = 'process:elapsed';
    const EVENT_TERMINATED = 'process:terminated';
    const EVENT_RETRY = 'process:retry';
    const EVENT_MAX_RETRY = 'process:max_retry';
    const EVENT_ERROR = 'process:error';
    const EVENT_EXIT = 'exit';
    const EVENT_DATA = 'data';
    const EXIT_OK = 0;
    
    public function start(LoopInterface $loop, $interval = 0.1);
    public function terminate($signal = null);
    public function getCommand();
    public function getEnhanceSigchildCompatibility();
    public function setEnhanceSigchildCompatibility($enhance);
    public function getExitCode();
    public function getPid();
    public function getStopSignal();
    public function getTermSignal();
    public function isRunning();
    public function isStopped();
    public function isTerminated();
    public static function isSigchildEnabled();
    
    public function getName();
    public function getWait();
    public function hasTimer();
    public function getTimer();
    public function removeTimer();
    public function getElapsed();
    public function hasHangingStrategy();
    public function getHangingStrategy();
    public function setHangingStrategy(HangingStrategyInterface $hangingStrategy);
    public function hasExitStrategy();
    public function getExitStrategy();
    public function setExitStrategy(ExitStrategyInterface $exitStrategy);
    public function isHanged();
    public function isHanging();
    public function getLoop();
    public function send($event, array $args);
}