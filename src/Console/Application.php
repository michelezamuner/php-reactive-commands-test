<?php
namespace Update\Console;

use Symfony\Component\Console\Application as SymfonyApplication;
use Update\Configuration\Configuration;

class Application extends SymfonyApplication
{
    protected $configuration = null;
    
    public function __construct(Configuration $configuration, $name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        parent::__construct($name, $version);
        $this->configuration = $configuration;
    }
    
    public function getConfiguration()
    {
        return $this->configuration;
    }
    
    public function getConf($key = '')
    {
        return $this->configuration->get($key);
    }
}