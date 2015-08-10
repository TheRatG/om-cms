<?php

namespace TheRat\OmCms\I18nBundle;

use TheRat\OmCms\I18nBundle\DependencyInjection\Compiler\RoutingCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OmCmsI18nBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RoutingCompilerPass());
    }
}
