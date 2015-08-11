<?php
namespace TheRat\OmCms\I18nBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Parser;

class RoutingCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('jms_i18n_routing.loader');
        $definition->setClass('TheRat\OmCms\I18nBundle\Routing\Loader');

        $definition = $container->getDefinition('jms_i18n_routing.router');
        $definition->setClass('TheRat\OmCms\I18nBundle\Routing\Router');

        $definition = $container->getDefinition('jms_i18n_routing.pattern_generation_strategy.default');
        $definition->setClass('TheRat\OmCms\I18nBundle\Routing\PatternGenerationStrategy');
        $definition->addMethodCall('setContainer', [new Reference('service_container')]);

        $filename = $container->getParameter('kernel.cache_dir') . '/../locales.yml';
        $container->setParameter('om_cms_i18n.locale.filename', $filename);

        $fs = new Filesystem();
        if ($fs->exists($filename)) {
            $parser = new Parser();
            $value = $parser->parse(file_get_contents($filename));
            $locales = $value['parameters']['om_cms_i18n.locale.aliases'];
            $container->setParameter('jms_i18n_routing.locales', $locales);
        }
    }
}
