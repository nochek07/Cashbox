<?php
/**
 * Created by PhpStorm.
 * User: kirill
 * Date: 02.09.17
 * Time: 21:01
 */

namespace Cashbox\BoxBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Id;
use Doctrine\ODM\MongoDB\Mapping\Annotations\date;

/**
 * @Annotation
 * @MongoDB/Document
 *
 */
class YandexTransaction
{
    /**
     * @MongoDB\Id(strategy="INCREMENT")
     */
    protected $id;

    /**
     * @MongoDB\Field(type="date")
     */
    protected $datetime;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $action;

    /**
     * @MongoDB\Field(type="float")
     */
    protected $Sum;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $email;

    /**
     * @MongoDB\Field(type="hash")
     */
    protected $dataPost;

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set datetime
     *
     * @return $this
     */
    public function setDatetime()
    {
        $this->datetime = new \DateTime();
        return $this;
    }

    /**
     * Get Sum
     *
     * @return date $datetime
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * Set action
     *
     * @param string $action
     * @return $this
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * Get action
     *
     * @return string $action
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set Sum
     *
     * @param float $Sum
     * @return $this
     */
    public function setSum($Sum)
    {
        $this->Sum = $Sum;
        return $this;
    }

    /**
     * Get Sum
     *
     * @return float $Sum
     */
    public function getSum()
    {
        return $this->Sum;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get email
     *
     * @return string $email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set dataPost
     *
     * @param array $dataPost
     * @return $this
     */
    public function setDataPost($dataPost)
    {
        $this->dataPost = $dataPost;
        return $this;
    }

    /**
     * Get dataPost
     *
     * @return array $dataPost
     */
    public function getDataPost()
    {
        return $this->dataPost;
    }
}
