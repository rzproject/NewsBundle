<?php

namespace Rz\NewsBundle\Route;

use Symfony\Cmf\Component\Routing\ChainedRouterInterface;
use Symfony\Cmf\Component\Routing\VersatileGeneratorInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;
use Sonata\NewsBundle\Model\PostInterface;
use Sonata\NewsBundle\Model\PostManagerInterface;

class NewsRouter implements ChainedRouterInterface
{
    /**
     * @var RequestContext
     */
    protected $context;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var RedirectManagerInterface
     */
    protected $newsManager;

    /**
     * @param CmsManagerSelectorInterface $cmsSelector  Cms manager selector
     * @param SiteSelectorInterface       $siteSelector Sites selector
     * @param RouterInterface             $router       Router for hybrid pages
     */
    public function __construct(PostManagerInterface $newsManager, RouterInterface $router)
    {
        $this->newsManager = $newsManager;
        $this->router          = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function setContext(RequestContext $context)
    {
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteCollection()
    {
        return new RouteCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        throw new RouteNotFoundException('Implement generate() method');
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteDebugMessage($name, array $parameters = array())
    {
        if ($this->router instanceof VersatileGeneratorInterface) {
            return $this->router->getRouteDebugMessage($name, $parameters);
        }

        return "Route '$name' not found";
    }

    /**
     * {@inheritdoc}
     */
    public function match($pathinfo)
    {
        //$redirect = $this->redirectManager->findOneBy(array('fromPath' => $pathinfo));
//        $redirect = $this->redirectManager->findEnableRedirect(array('fromPath' => $pathinfo));


//        if ($redirect === null) {
//            throw new ResourceNotFoundException('Redirect not found!');
//        }
//        if(!$redirect->getEnabled()){
//            throw new ResourceNotFoundException('Redirect is disabled!');
//        }
        return array (
            '_controller' => 'RzNewsBundle:News:news',
            '_route'      => 'page_slug',
            //'redirect'    => $redirect,
            //'url'        => $this->decorateUrl($redirect->getToPath(), array(), self::ABSOLUTE_PATH, true),
            'params'      => array()
        );
    }

    /**
     * Decorates an URL with url context and query.
     *
     * @param string      $url           Relative URL
     * @param array       $parameters    An array of parameters
     * @param bool|string $referenceType The type of reference to be generated (one of the constants)
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected function decorateUrl($url, array $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        if (!$this->context) {
            throw new \RuntimeException('No context associated to the CmsPageRouter');
        }

        $schemeAuthority = '';
        if ($this->context->getHost() && (self::ABSOLUTE_URL === $referenceType || self::NETWORK_PATH === $referenceType)) {
            $port = '';
            if ('http' === $this->context->getScheme() && 80 != $this->context->getHttpPort()) {
                $port = sprintf(':%s', $this->context->getHttpPort());
            } elseif ('https' === $this->context->getScheme() && 443 != $this->context->getHttpsPort()) {
                $port = sprintf(':%s', $this->context->getHttpsPort());
            }

            $schemeAuthority = self::NETWORK_PATH === $referenceType ? '//' : sprintf('%s://', $this->context->getScheme());
            $schemeAuthority = sprintf('%s%s%s', $schemeAuthority, $this->context->getHost(), $port);
        }

        if (self::RELATIVE_PATH === $referenceType) {
            $url = $this->getRelativePath($this->context->getPathInfo(), $url);
        } else {
            $url = sprintf('%s%s%s', $schemeAuthority, $this->context->getBaseUrl(), $url);
        }

        if (count($parameters) > 0) {
            return sprintf('%s?%s', $url, http_build_query($parameters, '', '&'));
        }

        return $url;
    }
    /**
     * Returns the target path as relative reference from the base path.
     *
     * @param string $basePath   The base path
     * @param string $targetPath The target path
     *
     * @return string The relative target path
     */
    protected function getRelativePath($basePath, $targetPath)
    {
        return UrlGenerator::getRelativePath($basePath, $targetPath);
    }


    public function supports($name)
    {
        if (is_string($name)) {
            return false;
        }
        if (is_object($name) && !($name instanceof RedirectInterface)) {
            return false;
        }
        return true;
    }
}
