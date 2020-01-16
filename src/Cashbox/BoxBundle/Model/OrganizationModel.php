<?php

namespace Cashbox\BoxBundle\Model;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Cashbox\BoxBundle\Document\Organization;

class OrganizationModel
{
    /**
     * Get Organization by INN
     *
     * @param Request|array $request
     * @param ManagerRegistry $managerMongoDB
     *
     * @return Organization|null
     */
    public static function getOrganization($request, ManagerRegistry $managerMongoDB)
    {
        if ($request instanceof Request ) {
            if ($request->isMethod(Request::METHOD_POST)) {
                $INN = $request->get('inn');
            } else {
                $INN = $request->query->get('inn');
            }
        } else {
            $INN = $request['inn'];
        }

        if (!is_null($INN) && !empty($INN)) {
            return $managerMongoDB->getManager()
                ->getRepository('BoxBundle:Organization')
                ->findOneBy([
                    'INN' => (int)$INN
                ]);
        }

        return null;
    }

    /**
     * Set choice or organizations
     *
     * @param ManagerRegistry $manager
     *
     * @return array
     */
    public static function setChoiceOrganization(ManagerRegistry $manager)
    {
        $choicesOrganizations = [];
        /**
         * @var Organization[] $Organizations
         */
        $Organizations = $manager->getManager()
            ->getRepository(Organization::class)
            ->findAll();

        foreach ($Organizations as $Organization) {
            $choicesOrganizations[$Organization->getINN()] = $Organization;
        }

        return $choicesOrganizations;
    }
}