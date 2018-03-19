<?php

namespace Cashbox\UserBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RequestListener
{
    /**
     * @var ContainerInterface $container
     */
    private $container;

    /**
     * RequestListener constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        // force ssl based on authentication
        //if ($this->container->get("security.context_listener.0")->isGranted('IS_AUTHENTICATED_FULLY')) {
            //if (!$request->isSecure()) {
                //$request->server->set('HTTPS', true);
                //$request->server->set('SERVER_PORT', 443);
                //$event->setResponse(new RedirectResponse($request->getUri()));
            //}
        /*} else {
            if ($request->isSecure()) {
                $request->server->set('HTTPS', false);
                $request->server->set('SERVER_PORT', 80);
                $event->setResponse(new RedirectResponse($request->getUri()));
            }
        } */
    }
}