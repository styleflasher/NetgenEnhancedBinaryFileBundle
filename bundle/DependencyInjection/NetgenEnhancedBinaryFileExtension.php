<?php

namespace Netgen\Bundle\EnhancedBinaryFileBundle\DependencyInjection;

use ReflectionClass;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Netgen\Bundle\EnhancedBinaryFileBundle\NetgenEnhancedBinaryFileBundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

class NetgenEnhancedBinaryFileExtension extends Extension implements PrependExtensionInterface
{
    /**
     * Preprend ezplatform configuration to make the field templates
     * visibile to the admin template engine.
     *
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        $refl = new ReflectionClass(NetgenEnhancedBinaryFileBundle::class);
        $path = \dirname($refl->getFileName()).'/Resources/views';

        $container->prependExtensionConfig('twig', ['paths' => [
            $path => 'NetgenEnhancedBinaryFileBundle'
        ]]);

        $fileName = 'ez_field_templates.yml';
        $configFile = __DIR__ . '/../Resources/config/' . $fileName;
        $config = Yaml::parse(file_get_contents($configFile));

        $container->prependExtensionConfig('ibexa', $config);
        $container->addResource(new FileResource($configFile));

    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('fieldtype_form_mappers.yml');
        $loader->load('fieldtypes.yml');
        $loader->load('field_type_handlers.yml');
        $loader->load('storage_engines.yml');
        $loader->load('mime.yml');
        $loader->load('information_collection.yml');
        $loader->load('services.yml');
    }
}
