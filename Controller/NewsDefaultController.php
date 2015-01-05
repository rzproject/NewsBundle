<?php

namespace Rz\NewsBundle\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class NewsController
 * @package Rz\NewsBundle\Controller
 */
class NewsDefaultController extends AbstractNewsController
{

    const NEWS_LIST_TYPE_DEFAULT = 'archive';

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

    /**
     * @return \Sonata\NewsBundle\Model\CommentManagerInterface
     */
    protected function getPostHasMediaManager()
    {
        return $this->get('rz_news.manager.post_has_media');
    }
}
