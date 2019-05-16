<?php

namespace Cashbox\BoxBundle\Model\Payment;

use Cashbox\BoxBundle\Document\Organization;
use Cashbox\BoxBundle\Model\KKM\KKMInterface;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PaymentAbstract
 * @package Cashbox\BoxBundle\Model\Payment
 */
abstract class PaymentAbstract implements PaymentInterface
{
    /**
     * @var ManagerRegistry $manager
     */
    protected $manager;

    /**
     * SberbankPayment constructor.
     *
     * @param ManagerRegistry $manager
     */
    public function __construct(ManagerRegistry $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param Request $request
     * @param Organization $Organization
     * @param KKMInterface|null $kkm
     * @return mixed
     */
    abstract public function send(Request $request, Organization $Organization, $kkm = null);

    /**
     * @param Request $request
     * @param Organization $Organization
     * @param KKMInterface|null $kkm
     * @return mixed
     */
    abstract public function check(Request $request, Organization $Organization, $kkm = null);

    /**
     * Дополнительная проверка
     *
     * @param Request $request
     * @param String $handling_secret
     * @return bool
     */
    public function otherCheckMD5(Request $request, $handling_secret){
        return ($request->get('h') != md5($request->get('customerNumber') . "_" . $request->get('orderSumAmount') . "_" . $handling_secret));
    }
}