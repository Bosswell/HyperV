<?php

namespace App\MessageHandler\User;

use App\Entity\User;
use App\Event\User\UserRegisteredEvent;
use App\Message\User\RegisterUserMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class RegisterUserHandler implements MessageHandlerInterface
{
    /** @var EntityManagerInterface*/
    private $entityManager;

    /** @var PasswordEncoderInterface */
    private $passwordEncoder;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $passwordEncoder,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function __invoke(RegisterUserMessage $registerUserMessage)
    {
        $user = (new User())
            ->setEmail($registerUserMessage->getEmail())
            ->setFirstName($registerUserMessage->getFirstName())
            ->setPassword($registerUserMessage->getPassword())
            ->setEmail($registerUserMessage->getEmail());

        $user->encodePassword($this->passwordEncoder);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new UserRegisteredEvent($registerUserMessage));
    }
}