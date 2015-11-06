<?php

namespace Rz\NewsBundle\Admin;

use Sonata\NewsBundle\Admin\PostAdmin as Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;

class PostAdmin extends Admin
{
    protected $datagridValues = array(
        '_page' => 1,
        '_sort_order' => 'DESC',
        '_sort_by' => 'publicationDateStart',
    );

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('group_post', array(
                    'class' => 'col-md-8',
                ))
                ->add('author', 'sonata_type_model_list')
                ->add('title')
                ->add('abstract', null, array('attr' => array('rows' => 5)))
                ->add('content', 'sonata_formatter_type', array(
                    'event_dispatcher'          => $formMapper->getFormBuilder()->getEventDispatcher(),
                    'format_field'              => 'contentFormatter',
                    'source_field'              => 'rawContent',
                    'source_field_options'      => array(
                        'horizontal_input_wrapper_class' => $this->getConfigurationPool()->getOption('form_type') == 'horizontal' ? 'col-lg-12' : '',
                        'attr'                           => array('class' => $this->getConfigurationPool()->getOption('form_type') == 'horizontal' ? 'span10 col-sm-10 col-md-10' : '', 'rows' => 20),
                    ),
                    'ckeditor_context'     => 'news',
                    'target_field'         => 'content',
                    'listener'             => true,
                ))
            ->end()
            ->with('group_status', array(
                    'class' => 'col-md-4',
                ))
                ->add('enabled', null, array('required' => false))
                ->add('image', 'sonata_type_model_list', array('required' => false), array(
                    'link_parameters' => array(
                        'context'      => 'news',
                        'hide_context' => true,
                    ),
                ))
                ->add('publicationDateStart', 'sonata_type_datetime_picker', array('dp_side_by_side' => true))
            ->end()

            ->with('group_classification', array(
                'class' => 'col-md-4',
                ))
                ->add('tags', 'sonata_type_model_autocomplete', array(
                    'property' => 'name',
                    'multiple' => 'true',
                    'required' => false,
                    'callback' => function ($admin, $property, $value) {
                        $datagrid = $admin->getDatagrid();
                        $queryBuilder = $datagrid->getQuery();
                        $queryBuilder
                            ->andWhere($queryBuilder->getRootAlias() . '.context=:context')
                            ->setParameter('context', 'news')
                        ;
                        $datagrid->setValue($property, null, $value);
                    },
                ))
                ->add('collection', 'sonata_type_model_list', array('required' => false), array('link_parameters'=>array('context'=>'news', 'hide_context'=>true)))
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('custom', 'string', array('template' => 'RzNewsBundle:PostAdmin:list_post_custom.html.twig', 'label' => 'Post'))
            ->add('enabled', null, array('editable' => true, 'footable'=>array('attr'=>array('data-breakpoints'=>array('all')))))
            ->add('publicationDateStart', null, array('footable'=>array('attr'=>array('data-breakpoints'=>array('all')))))
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureSideMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        if (!$childAdmin && !in_array($action, array('edit'))) {
            return;
        }

        $admin = $this->isChild() ? $this->getParent() : $this;

        $id = $admin->getRequest()->get('id');

        $menu->addChild(
            $this->trans('sidemenu.link_edit_post'),
            array('uri' => $admin->generateUrl('edit', array('id' => $id)))
        );

        if ($this->hasSubject() && $this->getSubject()->getId() !== null) {
            $menu->addChild(
                $this->trans('sidemenu.link_view_post'),
                array('uri' => $admin->getRouteGenerator()->generate('sonata_news_view', array('permalink' => $this->permalinkGenerator->generate($this->getSubject()))))
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title')
            ->add('enabled')
            ->add('tags', null, array('field_options' => array('expanded' => true, 'multiple' => true)))
            ->add('author')
            ->add('publicationDateStart', 'doctrine_orm_datetime_range', array('field_type' => 'sonata_type_datetime_range_picker'))
        ;
    }
}
