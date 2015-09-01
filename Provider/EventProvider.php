<?php

namespace Rz\NewsBundle\Provider;

use Sonata\NewsBundle\Model\PostInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\CoreBundle\Validator\ErrorElement;

class EventProvider extends DefaultProvider
{

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     * @return array
     */
    public function getFormSettingsKeys(FormMapper $formMapper)
    {
        return array_merge(parent::getFormSettingsKeys($formMapper),array(
            array('start_date', 'date', array('required' => false,'input'=>'array')),
            array('end_date', 'date', array('required' => false,'input'=>'array')),
            array('location', 'rz_google_maps', array('required' => false)),
            array('address', 'textarea', array('required' => false, 'attr'=>array('class'=>'span8'))),
        ));
    }
}
