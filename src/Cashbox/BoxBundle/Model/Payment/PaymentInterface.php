<?php

namespace Cashbox\BoxBundle\Model\Payment;

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
}