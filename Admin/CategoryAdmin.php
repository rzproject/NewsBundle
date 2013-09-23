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

use Sonata\NewsBundle\Admin\CategoryAdmin as BaseCategoryAdmin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\ChoiceList\SimpleChoiceList;
use Sonata\NewsBundle\Model\CategoryManagerInterface;

class CategoryAdmin extends BaseCategoryAdmin
{

    protected $categoryManager;

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name', null, array('footable'=>array('attr'=>array('data_toggle'=>true))))
            ->add('slug', null, array('footable'=>array('attr'=>array('data_hide'=>'phone,tablet'))))
            ->add('enabled', null, array('editable' => true,'footable'=>array('attr'=>array('data_hide'=>'phone'))))
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('enabled', null ,array('field_options'=>array('selectpicker_enabled'=>true, 'selectpicker_data_size' => 3, 'selectpicker_dropup' => true)))
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
        ->add('name')
        ->add('parent', 'rz_type_tree',
                        array(
                          'choice_list' => new SimpleChoiceList($this->getChoices()),
                          'model_manager' => $this->getModelManager(),
                          'class'         => $this->getClass(),
                          'required'      => false,
                          'current'      => $this->getSubject() ?: null))
        ->add('description', null, array('required' => false))
        ->add('enabled', null, array('required' => false))
        ;
    }

    /**
     *
     * @return array
     */
    public function getChoices()
    {
        $categories = $this->categoryManager->fetchCategories();
        $choices = array();
        foreach ($categories as $category) {
            $choices[$category->getId()] = $category;
        }
        return $choices;
    }

    public function setCategoryManager(CategoryManagerInterface $categoryManager) {
        $this->categoryManager = $categoryManager;
    }
}
