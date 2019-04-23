<?php

namespace Cashbox\BoxBundle\Model\Payment;

use Symfony\Component\HttpFoundation\Request;

abstract class PaymentAbstract implements PaymentInterface
{
    abstract public function send(Request $request);
    abstract public function check(Request $request);

    /**
     * Дополнительная проверка
     *
     * @param Request $request
     * @param String $handling_secret
     * @return bool
     */
    public function otherCheckMD5(Request $request, $handling_secret){
        return ( $request->get('h') != md5($request->get('customerNumber') . "_" . $request->get('orderSumAmount') . "_" . $handling_secret) );
    }
}