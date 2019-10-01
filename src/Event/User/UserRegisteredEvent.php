<?php

namespace App\Event\User;

use App\Dto\User\UserRegister;
use Symfony\Contracts\EventDispatcher\Event;

class UserRegisteredEvent extends Event
{
    private $userRegisterDto;

    public function __construct(UserRegister $userRegisterDto)
    {
        $this->userRegisterDto = $userRegisterDto;
    }

    public function getUser(): UserRegister
    {
       return $this->userRegisterDto;
    }
}