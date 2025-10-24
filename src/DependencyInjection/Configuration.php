<?php

namespace K3Progetti\JwtBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('jwt');

        $treeBuilder->getRootNode()
            ->children()
            ->scalarNode('secret_key')->isRequired()->end()
            ->scalarNode('algorithm')->defaultValue('HS256')->end()
            ->integerNode('token_ttl')->defaultValue(3600)->end()
            ->integerNode('refresh_token_ttl')->defaultValue(604800)->end()
            ->integerNode('time_zone')->defaultValue('Europe/Rome')->end()
            ->integerNode('2fa_expired_code')->defaultValue('10')->end()
            ->end();

        return $treeBuilder;
    }
}
