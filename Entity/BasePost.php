<?php

namespace Rz\NewsBundle\Entity;

use Sonata\NewsBundle\Entity\BasePost as Post;

abstract class BasePost extends Post
{
    protected $commentsDefaultStatus = true;
}
