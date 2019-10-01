<?php

namespace Cashbox\BoxBundle\Model\Payment;

use Symfony\Component\HttpFoundation\Request;

interface PaymentInterface
{
    /**
     * Sending
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function send(Request $request);

    /**
     * Checking
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function check(Request $request);
}