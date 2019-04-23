<?php

namespace  Cashbox\BoxBundle\Model\Payment;

use Symfony\Component\HttpFoundation\Request;

interface PaymentInterface
{
    public function send(Request $request);

    public function check(Request $request);
}