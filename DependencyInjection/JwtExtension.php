<?php

namespace K3Progetti\JwtBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class JwtExtension extends Extension
{
    /**
     * @param array $configs
     * @param ContainerBuilder $container
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('jwt.secret_key', $config['secret_key']);
        $container->setParameter('jwt.algorithm', $config['algorithm']);
        $container->setParameter('jwt.token_ttl', $config['token_ttl']);
        $container->setParameter('jwt.refresh_token_ttl', $config['refresh_token_ttl']);

    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return 'jwt';
    }
}
