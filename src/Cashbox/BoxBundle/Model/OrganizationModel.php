<?php

namespace Cashbox\BoxBundle\Model;

use Cashbox\BoxBundle\Document\Organization;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

class OrganizationModel
{
    /**
     * Get Organization by TIN
     *
     * @param Request|array $request
     * @param ManagerRegistry $managerMongoDB
     *
     * @return Organization|null
     */
    public static function getOrganization($request, ManagerRegistry $managerMongoDB): ?Organization
    {
        if ($request instanceof Request ) {
            if ($request->isMethod(Request::METHOD_POST)) {
                $tin = $request->get('inn');
            } else {
                $tin = $request->query->get('inn');
            }
        } else {
            $tin = $request['inn'];
        }

        if (!is_null($tin) && !empty($tin)) {
            /**
             * @var Organization $organization
             */
            $organization = $managerMongoDB->getManager()
                ->getRepository(Organization::class)
                ->findOneBy([
                    'tin' => $tin
                ]);
            return $organization;
        }

        return null;
    }

    /**
     * Set choice of organizations
     *
     * @param ManagerRegistry $manager
     *
     * @return array
     */
    public static function setChoiceOrganization(ManagerRegistry $manager): array
    {
        $choiceOrganizations = [];
        /**
         * @var Organization[] $organizations
         */
        $organizations = $manager->getManager()
            ->getRepository(Organization::class)
            ->findAll();

        foreach ($organizations as $organization) {
            $choiceOrganizations[$organization->getTin()] = $organization;
        }

        return $choiceOrganizations;
    }
}