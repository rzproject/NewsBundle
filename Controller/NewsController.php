<?php

namespace Rz\NewsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Sonata\NewsBundle\Model\CommentInterface;
use Sonata\NewsBundle\Model\PostInterface;

/**
 * Class NewsController
 * @package Rz\NewsBundle\Controller
 */
class NewsController extends Controller
{

    const NEWS_LIST_TYPE_DEFAULT = 'archive';
    const NEWS_LIST_TYPE_COLLECTION = 'collection';
    const NEWS_LIST_TYPE_CATEGORY = 'category';
    const NEWS_LIST_TYPE_TAG = 'tag';


    /**
     * @return RedirectResponse
     */
    public function homeAction()
    {
        return $this->redirect($this->generateUrl('rz_news_archive'));
    }

    /**
     * @param array $parameters
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderNewsArchive(array $parameters = array())
    {
        $request = $this->get('request_stack')->getCurrentRequest();

        $template = $this->container->get('rz_admin.template.loader')->getTemplates();
        $response = $this->render($template[sprintf('rz_news.template.archive_%s', $request->getRequestFormat())], $parameters);
        if ('rss' === $request->getRequestFormat()) {
            $response->headers->set('Content-Type', 'application/rss+xml');
        }
        return $response;
    }

    /**
     * @internal param int $page
     * @return Response
     */
    public function archiveAction()
    {
        $pager = $this->fetchNews(array());
        return $this->renderNewsArchive($this->buildParameters($pager, $this->get('request_stack')->getCurrentRequest()));

    }

    /**
     * @param int $page
     * @return Response
     */
    public function archivePagerAction($page = 1)
    {
        $pager = $this->fetchNews(array('page' => $page));
        return $this->renderNewsArchive($this->buildParameters($pager, $this->get('request_stack')->getCurrentRequest()));
    }

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

    /**
     * @param $collection
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function collectionAction($collection)
    {
        if(!$collection = $this->verifyCollection($collection)) {
            throw new NotFoundHttpException('Unable to find the collection');
        }

        return $this->renderCollectionList($collection);
    }


    /**
     * @param $page
     * @param $collection
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return \Symfony\Component\HttpFoundation\Response
     *
     */
    public function collectionPagerAction($collection, $page)
    {
        if(!$collection = $this->verifyCollection($collection)) {
            throw new NotFoundHttpException('Unable to find the collection');
        }

        return $this->renderCollectionList($collection, $page);
    }

    /**
     *
     * @param $collection
     * @param $permalink
     *
     *
     * @throws \Exception
     * @return Response
     */
    public function collectionViewAction($collection, $permalink)
    {

        if(!$collection = $this->verifyCollection($collection)) {
            throw new NotFoundHttpException('Unable to find the collection');
        }

        if ($post = $this->getPostManager()->findOneByPermalink($permalink, $this->container->get('sonata.news.blog'))) {
            try {
                return $this->renderCollectionView($post, $collection);
            } catch (\Exception $e) {
                throw $e;
            }
        } else {
            throw new NotFoundHttpException('Invalid URL');
        }
    }

    /**
     * @param string $year
     * @param string $month
     *
     * @return Response
     */
    public function archiveMonthlyAction($year, $month)
    {
        $pager = $this->fetchNews(array('date' => $this->getPostManager()->fetchPublicationDateQueryParts(sprintf('%d-%d-%d', $year, $month, 1), 'month')));
        return $this->renderNewsArchive($this->buildParameters($pager, $this->get('request_stack')->getCurrentRequest(), array('type' => 'monthly')));
    }

    /**
     *
     */
    public function archiveMonthlyPagerAction($page, $year, $month)
    {
        $pager = $this->fetchNews(array('date' => $this->getPostManager()->fetchPublicationDateQueryParts(sprintf('%d-%d-%d', $year, $month, 1), 'month'), 'page' => $page));
        return $this->renderNewsArchive($this->buildParameters($pager, $this->get('request_stack')->getCurrentRequest(), array('type' => 'monthly')));
    }

    /**
     * @param string $year
     *
     * @return Response
     */
    public function archiveYearlyAction($year)
    {

        $pager = $this->fetchNews(array('date' => $this->getPostManager()->fetchPublicationDateQueryParts(sprintf('%d-%d-%d', $year, 1, 1), 'year')));
        return $this->renderNewsArchive($this->buildParameters($pager, $this->get('request_stack')->getCurrentRequest(), array('type' => 'yearly')));

    }

    /**
     * @param $page
     * @param string $year
     *
     * @return Response
     */
    public function archiveYearlyPagerAction($page, $year)
    {

        $pager = $this->fetchNews(array('date' => $this->getPostManager()->fetchPublicationDateQueryParts(sprintf('%d-%d-%d', $year, 1, 1), 'year'), 'page' => $page));
        return $this->renderNewsArchive($this->buildParameters($pager, $this->get('request_stack')->getCurrentRequest(), array('type' => 'yearly')));

    }

    /**
     *
     * @param $permalink
     * @param string $_format
     *
     * @return Response
     */
    public function viewAction($permalink, $_format = 'html')
    {
        $post = $this->getPostManager()->findOneByPermalink($permalink, $this->container->get('sonata.news.blog'));

        if (!$post || !$post->isPublic()) {
            throw new NotFoundHttpException('Unable to find the post');
        }

        if ($seoPage = $this->getSeoPage()) {

            $seoPage
                ->setTitle($post->getTitle())
                ->addMeta('name', 'description', $post->getAbstract())
                ->addMeta('property', 'og:title', $post->getTitle())
                ->addMeta('property', 'og:type', 'blog')
                ->addMeta('property', 'og:url', $this->generateUrl('rz_news_view', array(
                    'permalink' => $this->getBlog()->getPermalinkGenerator()->generate($post, true),
                    '_format' => $_format
                ), true))
                ->addMeta('property', 'og:description', $post->getAbstract());
        }

        $template = $this->container->get('rz_admin.template.loader')->getTemplates();
        return $this->render($template['rz_news.template.view'], array(
            'post' => $post,
            'form' => false,
            'blog' => $this->get('sonata.news.blog')
        ));
    }

    /**
     * @return \Sonata\SeoBundle\Seo\SeoPageInterface
     */
    public function getSeoPage()
    {
        if ($this->has('sonata.seo.page')) {
            return $this->get('sonata.seo.page');
        }

        return null;
    }

    /**
     * @param integer $postId
     *
     * @return Response
     */
    public function commentsAction($postId)
    {
        $pager = $this->getCommentManager()
            ->getPager(array(
                'postId' => $postId,
                'status' => CommentInterface::STATUS_VALID
            ), 1, 500); //no limit

        $template = $this->container->get('rz_admin.template.loader')->getTemplates();
        return $this->render($template['rz_news.template.comments'], array(
            'pager' => $pager,
        ));
    }

    /**
     * @param $postId
     * @param bool $form
     *
     * @return Response
     */
    public function addCommentFormAction($postId, $form = false)
    {
        if (!$form) {
            $post = $this->getPostManager()->findOneBy(array(
                'id' => $postId
            ));

            $form = $this->getCommentForm($post);
        }

        $template = $this->container->get('rz_admin.template.loader')->getTemplates();
        return $this->render($template['rz_news.template.comment_form'], array(
            'form' => $form->createView(),
            'post_id' => $postId
        ));
    }

    /**
     * @param $post
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getCommentForm(PostInterface $post)
    {
        $comment = $this->getCommentManager()->create();
        $comment->setPost($post);
        $comment->setStatus($post->getCommentsDefaultStatus());

        return $this->get('form.factory')->createNamed('comment', 'rz_post_comment', $comment);
    }

    /**
     * @throws NotFoundHttpException
     *
     * @param string $id
     *
     * @return Response
     */
    public function addCommentAction($id)
    {
        $post = $this->getPostManager()->findOneBy(array(
            'id' => $id
        ));

        if (!$post) {
            throw new NotFoundHttpException(sprintf('Post (%d) not found', $id));
        }

        if (!$post->isCommentable()) {
            // todo add notice
            return new RedirectResponse($this->generateUrl('rz_news_view', array(
                'permalink' => $this->getBlog()->getPermalinkGenerator()->generate($post),
                '_format' => 'html'
            )));
        }

        $form = $this->getCommentForm($post);
        $form->bind($this->get('request'));

        if ($form->isValid()) {
            $comment = $form->getData();

            $this->getCommentManager()->save($comment);
            $this->get('sonata.news.mailer')->sendCommentNotification($comment);

            // todo : add notice
            return new RedirectResponse($this->generateUrl('rz_news_view', array(
                'permalink' => $this->getBlog()->getPermalinkGenerator()->generate($post),
                '_format' => 'html'
            )));
        }

        $template = $this->container->get('rz_admin.template.loader')->getTemplates();
        return $this->render($template['rz_news.template.view'], array(
            'post' => $post,
            'form' => $form
        ));
    }

    /**
     * @return RedirectResponse
     */
    public function categoryHomeAction()
    {
        return $this->redirect($this->generateUrl('rz_news_archive'));
    }

    /**
     * @param $category
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
     * @param $category
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

    protected function renderNewsList($parameters, $type) {

        $request = $this->get('request_stack')->getCurrentRequest();

        $template = $this->container->get('rz_admin.template.loader')->getTemplates();
        $response = $this->render($template[sprintf('rz_news.template.%s_%s', $type,  $request->getRequestFormat())], $parameters);
        if ('rss' === $request->getRequestFormat()) {
            $response->headers->set('Content-Type', 'application/rss+xml');
        }
        return $response;
    }

    protected function renderCollectionView($post, $collection) {

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
                ->addMeta('property', 'og:url',  $this->generateUrl('rz_news_collection_view', array(
                    'collection'  => $collection->getSlug(),
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

    protected function renderCollectionList($collection, $page = null) {

        $parameters = array('collection' => $collection);

        if($page) {
            $parameters['page'] = $page;
        }

        $pager = $this->fetchNews($parameters);
        $parameters = $this->buildParameters($pager, $this->get('request_stack')->getCurrentRequest(), array('collection' => $collection));

        return $this->renderNewsList($parameters, self::NEWS_LIST_TYPE_COLLECTION);
    }

    protected function verifyCollection($collection) {
        $collection = $this->get('sonata.classification.manager.collection')->findOneBy(array(
            'slug' => $collection,
            'enabled' => true
        ));

        if (!$collection) {
            return false;
        }

        if (!$collection->getEnabled()) {
            return false;
        }

        return $collection;
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

    /**
     * @return \Sonata\ClassificationBundle\Model\CategoryManagerInterface
     */
    protected function getCategoryManager()
    {
        return $this->get('sonata.classification.manager.category');
    }

    /**
     * @return \Sonata\NewsBundle\Model\PostManagerInterface
     */
    protected function getPostManager()
    {
        return $this->get('sonata.news.manager.post');
    }

    /**
     * @return \Sonata\NewsBundle\Model\CommentManagerInterface
     */
    protected function getCommentManager()
    {
        return $this->get('sonata.news.manager.comment');
    }

    /**
     * @return \Sonata\NewsBundle\Model\BlogInterface
     */
    protected function getBlog()
    {
        return $this->container->get('sonata.news.blog');
    }

    /**
     * @param string $commentId
     * @param string $hash
     * @param string $status
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function commentModerationAction($commentId, $hash, $status)
    {
        $comment = $this->getCommentManager()->findOneBy(array('id' => $commentId));

        if (!$comment) {
            throw new AccessDeniedException();
        }

        $computedHash = $this->get('sonata.news.hash.generator')->generate($comment);

        if ($computedHash != $hash) {
            throw new AccessDeniedException();
        }

        $comment->setStatus($status);

        $this->getCommentManager()->save($comment);

        return new RedirectResponse($this->generateUrl('rz_news_view', array(
            'permalink'  => $this->getBlog()->getPermalinkGenerator()->generate($comment->getPost()),
            '_format' => 'html'
        )));
    }

    protected function fetchNews(array $criteria = array()) {

        if(array_key_exists('page', $criteria)) {
            $page = $criteria['page'];
            unset($criteria['page']);
        } else {
            $page = 1;
        }


        $pager = $this->getPostManager()->getNewsPager($criteria);
        $pager->setMaxPerPage($this->container->hasParameter('rz_news.settings.news_pager_max_per_page')?$this->container->getParameter('rz_news.settings.news_pager_max_per_page'): 5);
        $pager->setCurrentPage($page, false, true);
        return $pager;
    }

    protected function buildParameters($pager, $request, $parameters = array()) {

        return array_merge(array(
                'pager' => $pager,
                'blog'  => $this->get('sonata.news.blog'),
                'tag'   => false,
                'route' => $request->get('_route'),
                'route_parameters' => $request->get('_route_params'),
                'type'  => 'none')
            ,$parameters);
    }
}
