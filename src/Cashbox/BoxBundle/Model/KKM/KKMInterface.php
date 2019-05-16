<?php

namespace Cashbox\BoxBundle\Model\KKM;

/**
 * Interface KKMInterface
 * @package Cashbox\BoxBundle\Model\KKM
 */
interface KKMInterface
{
    /**
     * @param array $param
     * @return mixed
     */
    public function buildData(array $param);

    /**
     * @param array $data
     * @param string $from
     * @return mixed
     */
    public function send(array $data, string $from);

    /**
     * @param array $data
     * @param string $from
     * @return bool
     */
    public function sendMail(array $data, string $from);

    /**
     * @return bool
     */
    public function connect();

    /**
     * @param mixed $id
     * @return mixed
     */
    public function isQueueActive($id);
}