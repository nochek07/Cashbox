<?php

namespace Cashbox\BoxBundle\Model;

use Cashbox\BoxBundle\Document\Organization;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

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
                $inn = $request->get('inn');
            } else {
                $inn = $request->query->get('inn');
            }
        } else {
            $inn = $request['inn'];
        }

        if (!is_null($inn) && !empty($inn)) {
            /**
             * @var Organization $organization
             */
            $organization = $managerMongoDB->getManager()
                ->getRepository(Organization::class)
                ->findOneBy([
                    'INN' => (int)$inn
                ]);
            return $organization;
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