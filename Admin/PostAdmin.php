<?php

/*
 * This file is part of the RzNewsBundle package.
 *
 * (c) mell m. zamora <mell@rzproject.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rz\NewsBundle\Admin;

use Sonata\NewsBundle\Admin\PostAdmin as BaseAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;

class PostAdmin extends BaseAdmin
{

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('author')
            ->add('enabled')
            ->add('title')
            ->add('abstract','text')
            ->add('content', 'text', array('safe' => true))
            ->add('tags')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title', null, array('footable'=>array('attr'=>array('data_toggle'=>true))))
            ->add('collection', null, array('footable'=>array('attr'=>array('data_hide'=>'phone'))))
            ->add('enabled', null, array('editable' => true))
            ->add('publicationDateStart', null, array('footable'=>array('attr'=>array('data_hide'=>'phone,tablet'))))
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Post')
                ->add('enabled', null, array('required' => false))
                ->add('author', 'sonata_type_model_list')
                ->add('collection', 'sonata_type_model_list', array('required' => false, 'attr'=>array('class'=>'span8')))
                ->add('title', null, array('attr'=>array('class'=>'span12')))
                ->add('abstract', null, array('attr' => array('class' => 'span12', 'rows' => 5)))
                ->add('image', 'sonata_type_model_list',array('required' => false, 'attr'=>array('class'=>'span8')), array('link_parameters' => array('context' => 'news')))
                ->add('content', 'sonata_formatter_type', array(
                           'event_dispatcher' => $formMapper->getFormBuilder()->getEventDispatcher(),
                           'format_field'   => 'contentFormatter',
                           'source_field'   => 'rawContent',
                           'source_field_options'      => array(
                               'attr' => array('class' => 'span12', 'rows' => 20)
                           ),
                           'target_field'   => 'content',
                           'listener'       => true,
                       ))
            ->end()
            ->with('Tags')
                ->add('tags', 'sonata_type_model', array(
                    'required' => false,
                    'multiple' => true,
                    'select2'=>true,
                    'attr'=>array('class'=>'span12'),
                    ))
            ->end()
            ->with('Status')
                ->add('enabled', null, array('required' => false))
                ->add('commentsCloseAt')
                ->add('commentsEnabled', null, array('required' => false))
                ->add('commentsEnabled', null, array('required' => false))
                ->add('commentsDefaultStatus', 'sonata_news_comment_status', array('expanded' => true))
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title')
            ->add('enabled')
            ->add('author', null, array('field_options' => array('selectpicker_enabled'=>true)))
            ->add('tags', null, array('field_options' => array('expanded' => false, 'multiple' => true, 'selectpicker_enabled'=>true)))
            ->add('with_open_comments', 'doctrine_orm_callback', array(
//                'callback'   => array($this, 'getWithOpenCommentFilter'),
                                          'callback' => function ($queryBuilder, $alias, $field, $data) {
                                              if (!is_array($data) || !$data['value']) {
                                                  return;
                                              }

                                              $commentClass = $this->commentManager->getClass();

                                              $queryBuilder->leftJoin(sprintf('%s.comments', $alias), 'c');
                                              $queryBuilder->andWhere('c.status = :status');
                                              $queryBuilder->setParameter('status', $commentClass::STATUS_MODERATE);
                                          },
                                          'field_type' => 'checkbox'
                                      ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureSideMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
//        if (!$childAdmin && !in_array($action, array('edit'))) {
//            return;
//        }
//
//        $admin = $this->isChild() ? $this->getParent() : $this;
//
//        $id = $admin->getRequest()->get('id');
//
//        $menu->addChild(
//            $this->trans('sidemenu.link_edit_post'),
//            array('uri' => $admin->generateUrl('edit', array('id' => $id)))
//        );
//
//        $menu->addChild(
//            $this->trans('sidemenu.link_view_comments'),
//            array('uri' => $admin->generateUrl('sonata.news.admin.comment.list', array('id' => $id)))
//        );
//
//        if ($this->hasSubject() && $this->getSubject()->getId() !== null) {
//            $menu->addChild(
//                $this->trans('sidemenu.link_view_post'),
//                array('uri' => $admin->getRouteGenerator()->generate('sonata_news_view', array('permalink' => $this->permalinkGenerator->generate($this->getSubject()))))
//            );
//        }
    }
}
