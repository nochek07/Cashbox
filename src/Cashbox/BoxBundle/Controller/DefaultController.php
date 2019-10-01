<?php

namespace Cashbox\BoxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{
    /**
     * Home page
     *
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        return $this->redirect(
            $this->getParameter('redirect_url')
        );
    }
}