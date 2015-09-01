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
class NewsCommentController extends AbstractNewsController
{

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
}
