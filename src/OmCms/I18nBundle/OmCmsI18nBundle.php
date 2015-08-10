<?php

namespace OmCms\I18nBundle;

use OmCms\I18nBundle\DependencyInjection\Compiler\RoutingCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OmCmsI18nBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RoutingCompilerPass());
    }
}
