<?php

namespace Cashbox\BoxBundle\Model\Till;

interface TillInterface
{
    /**
     * Connect to Till
     */
    public function connect(): bool;

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
     * @param array $data data of payment
     * @param string $type type of payment
     *
     * @return mixed
     */
    public function send(array $data, string $type);

    /**
     * Sending a receipt to mail
     *
     * @param array $data data of payment
     * @param string $type type of payment
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
     */
    public function checkTill(): bool;
}