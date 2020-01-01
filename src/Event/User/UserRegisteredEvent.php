<?php

namespace App\Event\User;

use App\Message\User\RegisterUserMessage;
use Symfony\Contracts\EventDispatcher\Event;

class UserRegisteredEvent extends Event
{
    private $registerUserMessage;

    public function __construct(RegisterUserMessage $registerUserMessage)
    {
        $this->registerUserMessage = $registerUserMessage;
    }

    public function getUser(): RegisterUserMessage
    {
       return $this->registerUserMessage;
    }
}