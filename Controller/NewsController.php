<?php

namespace Rz\NewsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NewsController extends Controller
{
    public function viewAction(Request $request, $permalink, $_format = 'html')
    {
        # Use parse URL to make sure you have a valid URL string
        $path = parse_url($permalink);
        if ($path && is_array($path)) {
            $paths = array_reverse(explode('/', $path['path']));
        }

        $url = $request->get('url');
        return $this->redirect($url, 301);
    }
}
