<?php
namespace TheRat\OmCms\I18nBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use TheRat\OmCms\I18nBundle\Helper\Locales;

class RoutingCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $locales = Locales::getDbLocalesFromFile($container->getParameter('om_cms_i18n.locale.filename'));
        if (!empty($locales)) {
            $container->setParameter('jms_i18n_routing.locales', $locales);
        }

        $definition = $container->getDefinition('jms_i18n_routing.loader');
        $definition->setClass('TheRat\OmCms\I18nBundle\Routing\Loader');

        $definition = $container->getDefinition('jms_i18n_routing.router');
        $definition->setClass('TheRat\OmCms\I18nBundle\Routing\Router');
        $definition->addMethodCall('setLocales', ['%jms_i18n_routing.locales%']);

        $definition = $container->getDefinition('jms_i18n_routing.pattern_generation_strategy.default');
        $definition->setClass('TheRat\OmCms\I18nBundle\Routing\PatternGenerationStrategy');
        $definition->addMethodCall('setContainer', [new Reference('service_container')]);
    }
}
