<?php

namespace Cashbox\BoxBundle\Model\Payment;

use Cashbox\BoxBundle\Document\Payment;
use Cashbox\BoxBundle\Model\Type\OtherTypes;
use Cashbox\BoxBundle\Model\KKM\{KKMInterface, KKMMessages};
use Symfony\Component\HttpFoundation\Request;

class For1CPayment extends YandexPayment
{
    protected $name = OtherTypes::PAYMENT_TYPE_1C;

    /**
     * @var array
     */
    private $dataJSON = [];

    /**
     * {@inheritDoc}
     *
     * @return string
     */
    public function send(Request $request)
    {
        $payment = $this->getDesiredPayment(
            $this->Organization->getOthers()
        );
        if ($payment instanceof Payment) {
            if ($this->check1cMD5($this->dataJSON, $this->Organization->getSecret())) {
                $kkm = $this->getKkmByPayment($payment);
                if ($kkm instanceof KKMInterface) {
                    if (!$kkm->checkKKM()) {
                        return $this->buildResponse('For1C', 0, 100, null, KKMMessages::MSG_CASHBOX_UNAV);
                    }
                }

                $repository = $this->getManager()
                    ->getRepository('BoxBundle:ReportKKMp');
                $report = $repository->findOneBy([
                    'type' => OtherTypes::PAYMENT_TYPE_1C,
                    'action' => $this->dataJSON["action"],
                    'uuid' => $this->dataJSON["uuid"]
                ]);

                if (is_null($report) && $kkm instanceof KKMInterface) {
                    $error = $kkm->send($this->dataJSON, OtherTypes::PAYMENT_TYPE_1C);
                } else {
                    $error = KKMMessages::MSG_ERROR_CHECK;
                }

                return $this->buildResponse('For1C', 0, 100, null, $error);
            }
        }
        return $this->buildResponse('For1C', 0, 100, null, KKMMessages::MSG_ERROR_HASH);
    }

    /**
     * {@inheritDoc}
     *
     * @return string
     */
    public function check(Request $request)
    {
        $payment = $this->getDesiredPayment(
            $this->Organization->getOthers()
        );
        if ($payment instanceof Payment) {
            $kkm = $this->getKkmByPayment($payment);
            if ($kkm instanceof KKMInterface) {
                if (!$kkm->checkKKM()) {
                    return $this->buildResponse('For1C', 0, 100, null, KKMMessages::MSG_CASHBOX_UNAV);
                }
            }
        }
        return $this->buildResponse('For1C', 0, 0, null, null);
    }

    /**
     * Checking the MD5 sign.
     *
     * @param array $data payment parameters
     * @param string $secret secret word
     *
     * @return bool true if MD5 hash is correct
     */
    private function check1cMD5(array $data, string $secret)
    {
        $hash = $data["action"] . ';' . $secret . ';';
        if (isset($data["kkm"]["payment"]["card"])) {
            $hash .= $data["kkm"]["payment"]["card"] . ';';
        }
        if (isset($data["kkm"]["payment"]["cash"])) {
            $hash .= $data["kkm"]["payment"]["cash"] . ';';
        }
        $hash .= $data["order"] . ';' . $data["inn"] . ';';

        if (strtolower(md5($hash)) === $data["hash"]) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Set DataJSON
     *
     * @param array $data
     *
     * @return self
     */
    public function setDataJSON(array $data)
    {
        $this->dataJSON = $data;
        return $this;
    }
}