<?php

namespace Rz\NewsBundle\Entity;

use Sonata\NewsBundle\Entity\BasePost as BasePost;
use Sonata\NewsBundle\Model\PostInterface;

abstract class post extends BasePost
{
    /**
     * @param mixed $image
     */
    public function setImage ($image)
    {
        $this->image = $image;
    }

    /**
     * @return mixed
     */
    public function getImage ()
    {
        return $this->image;
    }

}
