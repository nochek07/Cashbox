<?php

namespace Cashbox\BoxBundle\Model;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Cashbox\BoxBundle\Document\Organization;

class OrganizationModel
{
    /**
     * @param Request|array $request
     * @param ManagerRegistry $managerMongoDB
     * @return null|Organization
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