<?php

namespace Rz\NewsBundle\Block;

use Sonata\NewsBundle\Block\RecentCommentsBlockService as BaseRecentCommentsBlockService;

use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\NewsBundle\Model\CommentManagerInterface;
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
class RecentCommentsBlockService extends BaseRecentCommentsBlockService
{
    /**
     * {@inheritdoc}
     */
    public function getStylesheets($media)
    {
        return array(
            '/bundles/rznews/css/comment_block.css'
        );
    }
}
