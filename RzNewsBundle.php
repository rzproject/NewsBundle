<?php

namespace Rz\NewsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Rz\NewsBundle\DependencyInjection\Compiler\OverrideServiceCompilerPass;
use Rz\NewsBundle\DependencyInjection\Compiler\AddProviderCompilerPass;
//use Rz\NewsBundle\DependencyInjection\Compiler\AddNewsPageCompilerPass;

class RzNewsBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new OverrideServiceCompilerPass());
        $container->addCompilerPass(new AddProviderCompilerPass());
//        if (interface_exists('Sonata\PageBundle\Model\PageInterface')) {
//            $container->addCompilerPass(new AddNewsPageCompilerPass());
//        }
    }
}
