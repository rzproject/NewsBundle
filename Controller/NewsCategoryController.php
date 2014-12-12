<?php

namespace Rz\NewsBundle\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class NewsController
 * @package Rz\NewsBundle\Controller
 */
class NewsCategoryController extends AbstractNewsController
{

    const NEWS_LIST_TYPE_CATEGORY = 'category';

    /**
     * @return RedirectResponse
     */
    public function categoryHomeAction()
    {
        return $this->redirect($this->generateUrl('rz_news_archive'));
    }

    /**
     * @param $permalink
     * @throws \Exception
     * @return RedirectResponse
     */
    public function categoryAction($permalink)
    {
        if ($category = $this->verifyCategoryPermalink($permalink)) {
            return $this->renderCategoryList($category, $permalink);
        } else {
            throw new NotFoundHttpException('Invalid URL');
        }
    }

    /**
     * @param $permalink
     * @throws \Exception
     * @return RedirectResponse
     */
    public function categoryPagerAction($permalink, $page)
    {
        if ($category = $this->verifyCategoryPermalink($permalink)) {
            return $this->renderCategoryList($category, $permalink, $page);
        } else {
            throw new NotFoundHttpException('Invalid URL');
        }
    }


    /**
     *
     * @param $category
     * @param $permalink
     *
     * @throws \Exception
     * @return Response
     */
    public function categoryViewAction($category, $permalink)
    {
        if (!$category = $this->verifyCategoryPermalink($category)) {
            throw new NotFoundHttpException('Invalid URL');
        }

        if ($post = $this->getPostManager()->findOneByPermalink($permalink, $this->container->get('sonata.news.blog'))) {
            try {
                return $this->renderCategoryView($post, $category);
            } catch (\Exception $e) {
                throw $e;
            }
        } else {
            throw new NotFoundHttpException('Invalid URL');
        }
    }


    protected function renderCategoryView($post, $category) {

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
                ->addMeta('property', 'og:url',  $this->generateUrl('rz_news_category_view', array(
                    'category' => $this->getCategoryManager()->getPermalinkGenerator()->createSubCategorySlug($category),
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

    protected function renderCategoryList($category, $permalink, $page = null) {

        $parameters = array('category' => $this->getCategoryManager()->getPermalinkGenerator()->getSlugParameters($permalink));

        if($page) {
            $parameters['page'] = $page;
        }
        $pager = $this->fetchNews($parameters);
        $parameters = $this->buildParameters($pager, $this->get('request_stack')->getCurrentRequest(), array('permalink' => $permalink, 'category'=>$category));
        return $this->renderNewsList($parameters, self::NEWS_LIST_TYPE_CATEGORY);
    }

    protected function verifyCategoryPermalink($permalink) {

        $category = $this->getCategoryManager()->getCategoryByPermalink($permalink);

        if (!$category || !$category->getEnabled()) {
            return null;
        }

        if ($category && (!$this->getCategoryManager()->getPermalinkGenerator()->validatePermalink($category, $permalink))) {
            return null;
        }

        return $category;
    }
}
