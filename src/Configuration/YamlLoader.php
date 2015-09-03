<?php
namespace Update\Configuration;

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Yaml;

class YamlLoader extends FileLoader
{
    public function load($resource, $type = null)
    {
        return Yaml::parse($resource);
    }
    
    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'yml' === pathinfo($resouce, PATHINFO_EXTENSION);
    }
}