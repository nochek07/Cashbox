<?php

namespace Cashbox\BoxBundle\Form;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ReportByPeriodForm
{
    /**
     * @var \DateTime
     */
    private $dateStart;

    /**
     * @var \DateTime
     */
    private $dateEnd;

    /**
     * @var string
     */
    private $INN;

    /**
     * @return \DateTime
     */
    public function getDateStart()
    {
        return $this->dateStart;
    }

    /**
     * @param \DateTime $dateStart
     * @return self
     */
    public function setDateStart($dateStart)
    {
        $this->dateStart = $dateStart;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateEnd()
    {
        return $this->dateEnd;
    }

    /**
     * @param \DateTime $dateEnd
     * @return self
     */
    public function setDateEnd($dateEnd)
    {
        $this->dateEnd = $dateEnd;
        return $this;
    }

    /**
     * @return string
     */
    public function getINN()
    {
        return $this->INN;
    }

    /**
     * @param string $INN
     * @return self
     */
    public function setINN($INN)
    {
        $this->INN = $INN;
        return $this;
    }
    
    /**
     * @param ExecutionContextInterface $context
     * @Assert\Callback()
     */
    public function validate(ExecutionContextInterface $context)
    {
        $diffDate = $this->getDateStart()
            ->diff($this->getDateEnd());

        if ($diffDate->invert == 1) {
            $context->buildViolation('period.error')
                ->atPath('dateEnd')
                ->addViolation()
            ;
        }
    }
}