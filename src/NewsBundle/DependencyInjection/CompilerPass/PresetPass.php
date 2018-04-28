<?php

namespace NewsBundle\DependencyInjection\CompilerPass;

use NewsBundle\Registry\PresetRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class PresetPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition(PresetRegistry::class);
        foreach ($container->findTaggedServiceIds('news.preset') as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition->addMethodCall('register', [$attributes['alias'], new Reference($id)]);
            }
        }
    }
}