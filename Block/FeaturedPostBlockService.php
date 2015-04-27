<?php

namespace Rz\NewsBundle\Block;

use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\CoreBundle\Model\ManagerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Block\BaseBlockService;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


class FeaturedPostBlockService extends BaseBlockService
{
    protected $postManager;
    protected $postAdmin;
    protected $templates;
    protected $isControllerEnabled;

    /**
     * @param string $name
     * @param EngineInterface $templating
     * @param AdminInterface $postAdmin
     * @param ManagerInterface $postManager
     * @param array $templates
     * @param bool $isControllerEnabled
     */
    public function __construct($name, EngineInterface $templating, AdminInterface $postAdmin, ManagerInterface $postManager, array $templates =array(), $isControllerEnabled = true)
    {
        $this->postManager = $postManager;
        $this->postAdmin      = $postAdmin;
        $this->templates = $templates;
        $this->isControllerEnabled = $isControllerEnabled;
        parent::__construct($name, $templating);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $post = $blockContext->getBlock()->getSetting('post');

        $parameters = array(
            'post'      => $post,
            'context'   => $blockContext,
            'settings'  => $blockContext->getSettings(),
            'block'     => $blockContext->getBlock(),
            'is_controller_enabled' => $this->isControllerEnabled,
        );

        if ($blockContext->getSetting('mode') === 'admin') {
            return $this->renderPrivateResponse($blockContext->getTemplate(), $parameters, $response);
        }

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
                                           array($this->getPostBuilder($formMapper), null, array('attr'=>array('class'=>'span8'))),
                                           array('title', 'text', array('required' => false)),
                                           array('template', 'choice', array('choices' => $this->templates)),
                                       )
                                   ));
    }

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     *
     * @return \Symfony\Component\Form\FormBuilder
     */
    protected function getPostBuilder(FormMapper $formMapper)
    {
        // simulate an association ...
        $fieldDescription = $this->postAdmin->getModelManager()->getNewFieldDescriptionInstance($this->postAdmin->getClass(), 'post' );
        $fieldDescription->setAssociationAdmin($this->postAdmin);
        $fieldDescription->setAdmin($formMapper->getAdmin());
        $fieldDescription->setOption('edit', 'list');
        $fieldDescription->setAssociationMapping(array('fieldName' => 'post',
            'type' => \Doctrine\ORM\Mapping\ClassMetadataInfo::ONE_TO_MANY,
            'targetEntity' => $this->postAdmin->getClass(),
            'cascade'       => array(
                0 => 'persist',
            )));


        // TODO: add label on config
        return $formMapper->create('post', 'sonata_type_model_list', array(
            'sonata_field_description' => $fieldDescription,
            'class'             => $this->postAdmin->getClass(),
            'model_manager'     => $this->postAdmin->getModelManager()
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Featured Post';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                                   'mode'       => 'public',
                                   'title'      => 'Featured Posts',
                                   'block_type' => 'content',
                                   'template'   => 'SonataNewsBundle:Block:featured_posts.html.twig',
                                   'post'       => null
                               ));
    }

    /**
     * {@inheritdoc}
     */
    public function load(BlockInterface $block)
    {
        $post = $block->getSetting('post', null);

        if (is_int($post)) {
            $post = $this->postManager->findOneBy(array('id' => $post));
        }

        $block->setSetting('post', $post);
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist(BlockInterface $block)
    {
        $block->setSetting('post', is_object($block->getSetting('post')) ? $block->getSetting('post')->getId() : null);
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate(BlockInterface $block)
    {
        $block->setSetting('post', is_object($block->getSetting('post')) ? $block->getSetting('post')->getId() : null);
    }
}
