<?php

namespace Rz\NewsBundle\Block;

use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\CoreBundle\Model\ManagerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\CoreBundle\Validator\ErrorElement;

use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Block\BaseBlockService;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\Common\Collections\ArrayCollection;


class CollectionBlockService extends BaseBlockService
{
    protected $manager;

    /**
     * @param string $name
     * @param EngineInterface $templating
     * @param ContainerInterface $container
     * @param \Sonata\CoreBundle\Model\ManagerInterface $manager
     */
    public function __construct($name, EngineInterface $templating, ContainerInterface $container, ManagerInterface $manager)
    {
        $this->manager = $manager;
        $this->container      = $container;
        parent::__construct($name, $templating);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {

        $limit = $blockContext->getSetting('number') ? $blockContext->getSetting('number') : 5;

        $parameters = array(
            'context'   => $blockContext,
            'settings'  => $blockContext->getSettings(),
            'block'     => $blockContext->getBlock(),
            'collections'     => $this->manager->getCollections($limit)
        );

        return $this->renderResponse($blockContext->getTemplate(), $parameters, $response);
    }

    /**
     * {@inheritdoc}
     */
    public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
    {
        // TODO: Implement validateBlock() method.
    }

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
                                           ))
                                       )
                                   ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Post Collection';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                                   'title'      => 'Collection',
                                   'number'     => 5,
                                   'template'   => 'RzNewsBundle:Block:collection.html.twig'
                               ));
    }
}
