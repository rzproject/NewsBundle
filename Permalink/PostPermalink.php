<?php

namespace Rz\NewsBundle\Permalink;

use Sonata\NewsBundle\Model\PostInterface;
use Sonata\NewsBundle\Permalink\PermalinkInterface;

class PostPermalink implements PermalinkInterface
{
    protected $pattern;

    /**
     * @param $pattern
     */
    public function __construct($pattern = '%4$s')
    {
        $this->pattern = $pattern;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(PostInterface $post)
    {
        return sprintf($this->pattern, $post->getSlug());
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @param string $permalink
     *
     * @return array
     */
    public function getParameters($permalink)
    {


        $parameters = explode('/', $permalink);

        if (count($parameters) != 1) {
            throw new \InvalidArgumentException('wrong permalink format');
        }

        list($slug) = $parameters;

        return array(
            'slug'  => $slug
        );
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @param string $permalink
     *
     * @return array
     */
    public function getParametersWithCategory($permalink)
    {


        $parameters = explode('/', $permalink);

        if (count($parameters) < 1) {
            throw new \InvalidArgumentException('wrong permalink format');
        }

        $slug = array_pop($parameters);

        return array(
            'slug'  => $slug
        );
    }
}
