<?php

namespace Netgen\Bundle\EnhancedBinaryFileBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class NetgenEnhancedBinaryFileExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        if (class_exists(\eZ\Publish\SPI\FieldType\GatewayBasedStorage::class)) {
            $loader->load('fieldtypes_after_611.yml');
        } else {
            $loader->load('fieldtypes_before_611.yml');
        }
        $loader->load('fieldtypes.yml');
        $loader->load('field_type_handlers.yml');
        $loader->load('storage_engines.yml');
        $loader->load('mime.yml');
        $loader->load('information_collection.yml');
    }
}
