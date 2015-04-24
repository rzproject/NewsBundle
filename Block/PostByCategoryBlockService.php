<?php

namespace Rz\NewsBundle\Block;

use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\CoreBundle\Model\ManagerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Block\BaseBlockService;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\ClassificationBundle\Model\CategoryInterface;

class PostByCategoryBlockService extends BaseBlockService
{
    protected $categoryManager;
    protected $categoryAdmin;
    protected $templates;
    protected $ajaxTemplates;
    protected $ajaxPagerTemplates;
    protected $postManager;
    protected $maxPerPage;
    protected $isEnabledController;
    protected $isCanonicalPageEnabled;

    /**
     * @param string $name
     * @param EngineInterface $templating
     * @param ManagerInterface $categoryManager
     * @param AdminInterface $categoryAdmin
     * @param ManagerInterface $postManager
     * @param array $templates
     * @param array $ajaxTemplates
     * @param array $ajaxPagerTemplates
     * @param $maxPerPage
     * @param $isEnabledController
     * @param $isCanonicalPageEnabled
     */
    public function __construct($name,
                                EngineInterface $templating,
                                ManagerInterface $categoryManager,
                                AdminInterface $categoryAdmin,
                                ManagerInterface $postManager,
                                array $templates = array(),
                                array $ajaxTemplates = array(),
                                array $ajaxPagerTemplates = array(),
                                $maxPerPage,
                                $isEnabledController = true,
                                $isCanonicalPageEnabled = false)
    {
        $this->name       = $name;
        $this->templating = $templating;
        $this->categoryManager = $categoryManager;
        $this->categoryAdmin = $categoryAdmin;
        $this->postManager = $postManager;
        $this->templates = $templates;
        $this->ajaxTemplates = $ajaxTemplates;
        $this->ajaxPagerTemplates = $ajaxPagerTemplates;
        $this->maxPerPage = $maxPerPage;
        $this->isEnabledController = $isEnabledController;
        $this->isCanonicalPageEnabled = $isCanonicalPageEnabled;
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block) {

        if (!$block->getSetting('category') instanceof CategoryInterface) {
            $this->load($block);
        }

        $formMapper->add('settings', 'sonata_type_immutable_array', array(
            'keys' => array(
                array($this->getCategoryBuilder($formMapper), null, array('attr'=>array('class'=>'span8'))),
                array('mode', 'choice', array(
                    'choices' => array(
                        'public' => 'public',
                        'admin'  => 'admin'
                    )
                )),
                array('template', 'choice', array('choices' => $this->templates)),
                array('ajaxTemplate', 'choice', array('choices' => $this->ajaxTemplates)),
                array('ajaxPagerTemplate', 'choice', array('choices' => $this->ajaxPagerTemplates)),
            )
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
    public function prePersist(BlockInterface $block)
    {
        $block->setSetting('category', is_object($block->getSetting('category')) ? $block->getSetting('category')->getId() : null);
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate(BlockInterface $block)
    {
        $block->setSetting('category', is_object($block->getSetting('category')) ? $block->getSetting('category')->getId() : null);
    }

    /**
     * {@inheritdoc}
     */
    public function load(BlockInterface $block)
    {
        $category = $block->getSetting('category', null);

        if (is_int($category)) {
            $category = $this->categoryManager->findOneBy(array('id' => $category));
        }

        $block->setSetting('category', $category);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $settings = $blockContext->getBlock()->getSettings('category');

        $parameters = array(
            'block_context'  => $blockContext,
            'settings'       => $blockContext->getSettings(),
            'block'          => $blockContext->getBlock(),
            'enable_category_canonical_page' => $this->isCanonicalPageEnabled,
            'is_controller_enabled' => $this->isEnabledController,
        );

        if(isset($settings['category']) && $settings['category'] instanceof CategoryInterface) {

            $criteria['mode'] = $settings['mode'];
            $criteria['enabled'] = true;
            $criteria['category'] = $settings['category'];

            $pager = $this->postManager->getNewsPager($criteria);
            $pager->setMaxPerPage($this->maxPerPage ?: 5);
            $pager->setCurrentPage(1, false, true);

            $parameters['pager'] = $pager;
            $parameters['category'] = $criteria['category'];
        }

        if ($blockContext->getSetting('mode') !== 'public') {
            return $this->renderPrivateResponse($blockContext->getTemplate(), $parameters, $response);
        }

        return $this->renderResponse($blockContext->getTemplate(), $parameters, $response);
    }


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Post By Category List';
    }


    /**
     * {@inheritdoc}
     */
    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'mode'       => 'public',
            'template'   => 'RzNewsBundle:Block:post_by_category_list.html.twig',
            'ajaxTemplate'   => 'RzNewsBundle:Block:post_by_category_ajax.html.twig',
            'ajaxPagerTemplate'   => 'RzNewsBundle:Block:post_by_category_ajax_pager.html.twig',
            'category' => null,
        ));
    }
}
