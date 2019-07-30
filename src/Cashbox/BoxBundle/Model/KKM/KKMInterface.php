<?php

namespace Cashbox\BoxBundle\Model\KKM;

interface KKMInterface
{
    /**
     * Соединение с ККМ
     *
     * @return bool
     */
    public function connect();

    /**
     * Создания массива данных для фискализации
     *
     * @param array $param
     * @return array
     */
    public function buildData(array $param);

    /**
     * Отправка данных
     *
     * @param array $data
     * @param string $from
     * @return mixed
     */
    public function send(array $data, string $from);

    /**
     * Отправка чека на почту
     *
     * @param array $data
     * @param string $from
     * @return bool
     */
    public function sendMail(array $data, string $from);

    /**
     * Проверка очереди
     *
     * @param mixed $id
     * @return mixed
     */
    public function isQueueActive($id);
}