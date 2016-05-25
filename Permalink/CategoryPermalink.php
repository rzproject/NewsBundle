<?php

namespace Rz\NewsBundle\Permalink;

use Rz\NewsBundle\Model\PostHasCategoryInterface;

class CategoryPermalink implements CategoryPermalinkInterface
{
    /**
     * {@inheritdoc}
     */
    public function generate(PostHasCategoryInterface $postHasCategory, $parentCategorySlug = 'news')
    {
        $category = $postHasCategory->getCategory();
        #TODO : for verification
        $categories = [];
        $categories = $this->parentWalker($category, $parentCategorySlug, $categories);
        krsort($categories);
        $slug = '';
        foreach($categories as $category) {
            if(empty($slug)) {
                $slug = $category->getSlug();
            } else {
                $slug .= '/'.$category->getSlug();
            }
        }

        $post = $postHasCategory->getPost();
        return sprintf('%s/%s', $slug, $post->getSlug());
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters($permalink)
    {

        #TODO : for verification
        $parameters = explode('/', $permalink);

        if (count($parameters) > 1 || count($parameters) == 0) {
            throw new \InvalidArgumentException('wrong permalink format');
        }

        list($slug) = $parameters;

        return array(
            'slug'  => $slug
        );
    }

    public function parentWalker($category, $parentCategorySlug = 'news', &$categories=array()) {

        while ($category->getParent() && $category->getSlug() != $parentCategorySlug) {
            $categories[] = $category;
            return $this->parentWalker($category->getParent(), $parentCategorySlug, $categories);
        }
        return $categories;
    }
}
