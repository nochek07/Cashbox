<?php

namespace Cashbox\BoxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

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