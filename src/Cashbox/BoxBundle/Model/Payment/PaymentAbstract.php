<?php

namespace Cashbox\BoxBundle\Model\Payment;

use Cashbox\BoxBundle\Document\Organization;
use Cashbox\BoxBundle\Model\KKM\KKMInterface;
use Cashbox\BoxBundle\Model\Payment\Exception\KKMException;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

abstract class PaymentAbstract implements PaymentInterface
{
    /**
     * @var ManagerRegistry $manager
     */
    protected $manager;

    /**
     * PaymentAbstract constructor.
     *
     * @param ManagerRegistry $manager
     */
    public function __construct(ManagerRegistry $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritDoc}
     */
    abstract public function send(Request $request, Organization $Organization, $kkm = null);

    /**
     * {@inheritDoc}
     */
    abstract public function check(Request $request, Organization $Organization, $kkm = null);

    /**
     * Дополнительная проверка
     *
     * @param Request $request
     * @param String $handling_secret - секретное слово
     * @return bool
     */
    public function otherCheckMD5(Request $request, $handling_secret)
    {
        return ($request->get('h') != md5($request->get('customerNumber') . "_"
                . $request->get('orderSumAmount') . "_" . $handling_secret));
    }

    /**
     * Check KKM
     *
     * @param $queueName
     * @param null $kkm
     * @throws KKMException
     */
    public function checkKKM($queueName, $kkm = null)
    {
        if ($kkm instanceof KKMInterface) {
            if ($kkm->connect()) {
                if (!$kkm->isQueueActive($queueName)) {
                    throw new KKMException('KKM error');
                }
            } else {
                throw new KKMException('KKM error');
            }
        }
    }
}