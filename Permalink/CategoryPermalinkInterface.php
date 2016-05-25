<?php

namespace Rz\NewsBundle\Permalink;

use Rz\NewsBundle\Model\PostHasCategoryInterface;

interface CategoryPermalinkInterface
{
    /**
     * @param PostInterface $post
     */
    public function generate(PostHasCategoryInterface $postHasCategory, $parentCategorySlug = 'news');

    /**
     * @param string $permalink
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    public function getParameters($permalink);
}
