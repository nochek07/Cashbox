<?php

namespace Cashbox\BoxBundle\Model\Payment;

use Cashbox\BoxBundle\DependencyInjection\{Report, Mailer};
use Cashbox\BoxBundle\Document\Organization;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

interface PaymentInterface
{
    /**
     * Отправка
     *
     * @param Request $request
     * @return mixed
     */
    public function send(Request $request);

    /**
     * Проверка
     *
     * @param Request $request
     * @return mixed
     */
    public function check(Request $request);

    /**
     * @param ManagerRegistry $manager
     * @return mixed
     */
    public function setManager(ManagerRegistry $manager);

    /**
     * @param Organization $Organization
     * @return mixed
     */
    public function setOrganization(Organization $Organization);

    /**
     * @param Report $report
     * @return mixed
     */
    public function setReport(Report $report);

    /**
     * @param Mailer $mailer
     * @return mixed
     */
    public function setMailer(Mailer $mailer);
}