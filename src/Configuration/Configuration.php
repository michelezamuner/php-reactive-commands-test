<?php
namespace Update\Configuration;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;

class Configuration
{
    protected $configuration = array();
    
    public function __construct($configFile)
    {
        $locator = new FileLocator(array(dirname($configFile)));
        $loader = new YamlLoader($locator);
        $processor = new Processor();
        
        $this->configuration = $processor->processConfiguration(
            new Definition(),
            $loader->load($locator->locate(basename($configFile)))
        );
    }
    
    public function get($key = '')
    {
        $items = empty($key) ? array() : explode('/', $key);
        $current = $this->configuration;
        foreach ($items as $item) {
            if (!isset($current[$item]))
                throw new InvalidConfigKeyException($key);
            $current = $current[$item];
        }
        return $current;
    }
}