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
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        $formMapper->add('settings', 'sonata_type_immutable_array', array(
            'keys' => array(
                array('number', 'integer', array('required' => true)),
                array('title', 'text', array('required' => false)),
                array('mode', 'choice', array(
                    'choices' => array(
                        'public' => 'public',
                        'admin'  => 'admin'
                    )
                )),
                array('block_type', 'choice', array(
                    'choices' => array(
                        'content' => 'content',
                        'sidebar'  => 'sidebar'
                    )
                ))
            )
        ));
    }

        /**
     * {@inheritdoc}
     */
    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'number'     => 5,
            'mode'       => 'public',
            'title'      => 'Recent Comments',
            'block_type' => 'sidebar',
            'template'   => 'RzNewsBundle:Block:recent_comments.html.twig'
        ));
    }

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
