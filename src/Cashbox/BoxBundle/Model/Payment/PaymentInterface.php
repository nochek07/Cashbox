<?php

namespace Cashbox\BoxBundle\Model\Payment;

use Cashbox\BoxBundle\Document\Organization;
use Cashbox\BoxBundle\Model\KKM\KKMInterface;
use Symfony\Component\HttpFoundation\Request;

interface PaymentInterface
{
    /**
     * @param Request $request
     * @param Organization $Organization
     * @param KKMInterface|null $kkm
     * @return mixed
     */
    public function send(Request $request, Organization $Organization, $kkm = null);

    /**
     * @param Request $request
     * @param Organization $Organization
     * @param KKMInterface|null $kkm
     * @return mixed
     */
    public function check(Request $request, Organization $Organization, $kkm = null);
}