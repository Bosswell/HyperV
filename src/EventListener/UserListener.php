<?php

namespace App\EventListener;

use Symfony\Contracts\EventDispatcher\Event;
use Swift_Mailer;

class UserListener
{
    /** @var Swift_Mailer */
    private $mailer;

    public function __construct(Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * TODO Send email with confirmation and link to activate account
     */
    public function onUserRegistered(Event $event)
    {

    }
}