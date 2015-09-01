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
use Symfony\Component\OptionsResolver\OptionsResolver;
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


class RelatedPostBlockService extends BaseBlockService
{
    protected $postManager;
    protected $postAdmin;
    protected $categoryManager;
    protected $categoryAdmin;
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
    public function __construct($name,
                                EngineInterface $templating,
                                AdminInterface $postAdmin,
                                ManagerInterface $postManager,
                                AdminInterface $categoryAdmin,
                                ManagerInterface $categoryManager,
                                array $templates =array(),
                                $isControllerEnabled = true)
    {
        $this->postManager      = $postManager;
        $this->postAdmin        = $postAdmin;
        $this->categoryManager  = $categoryManager;
        $this->categoryAdmin    = $categoryAdmin;
        $this->templates        = $templates;
        $this->isControllerEnabled = $isControllerEnabled;
        parent::__construct($name, $templating);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $settings = $blockContext->getSettings();

        $post = $settings['post'];
        $category = $settings['category'];

        $criteria['mode'] = $settings['mode'];
        $criteria['enabled'] = true;
        $criteria['exclude_post_id'] = $post;
        $criteria['category_id'] = $category;
        $noOfPost = (int)(isset($settings['noOfPost']) ? (int)$settings['noOfPost'] : 3);

        $sort = array('publicationDateStart'=>'DESC');


        $pager = $this->postManager->getCustomNewsPager($criteria, $sort);
        $pager->setMaxPerPage($noOfPost);
        $pager->setCurrentPage(1, false, true);

        $parameters = array(
            'post'      => $post,
            'category'  => $category,
            'context'   => $blockContext,
            'settings'  => $blockContext->getSettings(),
            'block'     => $blockContext->getBlock(),
            'is_controller_enabled' => $this->isControllerEnabled,
            'pager'     => $pager,
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
                array('title', 'text', array('required' => false)),
                array($this->getPostBuilder($formMapper), null, array('attr'=>array('class'=>'span8'))),
                array($this->getCategoryBuilder($formMapper), null, array('attr'=>array('class'=>'span8'))),
                array('noOfPost', 'integer', array('attr'=>array('class'=>'span3'))),
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
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     *
     * @return \Symfony\Component\Form\FormBuilder
     */
    protected function getCategoryBuilder(FormMapper $formMapper)
    {
        // simulate an association ...
        $fieldDescription = $this->categoryAdmin->getModelManager()->getNewFieldDescriptionInstance($this->categoryAdmin->getClass(), 'category' );
        $fieldDescription->setAssociationAdmin($this->categoryAdmin);
        $fieldDescription->setAdmin($formMapper->getAdmin());
        $fieldDescription->setOption('edit', 'list');
        $fieldDescription->setOptions(array('link_parameters' => array('context' => 'news', 'hide_context' => true)));
        $fieldDescription->setAssociationMapping(array('fieldName' => 'category',
                                                       'type' => \Doctrine\ORM\Mapping\ClassMetadataInfo::ONE_TO_MANY,
                                                       'targetEntity' => $this->categoryAdmin->getClass(),
                                                       'cascade'       => array(
                                                           0 => 'persist',
                                                       )));

        // TODO: add label on config

        return $formMapper->create('category', 'sonata_type_model_list', array(
                'sonata_field_description' => $fieldDescription,
                'class'                    => $this->categoryAdmin->getClass(),
                'model_manager'            => $this->categoryAdmin->getModelManager())
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Related Post';
    }

    /**
     * Define the default options for the block.
     *
     * @param OptionsResolver $resolver
     */
    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'mode'       => 'public',
            'title'      => 'Related Posts',
            'block_type' => 'content',
            'template'   => 'SonataNewsBundle:Block:featured_posts.html.twig',
            'post'       => null,
            'category'   => null,
            'noOfPost'   => 3,
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

        $category = $block->getSetting('category', null);
        if (is_int($category)) {
            $category = $this->categoryManager->findOneBy(array('id' => $category));
        }
        $block->setSetting('category', $category);
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist(BlockInterface $block)
    {
        $block->setSetting('post', is_object($block->getSetting('post')) ? $block->getSetting('post')->getId() : null);
        $block->setSetting('category', is_object($block->getSetting('category')) ? $block->getSetting('category')->getId() : null);
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate(BlockInterface $block)
    {
        $block->setSetting('post', is_object($block->getSetting('post')) ? $block->getSetting('post')->getId() : null);
        $block->setSetting('category', is_object($block->getSetting('category')) ? $block->getSetting('category')->getId() : null);
    }
}
