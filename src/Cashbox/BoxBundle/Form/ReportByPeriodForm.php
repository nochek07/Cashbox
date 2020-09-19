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
    private $tin;

    public function getDateStart(): ?\DateTime
    {
        return $this->dateStart;
    }

    public function setDateStart(\DateTime $dateStart): self
    {
        $this->dateStart = $dateStart;
        return $this;
    }

    public function getDateEnd(): ?\DateTime
    {
        return $this->dateEnd;
    }

    public function setDateEnd(\DateTime $dateEnd): self
    {
        $this->dateEnd = $dateEnd;
        return $this;
    }

    public function getTin(): ?string
    {
        return $this->tin;
    }

    public function setTin(string $tin): self
    {
        $this->tin = $tin;
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