<?php

namespace Cashbox\BoxBundle\Admin\Report;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollection;

class ReportAdmin extends AbstractAdmin
{
    protected $baseRoutePattern = 'report';
    protected $baseRouteName = 'report';

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(['period']);
    }
}