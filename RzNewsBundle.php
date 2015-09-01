<?php

/*
 * This file is part of the RzNewsBundle package.
 *
 * (c) mell m. zamora <mell@rzproject.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rz\NewsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Rz\NewsBundle\DependencyInjection\Compiler\OverrideServiceCompilerPass;
use Rz\NewsBundle\DependencyInjection\Compiler\TemplateCompilerPass;
use Rz\NewsBundle\DependencyInjection\Compiler\AddProviderCompilerPass;

class RzNewsBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'SonataNewsBundle';
    }

    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new OverrideServiceCompilerPass());
        $container->addCompilerPass(new TemplateCompilerPass());
        $container->addCompilerPass(new AddProviderCompilerPass());
    }
}
