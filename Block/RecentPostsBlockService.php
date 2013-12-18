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

class RecentPostsBlockService extends BaseRecentPostsBlockService
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
                        'sidebar'  => 'sidebar',
                        'content' => 'content'
                    )
                ))
            )
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getStylesheets($media)
    {
        return array(
            '/bundles/rznews/css/news_block.css'
        );
    }


    /**
     * {@inheritdoc}
     */
    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'number'     => 5,
            'mode'       => 'public',
            'title'      => 'Recent Posts',
            'block_type' => 'sidebar',
//            'tags'      => 'Recent Posts',
            'template'   => 'RzNewsBundle:Block:recent_posts.html.twig'
        ));
    }
}
