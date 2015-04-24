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
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function tagAction($tag)
    {
        if(!$tag = $this->verifyTag($tag)) {
            throw new NotFoundHttpException('Unable to find the tag');
        }

        try {
            $response = $this->renderTagList($tag);
        } catch (\Exception $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * @param string $tag
     *
     * @param $page
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function tagPagerAction($tag, $page)
    {
        if(!$tag = $this->verifyTag($tag)) {
            throw new NotFoundHttpException('Unable to find the tag');
        }

        try {
            $response = $this->renderTagList($tag);
        } catch (\Exception $e) {
            throw $e;
        }

        return $response;
    }

    public function tagAjaxPagerAction($tag, $page) {

        if(!$tag = $this->verifyTag($tag)) {
            throw new NotFoundHttpException('Unable to find the tag');
        }

        //redirect to normal controller if not ajax
        if (!$this->get('request_stack')->getCurrentRequest()->isXmlHttpRequest()) {
            return $this->redirect($this->generateUrl('rz_news_tag_pager', array('tag'=>$tag->getSlug(), 'page'=>$page)), 301);
        }

        try {
            $parameters = $this->getTagDataForView($tag, $page);
        } catch(\Exception $e) {
            throw $e;
        }

        return $this->getAjaxResponse($tag, $parameters, self::NEWS_LIST_TYPE_TAG);
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

            $seoPage->setTitle($post->getSetting('seoTitle', null) ? $post->getSetting('seoTitle', null) : $post->getTitle());
            $seoPage->addMeta('name', 'description', $post->getSetting('seoMetaDescription', null)? $post->getSetting('seoMetaDescription', null) : $post->getAbstract());
            if($post->getSetting('seoMetaKeyword', null)) {
                $seoPage->addMeta('name', 'keywords', $post->getSetting('seoMetaKeyword', null));
            }
            $seoPage->addMeta('property', 'og:title', $post->getSetting('ogTitle', null) ? $post->getSetting('ogTitle', null) : $post->getTitle());
            $seoPage->addMeta('property', 'og:type', $post->getSetting('ogType', null) ? $post->getSetting('ogType', null): 'Article');
            $seoPage->addMeta('property', 'og:url',  $this->generateUrl('rz_news_tag_view', array(
                'tag'  => $tag->getSlug(),
                'permalink'  => $this->getBlog()->getPermalinkGenerator()->generate($post, true),
                '_format' => $request->getRequestFormat()
            ), true));
            $seoPage->addMeta('property', 'og:description', $post->getSetting('ogDescription', null) ? $post->getSetting('ogDescription', null) : $post->getAbstract());
            $seoPage->setLinkCanonical($this->generateUrl('rz_news_view', array(
                    'permalink'  => $this->getBlog()->getPermalinkGenerator()->generate($post, true),
                    '_format' => $request->getRequestFormat()
                ), true))
            ;
        }

        //set default template
        $template = $this->getFallbackTemplate();

        $viewTemplate = $post->getSetting('template');

        if($viewTemplate) {
            if ($this->getTemplating()->exists($template)) {
                $template = $viewTemplate;
            } else {
                //get generic template
                $pool = $this->getNewsPool();
                $defaultTemplateName = $pool->getDefaultTemplateNameByCollection($pool->getDefaultDefaultCollection());
                $defaultViewTemplate = $pool->getTemplateByCollection($defaultTemplateName);

                if($defaultViewTemplate) {
                    $template = $viewTemplate['path'];
                }
            }
        }

        return $this->render($template, array(
            'post' => $post,
            'form' => false,
            'tag'  => $tag,
            'is_controller_enabled' => $this->container->getParameter('rz_classification.enable_controllers'),
            'blog' => $this->get('sonata.news.blog')
        ));
    }

    protected function renderTagList($tag, $page = null) {

        try {
            $parameters = $this->getTagDataForView($tag, $page);
        } catch(\Exception $e) {
            throw $e;
        }

        $template = $tag->getSetting('template');


        if ($seoPage = $this->getSeoPage()) {
            $request = $this->get('request_stack')->getCurrentRequest();

            if($tag->getSetting('seoTitle', null)) {
                $seoPage->setTitle($tag->getSetting('seoTitle', null));
            }

            if($tag->getSetting('seoMetaDescription', null)) {
                $seoPage->addMeta('name', 'description', $tag->getSetting('seoMetaDescription', null));
            }

            if($tag->getSetting('seoMetaKeyword', null)) {
                $seoPage->addMeta('name', 'keywords', $tag->getSetting('seoMetaKeyword', null));
            }

            if($tag->getSetting('ogTitle', null)) {
                $seoPage->addMeta('property', 'og:title', $tag->getSetting('ogTitle', null));
            }

            $seoPage->addMeta('property', 'og:type', $tag->getSetting('ogType', null) ? $tag->getSetting('ogType', null): 'Article');

            $seoPage->addMeta('property', 'og:url',  $this->generateUrl('rz_news_tag', array(
                'tag'  => $tag->getSlug(),
                '_format' => $request->getRequestFormat()
            ), true));

            if($tag->getSetting('ogDescription', null)) {
                $seoPage->addMeta('property', 'og:description', $tag->getSetting('ogDescription', null));
            }
        }

        if($template && $this->getTemplating()->exists($template) ) {
            return $this->render($template, $parameters);
        } else {
            return $this->renderNewsList($parameters, self::NEWS_LIST_TYPE_TAG);
        }
    }

    protected function getTagDataForView($tag, $page = null) {

        $parameters = array('tag' => $tag);

        if($page) {
            $parameters['page'] = $page;
        }

        $pager = $this->fetchNews($parameters);

        if ($pager->getNbResults() <= 0) {
            throw new NotFoundHttpException('Invalid URL');
        }

        return $this->buildParameters($pager, $this->get('request_stack')->getCurrentRequest(), array('tag' => $tag,
                                                                                                      'is_ajax_pagination'=>$this->container->getParameter('rz_news.settings.ajax_pagination'),
                                                                                                      'is_controller_enabled' => $this->container->getParameter('rz_classification.enable_controllers')));

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
