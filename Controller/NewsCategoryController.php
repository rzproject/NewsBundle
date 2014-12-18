<?php

namespace Rz\NewsBundle\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

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

            try {
                return $this->renderCategoryList($category, $permalink, $page);
            } catch(\Exception $e) {
                throw $e;
            }

        } else {
            throw new NotFoundHttpException('Invalid URL');
        }
    }

    public function categoryAjaxPagerAction($permalink, $page) {

        if (!$category = $this->verifyCategoryPermalink($permalink)) {
            throw new NotFoundHttpException('Invalid URL');
        }

        //redirect to normal controller if not ajax
        if (!$this->get('request_stack')->getCurrentRequest()->isXmlHttpRequest()) {
            return $this->redirect($this->generateUrl('rz_news_category_pager', array('permalink'=>$permalink, 'page'=>$page)), 301);
        }

        try {
            $parameters = $this->getCategoryDataForView($category, $permalink, $page);
        } catch(\Exception $e) {
            throw $e;
        }

        //for now reuse the template name TODO:implement on settings
        $template = $category->getSetting('template');
        $templates = $this->getAjaxTemplates($template);
        $templateAjax = $templates['ajax_template'];
        $templatePagerAjax = $templates['ajax_pager'];

        if($template && $this->getTemplating()->exists($template) &&
            $templateAjax && $this->getTemplating()->exists($templateAjax) &&
            $templatePagerAjax && $this->getTemplating()->exists($templatePagerAjax)) {

            $html = $this->container->get('templating')->render($templateAjax, $parameters);
            $html_pager = $this->container->get('templating')->render($templatePagerAjax, $parameters);
            return new JsonResponse(array('html' => $html, 'html_pager'=>$html_pager));

        } else {
            $defaultTemplate = $this->container->get('rz_admin.template.loader')->getTemplates();
            $template = $defaultTemplate[sprintf('rz_news.template.%s_%s', self::NEWS_LIST_TYPE_CATEGORY,  'html')];
            $templates = $this->getAjaxTemplates($template);
            $templateAjax = $templates['ajax_template'];
            $templatePagerAjax = $templates['ajax_pager'];
            $html = $this->container->get('templating')->render($templateAjax, $parameters);
            $html_pager = $this->container->get('templating')->render($templatePagerAjax, $parameters);
            return new JsonResponse(array('html' => $html, 'html_pager'=>$html_pager));
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
            'blog' => $this->get('sonata.news.blog')
        ));
    }

    protected function getCategoryDataForView($category, $permalink, $page = null) {

        $parameters = array('category' => $this->getCategoryManager()->getPermalinkGenerator()->getSlugParameters($permalink, true));

        if($page) {
            $parameters['page'] = $page;
        }

        $pager = $this->fetchNews($parameters);

        if ($pager->getNbResults() <= 0) {
            throw new NotFoundHttpException('Invalid URL');
        }

        return $this->buildParameters($pager, $this->get('request_stack')->getCurrentRequest(), array('permalink' => $permalink, 'category'=>$category, 'is_ajax_pagination'=>$this->container->getParameter('rz_news.settings.ajax_pagination')));
    }

    protected function renderCategoryList($category, $permalink, $page = null) {

        try {
            $parameters = $this->getCategoryDataForView($category, $permalink, $page);
        } catch(\Exception $e) {
            throw $e;
        }

        $template = $category->getSetting('template');

        if($template && $this->getTemplating()->exists($template) ) {
            return $this->render($template, $parameters);
        } else {
            return $this->renderNewsList($parameters, self::NEWS_LIST_TYPE_CATEGORY);
        }
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
