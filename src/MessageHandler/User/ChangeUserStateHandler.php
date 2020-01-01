<?php

namespace App\MessageHandler\User;

use App\Entity\User;
use App\Message\User\ChangeUserStateMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ChangeUserStateHandler implements MessageHandlerInterface
{
    /** @var EntityManagerInterface*/
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(ChangeUserStateMessage $message)
    {
        /** @var User $user */
        $user = $this->entityManager->find(User::class, $message->getUserId());

        if (is_null($user)) {
            throw new NotFoundHttpException('User has not been found');
        }

        $user->changeState($message->getState());
    }
}