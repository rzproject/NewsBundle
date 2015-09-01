<?php

namespace Rz\NewsBundle\Controller;

use Sonata\NewsBundle\Model\PostInterface;
/**
 * Interface ViewCountableControllerInterface
 * @package Rz\NewsBundle\Controller
 */
interface ViewCountableControllerInterface
{

	public function incrementPostView(PostInterface $post);

}