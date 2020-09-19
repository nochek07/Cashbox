<?php

namespace Cashbox\BoxBundle\Model\Payment;

use Cashbox\BoxBundle\Document\Payment;
use Cashbox\BoxBundle\Model\{Till\TillInterface, Till\TillMessages, Type\OtherTypes};
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
            $this->organization->getOthers()
        );
        if ($payment instanceof Payment) {
            if ($this->check1cMD5($this->dataJSON, $this->organization->getSecret())) {
                $till = $this->getTillByPayment($payment);
                if ($till instanceof TillInterface) {
                    if (!$till->checkTill()) {
                        return $this->buildResponse('For1C', 0, 100, null, TillMessages::MSG_CASHBOX_UNAV);
                    }
                }

                $repository = $this->getManager()
                    ->getRepository('BoxBundle:TillReport');
                $report = $repository->findOneBy([
                    'type' => OtherTypes::PAYMENT_TYPE_1C,
                    'action' => $this->dataJSON["action"],
                    'uuid' => $this->dataJSON["uuid"]
                ]);

                if (is_null($report) && $till instanceof TillInterface) {
                    $error = $till->send($this->dataJSON, OtherTypes::PAYMENT_TYPE_1C);
                } else {
                    $error = TillMessages::MSG_ERROR_CHECK;
                }

                return $this->buildResponse('For1C', 0, 100, null, $error);
            }
        }
        return $this->buildResponse('For1C', 0, 100, null, TillMessages::MSG_ERROR_HASH);
    }

    /**
     * {@inheritDoc}
     *
     * @return string
     */
    public function check(Request $request)
    {
        $payment = $this->getDesiredPayment(
            $this->organization->getOthers()
        );
        if ($payment instanceof Payment) {
            $till = $this->getTillByPayment($payment);
            if ($till instanceof TillInterface) {
                if (!$till->checkTill()) {
                    return $this->buildResponse('For1C', 0, 100, null, TillMessages::MSG_CASHBOX_UNAV);
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
    private function check1cMD5(array $data, string $secret): bool
    {
        $hash = $data["action"] . ';' . $secret . ';';
        if (isset($data["till"]["payment"]["card"])) {
            $hash .= $data["till"]["payment"]["card"] . ';';
        }
        if (isset($data["till"]["payment"]["cash"])) {
            $hash .= $data["till"]["payment"]["cash"] . ';';
        }
        $hash .= $data["order"] . ';' . $data["inn"] . ';';

        return (strtolower(md5($hash)) === $data["hash"]);
    }

    /**
     * Set DataJSON
     *
     * @param array $data
     *
     * @return self
     */
    public function setDataJSON(array $data): self
    {
        $this->dataJSON = $data;
        return $this;
    }
}