<?php

namespace Cashbox\BoxBundle\DependencyInjection;

use Swift_Mailer;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class Mailer
{
    /**
     * @var string $mailer_user
     */
    private $mailer_user;

    /**
     * @var EngineInterface $templating
     */
    private $templating;

    /**
     * @var Swift_Mailer $mailer
     */
    private $mailer;

    /**
     * Mailer constructor.
     *
     * @param string $mailer_user
     * @param EngineInterface $templating
     * @param Swift_Mailer $mailer
     */
    public function __construct(string $mailer_user, EngineInterface $templating, Swift_Mailer $mailer)
    {
        $this->mailer_user = $mailer_user;
        $this->templating = $templating;
        $this->mailer = $mailer;
    }

    public function send(string $email, array $data)
    {
        try {
            $message = \Swift_Message::newInstance()
                ->setSubject('Кассовая операция')
                ->setFrom($this->mailer_user)
                ->setTo($email)
                ->setBody(
                    $this->templating->render(
                        'BoxBundle:Default:email.text.twig', [
                            'action' => $data['action'],
                            'type' => $data['type'],
                            'email' => $data['email'],
                            'order' => $data['order'],
                            'cash' => $data['cash'],
                            'cart' => $data['cart'],
                        ]
                    )
                )
            ;
            $this->mailer->send($message);
        } catch (\Exception $error) {
            return false;
        }

        return true;
    }
}