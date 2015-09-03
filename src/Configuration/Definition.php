<?php
namespace Update\Configuration;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;

class Definition implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('update');
                
        self::addRemoteSection($rootNode);
        self::addLocalSection($rootNode);
        self::addDownloadSection($rootNode);
        
        return $treeBuilder;
    }
    
    protected static function addRemoteSection(ArrayNodeDefinition $rootNode)
    {
        $databaseNode = self::createDatabaseNode();
        $databaseNode
            ->children()
                ->append(self::createRequiredScalar('dump'))
            ->end()
        ;
        
        $rootNode
            ->children()
                ->arrayNode('remote')
                    ->isRequired()
                    ->children()
                        ->append(self::createRequiredScalar('host'))
                        ->scalarNode('path')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->validate()
                                ->ifTrue(function($s) {
                                    return preg_match('/\/$/', $s) !== 1;
                                })
                                ->thenInvalid('Remote and local paths must end with the slash character')
                            ->end()
                        ->end()
                        ->append($databaseNode)
                    ->end()
                ->end()
            ->end()
        ;
    }
    
    protected static function addLocalSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('local')
                    ->isRequired()
                    ->children()
                        ->scalarNode('path')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->validate()
                                ->ifTrue(function($s) {
                                    return preg_match('/\/$/', $s) !== 1;
                                })
                                ->thenInvalid('Remote and local paths must end with the slash character')
                            ->end()
                        ->end()
                        ->append(self::createDatabaseNode())
                        ->append(self::createRequiredScalar('backupPath'))
                    ->end()
                ->end()
            ->end()
        ;
    }
    
    protected static function addDownloadSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('download')
                    ->isRequired()
                    ->children()
                        ->arrayNode('paths')
                            ->isRequired()
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('remote')
                                        ->isRequired()
                                        ->cannotBeEmpty()
                                        ->validate()
                                            ->ifTrue(function($s) {
                                                return preg_match('/^[^\/]/', $s) !== 1;
                                            })
                                            ->thenInvalid('Download paths must not start with the slash character')
                                        ->end()
                                    ->end()
                                    ->scalarNode('local')
                                        ->cannotBeEmpty()
                                        ->defaultValue('./')
                                        ->validate()
                                            ->ifTrue(function($s) {
                                                return preg_match('/^[^\/]/', $s) !== 1;
                                            })
                                            ->thenInvalid('Download paths must not start with the slash character')
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('excludes')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
    
    protected static function createDatabaseNode()
    {
        $node = new ArrayNodeDefinition('database');
        $node
            ->isRequired()
            ->children()
                ->scalarNode('host')
                    ->defaultValue('localhost')
                ->end()
                ->append(self::createRequiredScalar('username'))
                ->append(self::createRequiredScalar('password'))
                ->append(self::createRequiredScalar('name'))
            ->end()
        ;
        return $node;
    }
    
    protected static function createRequiredScalar($name)
    {
        $node = new ScalarNodeDefinition($name);
        $node
            ->isRequired()
            ->cannotBeEmpty()
            ->end()
        ;
        return $node;
    }
}