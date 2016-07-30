<?php

namespace Rz\NewsBundle\Admin;

use Sonata\NewsBundle\Admin\CommentAdmin as BaseCommentAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\CoreBundle\Model\ManagerInterface;

class CommentAdmin extends BaseCommentAdmin
{
    protected $parentAssociationMapping = 'post';

    /**
     * @var ManagerInterface
     */
    protected $commentManager;

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        // define group zoning
        $formMapper
            ->with('group_comment', array('class' => 'col-md-6'))->end()
            ->with('group_general', array('class' => 'col-md-6'))->end()
        ;

        if (!$this->isChild()) {
            $formMapper
                ->with('group_general')
                    ->add('post', 'sonata_type_model_list')
                ->end()
            ;
        }

        $formMapper
            ->with('group_general')
                ->add('name')
                ->add('email')
                ->add('url', null, array('required' => false))
            ->end()
            ->with('group_comment')
                ->add('status', 'sonata_news_comment_status', array(
                    'expanded' => true,
                    'multiple' => false,
                ))
                ->add('message', null, array('attr' => array('rows' => 6)))
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('email')
            ->add('message')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        //        $listMapper
//            ->addIdentifier('name')
//            ->add('getStatusCode', 'text', array('label' => 'status_code', 'sortable' => 'status'))
//        ;
//
//        if (!$this->isChild()) {
//            $listMapper->add('post');
//        }
//
//        $listMapper
//            ->add('email')
//            ->add('url')
//            ->add('message');
        $listMapper
            ->add('custom', 'string', array('template' => 'RzNewsBundle:CommentAdmin:list_comment_custom.html.twig', 'label' => 'Comment'))
            ->add('getStatusCode', 'text', array('label' => 'status_code', 'sortable' => 'status', 'footable'=>array('attr'=>array('data-breakpoints'=>array('all')))))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBatchActions()
    {
        $actions = parent::getBatchActions();

        $actions['enabled'] = array(
            'label'            => $this->trans($this->getLabelTranslatorStrategy()->getLabel('enable', 'batch', 'comment')),
            'ask_confirmation' => false,
        );

        $actions['disabled'] = array(
            'label'            => $this->trans($this->getLabelTranslatorStrategy()->getLabel('disable', 'batch', 'comment')),
            'ask_confirmation' => false,
        );

        return $actions;
    }

    /**
     * Update the count comment.
     */
    private function updateCountsComment()
    {
        $this->commentManager->updateCommentsCount();
    }

    /**
     * {@inheritdoc}
     */
    public function postPersist($object)
    {
        $this->updateCountsComment();
    }

    /**
     * {@inheritdoc}
     */
    public function postRemove($object)
    {
        $this->updateCountsComment();
    }

    /**
     * {@inheritdoc}
     */
    public function postUpdate($object)
    {
        $this->updateCountsComment();
    }

    /**
     * @param ManagerInterface $commentManager
     */
    public function setCommentManager(ManagerInterface $commentManager)
    {
        $this->commentManager = $commentManager;
    }
}
