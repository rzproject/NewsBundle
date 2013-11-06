<?php

namespace Rz\NewsBundle\Block;

use Sonata\NewsBundle\Block\RecentPostsBlockService as BaseRecentPostsBlockService;

use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\NewsBundle\Model\PostManagerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Validator\ErrorElement;

use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Block\BaseBlockService;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 *
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class RecentPostsBlockService extends BaseRecentPostsBlockService
{
    /**
     * {@inheritdoc}
     */
    public function getStylesheets($media)
    {
        return array(
            '/bundles/rznews/css/news_block.css'
        );
    }
}
