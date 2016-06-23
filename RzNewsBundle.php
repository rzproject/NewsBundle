<?php

namespace Rz\NewsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Rz\NewsBundle\DependencyInjection\Compiler\OverrideServiceCompilerPass;
use Rz\NewsBundle\DependencyInjection\Compiler\AddProviderCompilerPass;

class RzNewsBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new OverrideServiceCompilerPass());
        $container->addCompilerPass(new AddProviderCompilerPass());
    }
}
