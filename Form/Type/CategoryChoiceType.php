<?php

namespace Rz\NewsBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\ChoiceList\SimpleChoiceList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Sonata\NewsBundle\Model\CategoryManagerInterface;


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
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $that = $this;

        $resolver->setDefaults(array(
                                   'choice_list' => function (Options $opts, $previousValue) use ($that) {
                                       return new SimpleChoiceList($that->getChoices($opts));
                                   },
                               ));
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
     */
    public function getName()
    {
        return 'rz_news_category_choice';
    }
}
