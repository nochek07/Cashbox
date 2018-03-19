<?php

namespace Cashbox\BoxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{

    /**
     * @Route("/test")
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function testAction(Request $request)
    {
        return new Response('<html><body>test</body></html>');
    }

    /**
     * @Route("/")
     */
    public function indexAction()
    {
        return $this->redirect($this->getParameter('redirect_url'));
    }
}
