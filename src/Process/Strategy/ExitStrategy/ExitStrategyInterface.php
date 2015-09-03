<?php
namespace Update\Process\Strategy\ExitStrategy;

interface ExitStrategyInterface
{
    public function onExit(/*$exitCode, $termSignal*/);
}