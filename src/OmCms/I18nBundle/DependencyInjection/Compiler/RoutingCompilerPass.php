<?php
namespace OmCms\I18nBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RoutingCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('jms_i18n_routing.loader');
        $definition->setClass('OmCms\I18nBundle\Routing\Loader');

        $definition = $container->getDefinition('jms_i18n_routing.router');
        $definition->setClass('OmCms\I18nBundle\Routing\Router');

        $definition = $container->getDefinition('jms_i18n_routing.pattern_generation_strategy.default');
        $definition->setClass('OmCms\I18nBundle\Routing\PatternGenerationStrategy');
        $definition->addMethodCall('setContainer', [new Reference('service_container')]);
    }
}
