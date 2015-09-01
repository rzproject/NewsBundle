<?php

namespace Rz\NewsBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\ChoiceList\SimpleChoiceList;
use Rz\NewsBundle\Form\Type\TreeList\TreeList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Sonata\NewsBundle\Model\CategoryManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;


class CategoryChoiceType extends AbstractType
{
    protected $manager;

    /**
     * @param CategoryManagerInterface $manager
     */
    public function __construct(CategoryManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['current'] = $options['current'] ?: null;
        $view->vars['tree_enabled'] = $options['tree_enabled'] ?: null;
        $view->vars['expanded'] = $options['expanded'] ?: null;
    }

    /**
     * @param Options $options
     *
     * @return array
     */
    public function getChoices(Options $options)
    {
        $categories = $this->manager->fetchCategories();
        $choices = array();
        foreach ($categories as $category) {
            $choices[$category->getId()] = $category;
        }
        return $choices;
    }

    /**
     * {@inheritdoc}
     *
     * @todo Remove it when bumping requirements to SF 2.7+
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->configureOptions($resolver);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $that = $this;
        $resolver->setDefaults(
            array(
                'expanded' => true,
                'current' => null,
                'tree_enabled' => true,
                'choice_list' => function (Options $opts, $previousValue) use ($that) {
                    return new SimpleChoiceList($that->getChoices($opts));
                },
            ));
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return 'sonata_type_model';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'rz_news_category_choice';
    }
}
