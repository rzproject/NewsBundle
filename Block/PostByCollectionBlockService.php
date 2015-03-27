<?php

namespace Rz\NewsBundle\Block;

use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\CoreBundle\Model\ManagerInterface;;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Block\BaseBlockService;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\ClassificationBundle\Model\CollectionInterface;

class PostByCollectionBlockService extends BaseBlockService
{
    protected $collectionManager;
    protected $collectionAdmin;
    protected $templates;
    protected $ajaxTemplates;
    protected $postManager;
    protected $maxPerPage;

    /**
     * @param string          $name
     * @param EngineInterface $templating
     */
    public function __construct($name,
                                EngineInterface $templating,
                                ManagerInterface $collectionManager,
                                AdminInterface $collectionAdmin,
                                ManagerInterface $postManager,
                                $templates,
                                $ajaxTemplates,
                                $maxPerPage)
    {
        $this->name       = $name;
        $this->templating = $templating;
        $this->collectionManager = $collectionManager;
        $this->collectionAdmin = $collectionAdmin;
        $this->postManager = $postManager;
        $this->templates = $templates;
        $this->ajaxTemplates = $ajaxTemplates;
        $this->maxPerPage = $maxPerPage;
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block) {

        if (!$block->getSetting('collection') instanceof CollectionInterface) {
            $this->load($block);
        }

        $formMapper->add('settings', 'sonata_type_immutable_array', array(
            'keys' => array(
                array($this->getCollectionBuilder($formMapper), null, array('attr'=>array('class'=>'span8'))),
                array('mode', 'choice', array(
                    'choices' => array(
                        'public' => 'public',
                        'admin'  => 'admin'
                    )
                )),
                array('template', 'choice', array('choices' => $this->templates)),
                array('ajaxTemplate', 'choice', array('choices' => $this->ajaxTemplates)),
            )
        ));
    }

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     *
     * @return \Symfony\Component\Form\FormBuilder
     */
    protected function getCollectionBuilder(FormMapper $formMapper)
    {
        // simulate an association ...
        $fieldDescription = $this->collectionAdmin->getModelManager()->getNewFieldDescriptionInstance($this->collectionAdmin->getClass(), 'collection' );
        $fieldDescription->setAssociationAdmin($this->collectionAdmin);
        $fieldDescription->setAdmin($formMapper->getAdmin());
        $fieldDescription->setOption('edit', 'list');
        $fieldDescription->setAssociationMapping(array('fieldName' => 'collection',
            'type' => \Doctrine\ORM\Mapping\ClassMetadataInfo::ONE_TO_MANY,
            'targetEntity' => $this->collectionAdmin->getClass(),
            'cascade'       => array(
                0 => 'persist',
            )));

        // TODO: add label on config

        return $formMapper->create('collection', 'sonata_type_model_list', array(
            'sonata_field_description' => $fieldDescription,
            'class'                    => $this->collectionAdmin->getClass(),
            'model_manager'            => $this->collectionAdmin->getModelManager()),
            array('link_parameters' => array('context' => 'news', 'hide_context' => true))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist(BlockInterface $block)
    {
        $block->setSetting('collection', is_object($block->getSetting('collection')) ? $block->getSetting('collection')->getId() : null);
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate(BlockInterface $block)
    {
        $block->setSetting('collection', is_object($block->getSetting('collection')) ? $block->getSetting('collection')->getId() : null);
    }

    /**
     * {@inheritdoc}
     */
    public function load(BlockInterface $block)
    {
        $collection = $block->getSetting('collection', null);

        if (is_int($collection)) {
            $collection = $this->collectionManager->findOneBy(array('id' => $collection));
        }

        $block->setSetting('collection', $collection);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $settings = $blockContext->getBlock()->getSettings('collection');

        $parameters = array(
            'block_context'  => $blockContext,
            'settings'       => $blockContext->getSettings(),
            'block'          => $blockContext->getBlock(),
        );

        if(isset($settings['collection']) && $settings['collection'] instanceof CollectionInterface) {

            $criteria['mode'] = $settings['mode'];
            $criteria['enabled'] = true;
            $criteria['collection'] = $settings['collection'];

            $pager = $this->postManager->getNewsPager($criteria);
            $pager->setMaxPerPage($this->maxPerPage ?: 5);
            $pager->setCurrentPage(1, false, true);

            $parameters['pager'] = $pager;
            $parameters['collection'] = $criteria['collection'];
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
        return 'Post By Collection List';
    }


    /**
     * {@inheritdoc}
     */
    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'mode'       => 'public',
            'template'   => 'RzNewsBundle:Block:post_by_collection_list.html.twig',
            'ajaxTemplate'   => 'RzNewsBundle:Block:post_by_collection_ajax.html.twig',
            'collection' => null,
        ));
    }
}
