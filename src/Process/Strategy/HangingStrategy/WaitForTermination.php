<?php
namespace Update\Process\Strategy\HangingStrategy;

class WaitForTermination extends Wait implements HangingStrategyInterface
{    
    public function isHanging()
    {
        return $this->isBasicHanging();
    }
}