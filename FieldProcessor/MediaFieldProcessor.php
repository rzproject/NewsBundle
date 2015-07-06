<?php

/*
 * This file is part of the RzSearchBundle package.
 *
 * (c) mell m. zamora <mell@rzproject.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rz\NewsBundle\FieldProcessor;

use  Rz\SearchBundle\FieldProcessor\AbstractFieldProcessor;
use Sonata\MediaBundle\Provider\Pool;

class MediaFieldProcessor extends AbstractFieldProcessor
{
    protected $mediaService;

    public function __construct(Pool $mediaService) {
        $this->mediaService = $mediaService;
    }
    public function processFieldIndexValue($entityId, $object, $field, $options = array()) {

        $getter = 'get'.ucfirst($this->configManager->getFieldMap($entityId, $field));

        $media = $object->$getter();

        if (!$media) {
            return;
        }

        $provider = $this->mediaService->getProvider($media->getProviderName());
        #fallback will be reference if no format is provided
        $mediaFormat = isset($options['format']) ? $options['format'] : 'reference';
        $format = $provider->getFormatName($media, $mediaFormat);
        return $provider->generatePublicUrl($media, $format);
    }
}