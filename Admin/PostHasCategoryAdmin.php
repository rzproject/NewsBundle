<?php


namespace Rz\NewsBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Route\RouteCollection;

class PostHasCategoryAdmin extends Admin
{
    protected $parentAssociationMapping = 'post';
    protected $postHasCategoryManager;
    protected $categoryManager;
    protected $defaultContext;
    protected $slugify;


    protected function configureFormFields(FormMapper $formMapper)
    {

        $formMapper
            ->with('group_relation',  array('class' => 'col-md-8'))->end()
            ->with('group_status',    array('class' => 'col-md-4'))->end();

        # check if admin is embeded
        if($this->hasParentFieldDescription()) {
            if (interface_exists('Sonata\ClassificationBundle\Model\CategoryInterface')) {
                $formMapper
                    ->with('group_relation',  array('class' => 'col-md-8'))
                        ->add('category', 'sonata_type_model_list', array('btn_delete' => false), array())
                    ->end();
            }
        } else {
            if (interface_exists('Sonata\ClassificationBundle\Model\CategoryInterface')) {
                $formMapper
                    ->with('group_relation',  array('class' => 'col-md-8'))
                        ->add('category', 'sonata_type_model_list', array('btn_delete' => false), array('link_parameters' => array('context'=>$this->getSlugify()->slugify($this->getDefaultContext()))))
                    ->end();
            }

            if (interface_exists('Sonata\NewsBundle\Model\PostInterface')) {
                $formMapper
                    ->with('group_relation',  array('class' => 'col-md-8'))
                        ->add('post', 'sonata_type_model_list', array('btn_delete' => false), array())
                    ->end();
            }
        }

        $formMapper
            ->with('group_status',    array('class' => 'col-md-4'))
                ->add('enabled', null, array('required' => false))
                ->add('position')
            ->end()
        ;
    }

    /**
     * @param  \Sonata\AdminBundle\Datagrid\ListMapper $listMapper
     * @return void
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('post.title')
            ->add('position', null, array('footable'=>array('attr'=>array('data-breakpoints'=>array('xs', 'sm'))), 'editable' => true))
            ->add('post.publicationDateStart', null, array('footable'=>array('attr'=>array('data-breakpoints'=>array('xs', 'sm')))))
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('post.title')
            ->add('post.publicationDateStart', 'doctrine_orm_datetime_range', array('field_type' => 'sonata_type_datetime_range_picker'))
            ->add('category', null, array('show_filter' => false,));
    }

    /**
     * @return mixed
     */
    public function getCategoryManager()
    {
        return $this->categoryManager;
    }

    /**
     * @param mixed $categoryManager
     */
    public function setCategoryManager($categoryManager)
    {
        $this->categoryManager = $categoryManager;
    }

    /**
     * @return mixed
     */
    public function getPostHasCategoryManager()
    {
        return $this->postHasCategoryManager;
    }

    /**
     * @param mixed $postHasCategoryManager
     */
    public function setPostHasCategoryManager($postHasCategoryManager)
    {
        $this->postHasCategoryManager = $postHasCategoryManager;
    }

    /**
     * @return mixed
     */
    public function getDefaultContext()
    {
        return $this->defaultContext;
    }

    /**
     * @param mixed $defaultContext
     */
    public function setDefaultContext($defaultContext)
    {
        $this->defaultContext = $defaultContext;
    }

    /**
     * @return mixed
     */
    public function getSlugify()
    {
        return $this->slugify;
    }

    /**
     * @param mixed $slugify
     */
    public function setSlugify($slugify)
    {
        $this->slugify = $slugify;
    }

    public function getPersistentParameters()
    {
        if($this->hasParentFieldDescription()) {
            return parent::getPersistentParameters();
        }

        $categories = $this->getPostHasCategoryManager()->getUniqueCategories();
        $currentCategory = null;
        if(count($categories) > 0) {
            $currentCategory = current($categories);
            $currentCategory = $currentCategory['id'];
        }

        $parameters = array(
            'category'      => $this->hasRequest() ? $this->getRequest()->get('category', $currentCategory) : $currentCategory,
            'hide_category' => $this->hasRequest() ? (int) $this->getRequest()->get('hide_category', 0) : 0
        );

        if ($this->hasSubject() || ($this->getSubject() && $this->getSubject()->getCategory())) {
            $parameters['category'] = $this->getSubject()->getCategory() ? $this->getSubject()->getCategory()->getId() : $currentCategory;
        }

        return $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewInstance()
    {
        $instance = parent::getNewInstance();

        if($this->hasParentFieldDescription()) {
            return $instance;
        }

        $category = $this->getPersistentParameter('category') ? $this->getCategoryManager()->findOneBy(array('id'=>$this->getPersistentParameter('category'))) : null;

        if($category) {
            $instance->setCategory($category);
        }

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist($object)
    {
        parent::prePersist($object);
        $object->setPost($object->getPost());
        $object->setCategory($object->getCategory());
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate($object)
    {
        parent::preUpdate($object);
        $object->setPost($object->getPost());
        $object->setCategory($object->getCategory());
    }
}
