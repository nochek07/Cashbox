<?php

namespace Cashbox\BoxBundle\Models;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Cashbox\BoxBundle\Document\Organization;

class OrganizationModel
{
    /**
     * @param Request $request
     * @return null|Organization
     */
    /**
     * @param Request|array $request
     * @param ManagerRegistry $managerMongoDB
     * @return null|object
     */
    public static function getOrganization($request, ManagerRegistry $managerMongoDB)
    {
        if($request instanceof Request ) {
            if ($request->isMethod(Request::METHOD_POST)) {
                $INN = $request->get('inn');
            } else {
                $INN = $request->query->get('inn');
            }
        } else {
            $INN = $request['inn'];
        }
        if (!is_null($INN)) {
            $repositoryOrganization = $managerMongoDB->getManager()
                ->getRepository('BoxBundle:Organization');
            return $repositoryOrganization->findOneBy([
                'INN' => (int)$INN
            ]);
        }

        return null;
    }
}