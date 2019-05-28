<?php

namespace Cashbox\BoxBundle\Model\Payment;

use Cashbox\BoxBundle\Document\Organization;
use Cashbox\BoxBundle\Model\KKM\KKMInterface;
use Symfony\Component\HttpFoundation\Request;

interface PaymentInterface
{
    /**
     * Отправка
     *
     * @param Request $request
     * @param Organization $Organization - организация
     * @param KKMInterface|null $kkm - касса
     * @return mixed
     */
    public function send(Request $request, Organization $Organization, $kkm = null);

    /**
     * Проверка
     *
     * @param Request $request
     * @param Organization $Organization - организация
     * @param KKMInterface|null $kkm - касса
     * @return mixed
     */
    public function check(Request $request, Organization $Organization, $kkm = null);
}