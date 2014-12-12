<?php

namespace Rz\NewsBundle\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class NewsController
 * @package Rz\NewsBundle\Controller
 */
class NewsTagController extends AbstractNewsController
{

    const NEWS_LIST_TYPE_TAG = 'tag';

    /**
     * @param string $tag
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function tagAction($tag)
    {
        if(!$tag = $this->verifyTag($tag)) {
            throw new NotFoundHttpException('Unable to find the tag');
        }

        return $this->renderTagList($tag);
    }

    /**
     * @param $page
     * @param string $tag
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return \Symfony\Component\HttpFoundation\Response
     *
     */
    public function tagPagerAction($tag, $page)
    {
        if(!$tag = $this->verifyTag($tag)) {
            throw new NotFoundHttpException('Unable to find the tag');
        }

        return $this->renderTagList($tag, $page);
    }

    /**
     *
     * @param $tag
     * @param $permalink
     *
     *
     * @throws \Exception
     * @return Response
     */
    public function tagViewAction($tag, $permalink)
    {

        if(!$tag = $this->verifyTag($tag)) {
            throw new NotFoundHttpException('Unable to find the collection');
        }

        if ($post = $this->getPostManager()->findOneByPermalink($permalink, $this->container->get('sonata.news.blog'))) {
            try {
                return $this->renderTagView($post, $tag);
            } catch (\Exception $e) {
                throw $e;
            }
        } else {
            throw new NotFoundHttpException('Invalid URL');
        }
    }


    protected function renderTagView($post, $tag) {

        if (!$post || !$post->isPublic()) {
            throw new NotFoundHttpException('Unable to find the post');
        }

        if ($seoPage = $this->getSeoPage()) {
            $request = $this->get('request_stack')->getCurrentRequest();
            $seoPage
                ->setTitle($post->getTitle())
                ->addMeta('name', 'description', $post->getAbstract())
                ->addMeta('property', 'og:title', $post->getTitle())
                ->addMeta('property', 'og:type', 'blog')
                ->addMeta('property', 'og:url',  $this->generateUrl('rz_news_tag_view', array(
                    'tag'  => $tag->getSlug(),
                    'permalink'  => $this->getBlog()->getPermalinkGenerator()->generate($post, true),
                    '_format' => $request->getRequestFormat()
                ), true))
                ->addMeta('property', 'og:description', $post->getAbstract())
                ->setLinkCanonical($this->generateUrl('rz_news_view', array(
                    'permalink'  => $this->getBlog()->getPermalinkGenerator()->generate($post, true),
                    '_format' => $request->getRequestFormat()
                ), true))
            ;
        }

        $template = $this->container->get('rz_admin.template.loader')->getTemplates();
        return $this->render($template['rz_news.template.view'], array(
            'post' => $post,
            'form' => false,
            'blog' => $this->get('sonata.news.blog')
        ));
    }

    protected function renderTagList($tag, $page = null) {

        $parameters = array('tag' => $tag);

        if($page) {
            $parameters['page'] = $page;
        }

        $pager = $this->fetchNews($parameters);
        $parameters = $this->buildParameters($pager, $this->get('request_stack')->getCurrentRequest(), array('tag' => $tag));

        return $this->renderNewsList($parameters, self::NEWS_LIST_TYPE_TAG);
    }

    protected function verifyTag($tag) {
        $tag = $this->get('sonata.classification.manager.tag')->findOneBy(array(
            'slug' => $tag,
            'enabled' => true
        ));

        if (!$tag) {
            return false;
        }

        if (!$tag->getEnabled()) {
            return false;
        }

        return $tag;
    }
}
