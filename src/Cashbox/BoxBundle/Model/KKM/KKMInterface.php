<?php

namespace Cashbox\BoxBundle\Model\KKM;

interface KKMInterface
{
    /**
     * KKM connect
     *
     * @return bool
     */
    public function connect();

    /**
     * Building data for fiscalization
     *
     * @param array $param
     *
     * @return array
     */
    public function buildData(array $param): array;

    /**
     * Sending data
     *
     * @param array $data
     * @param string $type - type of payment
     *
     * @return mixed
     */
    public function send(array $data, string $type);

    /**
     * Sending a receipt to mail
     *
     * @param array $data
     * @param string $type - type of payment
     *
     * @return bool
     */
    public function sendMail(array $data, string $type): bool;

    /**
     * Checking a queue
     *
     * @param mixed $id
     *
     * @return bool
     */
    public function isQueueActive($id): bool;

    /**
     * Checking the availability of cash
     *
     * @return bool
     */
    public function checkKKM(): bool;
}