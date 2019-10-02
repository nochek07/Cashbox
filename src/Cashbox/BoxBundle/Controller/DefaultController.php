<?php

namespace Cashbox\BoxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{
    /**
     * Homepage
     *
     * @Route("/", name="homepage")
     */
    public function index()
    {
        return $this->redirect(
            $this->getParameter('redirect_url')
        );
    }
}