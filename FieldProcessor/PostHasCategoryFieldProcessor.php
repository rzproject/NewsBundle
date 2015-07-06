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

use Rz\SearchBundle\FieldProcessor\AbstractFieldProcessor;

class PostHasCategoryFieldProcessor extends AbstractFieldProcessor
{

    public function processFieldIndexValue($entityId, $object, $field, $options = array()) {

        $getter = 'get'.ucfirst($this->configManager->getFieldMap($entityId, $field));
        $postHasCategories = $object->$getter();
        $recordCount = count($postHasCategories);

        if(isset($options) && isset($options['count'])) {
            $count = $options['count'];
        } else {
            $count = $recordCount;
        }

        if(isset($options) && isset($options['separator'])) {
            $separator = $options['separator'];
        } else {
            $separator = ' | ';
        }

        $retval = null;

        if($recordCount > 0) {
            foreach($postHasCategories as $key => $postHasCategory) {
                if($retval) {
                    $retval = sprintf('%s%s%s', $retval, $separator, $postHasCategory->getCategory()->getName());
                } else {
                    $retval = $postHasCategory->getCategory()->getName();
                }

                if(++$key ==  $count) {
                    break;
                }
            }
        }
        return $retval;
    }
}