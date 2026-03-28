<?php

namespace K3Progetti\JwtBundle\DependencyInjection;

use Exception;
use K3Progetti\JwtBundle\Mailer\TwoFactorMailerInterface;
use K3Progetti\JwtBundle\Repository\JwtUserRepositoryInterface;
use K3Progetti\JwtBundle\Security\JwtUserInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

class JwtExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @param ContainerBuilder $container
     * @return void
     */
    public function prepend(ContainerBuilder $container): void
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->prependExtensionConfig('doctrine', [
            'orm' => [
                'resolve_target_entities' => [
                    JwtUserInterface::class => $config['user_class'],
                ],
            ],
        ]);
    }

    /**
     * @param array $configs
     * @param ContainerBuilder $container
     * @return void
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('jwt.secret_key', $config['secret_key']);
        $container->setParameter('jwt.algorithm', $config['algorithm']);
        $container->setParameter('jwt.token_ttl', $config['token_ttl']);
        $container->setParameter('jwt.refresh_token_ttl', $config['refresh_token_ttl']);
        $container->setParameter('jwt.time_zone', $config['time_zone']);
        $container->setParameter('jwt.2fa_expired_code', $config['2fa_expired_code']);

        // Carico il services
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../resources/config'));
        $loader->load('services.yaml');

        // Alias: JwtUserRepositoryInterface deve puntare al repository concreto dell'app
        $container->setAlias(JwtUserRepositoryInterface::class, $config['user_repository_class'])
            ->setPublic(true);

        // Alias opzionale: TwoFactorMailerInterface deve puntare al mailer concreto (solo se 2FA è configurato)
        if ($config['mailer_class']) {
            $container->setAlias(TwoFactorMailerInterface::class, $config['mailer_class'])
                ->setPublic(true);
        }
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return 'jwt';
    }
}
