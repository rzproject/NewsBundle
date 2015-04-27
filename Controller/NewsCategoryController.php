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
     * @param $permalink
     * @throws \Exception
     * @return RedirectResponse
     */
    public function categoryAction($permalink)
    {
        if ($category = $this->verifyCategoryPermalink($permalink)) {
            try {
                //parent category
                if($category->getParent() != null && $category->getParent()->getSlug() == 'news') {
                    $response =  $this->renderSubCategories($category);
                } else {
                    $response =  $this->renderPostByCategory($category, $permalink);
                }
            } catch(\Exception $e) {
                throw $e;
            }
            return $response;
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

                if($category->getParent() != null && $category->getParent()->getSlug() == 'news') {
                    die('rommel');
                } else {
                    $response =  $this->renderPostByCategory($category, $permalink, $page);
                }
            } catch(\Exception $e) {
                throw $e;
            }
            return $response;
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
            //parent category
            if($category->getParent() != null && $category->getParent()->getSlug() == 'news') {
                $parameters = $this->getSubCategoriesDataForView($category, $page);
            } else {
                $parameters = $this->getPostByCategoryDataForView($category, $permalink, $page);
            }


        } catch(\Exception $e) {
            throw $e;
        }

        return $this->getAjaxResponse($category, $parameters, self::NEWS_LIST_TYPE_CATEGORY);
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

            $seoPage->setTitle($post->getSetting('seoTitle', null) ? $post->getSetting('seoTitle', null) : $post->getTitle());
            $seoPage->addMeta('name', 'description', $post->getSetting('seoMetaDescription', null)? $post->getSetting('seoMetaDescription', null) : $post->getAbstract());
            if($post->getSetting('seoMetaKeyword', null)) {
                $seoPage->addMeta('name', 'keywords', $post->getSetting('seoMetaKeyword', null));
            }
            $seoPage->addMeta('property', 'og:title', $post->getSetting('ogTitle', null) ? $post->getSetting('ogTitle', null) : $post->getTitle());
            $seoPage->addMeta('property', 'og:type', $post->getSetting('ogType', null) ? $post->getSetting('ogType', null): 'Article');
            $seoPage->addMeta('property', 'og:url',  $this->generateUrl('rz_news_category_view', array(
                'category' => $this->getCategoryManager()->getPermalinkGenerator()->createSubCategorySlug($category),
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
            'category' => $category,
            'form' => false,
            'is_controller_enabled' => $this->container->getParameter('rz_classification.enable_controllers'),
            'blog' => $this->get('sonata.news.blog')
        ));
    }

    protected function getPostByCategoryDataForView($category, $permalink, $page = null) {

        $parameters = array('category' => $this->getCategoryManager()->getPermalinkGenerator()->getSlugParameters($permalink, true));

        if($page) {
            $parameters['page'] = $page;
        }

        $pager = $this->fetchNewsNative($parameters);

        if ($pager->getNbResults() <= 0) {
            throw new NotFoundHttpException('Invalid URL');
        }

        return $this->buildParameters($pager,
                                      $this->get('request_stack')->getCurrentRequest(),
                                      array('permalink' => $permalink,
                                            'category'=>$category,
                                            'is_ajax_pagination'=>$this->container->getParameter('rz_news.settings.ajax_pagination'),
                                            'enable_category_canonical_page'=>$this->container->getParameter('rz_classification.settings.category.enable_category_canonical_page')));
    }

    protected function getSubCategoriesDataForView($category, $page = null) {

        $parameters = array('category' => $category);

        if($page) {
            $parameters['page'] = $page;
        }

        $permalink = $this->getCategoryManager()->getPermalinkGenerator()->generate($category);

        $pager = $this->fetchSubCategories($parameters);

        if ($pager->getNbResults() <= 0) {
            throw new NotFoundHttpException('Invalid URL');
        }

        return $this->buildParameters($pager,
            $this->get('request_stack')->getCurrentRequest(),
            array('permalink' => $permalink,
                  'category'=>$category,
                  'is_ajax_pagination'=>$this->container->getParameter('rz_news.settings.ajax_pagination')));
    }

    protected function renderPostByCategory($category, $permalink, $page = null) {

        try {
            $parameters = $this->getPostByCategoryDataForView($category, $permalink, $page);
        } catch(\Exception $e) {
            throw $e;
        }

        $template = $category->getSetting('template');

        if ($seoPage = $this->getSeoPage()) {
            $request = $this->get('request_stack')->getCurrentRequest();

            if($category->getSetting('seoTitle', null)) {
                $seoPage->setTitle($category->getSetting('seoTitle', null));
            }

            if($category->getSetting('seoMetaDescription', null)) {
                $seoPage->addMeta('name', 'description', $category->getSetting('seoMetaDescription', null));
            }

            if($category->getSetting('seoMetaKeyword', null)) {
                $seoPage->addMeta('name', 'keywords', $category->getSetting('seoMetaKeyword', null));
            }

            if($category->getSetting('ogTitle', null)) {
                $seoPage->addMeta('property', 'og:title', $category->getSetting('ogTitle', null));
            }

            $seoPage->addMeta('property', 'og:type', $category->getSetting('ogType', null) ? $category->getSetting('ogType', null): 'Article');


            $seoPage->addMeta('property', 'og:url',  $this->generateUrl('rz_news_category', array(
                'permalink'  => $category->getSlug(),
                '_format' => $request->getRequestFormat()
            ), true));

            $seoPage->setLinkCanonical($this->generateUrl('rz_news_archive', array(), true));

            if($category->getSetting('ogDescription', null)) {
                $seoPage->addMeta('property', 'og:description', $category->getSetting('ogDescription', null));
            }
        }

        if($template && $this->getTemplating()->exists($template) ) {
            return $this->render($template, $parameters);
        } else {
            //TODO create fallback template for category
            return $this->renderNewsList($parameters, self::NEWS_LIST_TYPE_CATEGORY);
        }
    }

    protected function renderSubCategories($category, $page = null) {

        try {
            $parameters = $this->getSubCategoriesDataForView($category, $page);
        } catch(\Exception $e) {
            throw $e;
        }

        $manager = $this->getCmsManagerSelector()->retrieve();
        $manager->getCurrentPage()->setTemplateCode($this->container->getParameter('rz_classification.settings.category.parent_category_page_template'));

        $template = $category->getSetting('template');

        if ($seoPage = $this->getSeoPage()) {
            $request = $this->get('request_stack')->getCurrentRequest();

            if($category->getSetting('seoTitle', null)) {
                $seoPage->setTitle($category->getSetting('seoTitle', null));
            }

            if($category->getSetting('seoMetaDescription', null)) {
                $seoPage->addMeta('name', 'description', $category->getSetting('seoMetaDescription', null));
            }

            if($category->getSetting('seoMetaKeyword', null)) {
                $seoPage->addMeta('name', 'keywords', $category->getSetting('seoMetaKeyword', null));
            }

            if($category->getSetting('ogTitle', null)) {
                $seoPage->addMeta('property', 'og:title', $category->getSetting('ogTitle', null));
            }

            $seoPage->addMeta('property', 'og:type', $category->getSetting('ogType', null) ? $category->getSetting('ogType', null): 'Article');


            $seoPage->addMeta('property', 'og:url',  $this->generateUrl('rz_news_category', array(
                'permalink'  => $category->getSlug(),
                '_format' => $request->getRequestFormat()
            ), true));

            if($category->getSetting('ogDescription', null)) {
                $seoPage->addMeta('property', 'og:description', $category->getSetting('ogDescription', null));
            }
        }

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

    public function getCmsManagerSelector() {

        if ($this->has('sonata.page.cms_manager_selector')) {
            return $this->get('sonata.page.cms_manager_selector');
        }

        return null;
    }

    protected function fetchSubCategories(array $criteria = array()) {

        if(array_key_exists('page', $criteria)) {
            $page = $criteria['page'];
            unset($criteria['page']);
        } else {
            $page = 1;
        }
        $pager = $this->getCategoryManager()->getSubCategoryPager($criteria['category']->getId());
        $pager->setMaxPerPage($this->container->hasParameter('rz_classification.settings.category.category_list_max_per_page')?$this->container->getParameter('rz_classification.settings.category.category_list_max_per_page'): 6);
        $pager->setCurrentPage($page, false, true);
        return $pager;
    }
}
