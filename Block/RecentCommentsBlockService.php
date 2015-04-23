<?php

namespace Rz\NewsBundle\Block;

use Sonata\NewsBundle\Block\RecentCommentsBlockService as BaseRecentCommentsBlockService;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Block\BaseBlockService;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class RecentCommentsBlockService extends BaseRecentCommentsBlockService
{

    protected $templates;

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        $formMapper->add('settings', 'sonata_type_immutable_array', array(
            'keys' => array(
                array('number', 'integer', array('required' => true)),
                array('title', 'text', array('required' => false)),
                array('template', 'choice', array('choices' => $this->templates)),
                array('max_characters', 'integer', array('required' => true)),
                array('mode', 'choice', array(
                    'choices' => array(
                        'public' => 'public',
                        'admin'  => 'admin'
                    )
                )),
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
            'max_characters' => 30,
            'template'   => 'RzNewsBundle:Block:recent_comments.html.twig'
        ));
    }

    /**
     * @return mixed
     */
    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     * @param mixed $templates
     */
    public function setTemplates($templates)
    {
        $this->templates = $templates;
    }
}
