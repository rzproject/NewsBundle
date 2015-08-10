<?php

namespace Rz\NewsBundle\Provider;

use Sonata\NewsBundle\Model\PostInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\CoreBundle\Validator\ErrorElement;

class AdsProvider extends DefaultProvider
{

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     * @return array
     */
    public function getFormSettingsKeys(FormMapper $formMapper)
    {
        return array_merge(parent::getFormSettingsKeys($formMapper),array(
            array('ads', 'sonata_formatter_type', array(
                'event_dispatcher' => $formMapper->getFormBuilder()->getEventDispatcher(),
                'error_bubbling' => false,
                'format_field'   => 'contentFormatter',
                'source_field'   => 'rawContent',
                'ckeditor_context' => 'news',
                'source_field_options'      => array(
                    'error_bubbling'=>false,
                    'attr' => array('rows' => 20)
                ),
                'target_field'   => 'content',
                'listener'       => true,
            ))
        ));
    }
}
