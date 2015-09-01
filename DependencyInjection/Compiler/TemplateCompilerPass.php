<?php

/*
 * This file is part of the RzUserBundle package.
 *
 * (c) mell m. zamora <mell@rzproject.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rz\NewsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TemplateCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('rz_admin.template.loader');
        $templates = $container->getParameter('rz_news.templates');
        $rznewsTemplates = array();
        foreach($templates as $key => $template) {
            $rznewsTemplates[sprintf('rz_news.template.%s', $key)] = $template;
        }
        $definition->addMethodCall('setTemplates', array($rznewsTemplates));
    }
}
