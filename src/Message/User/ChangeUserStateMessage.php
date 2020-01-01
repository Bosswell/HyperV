<?php

namespace App\Message\User;

use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ChangeUserStateMessage
{
    /**
     * @var UuidInterface
     *
     * @Assert\Type(
     *     type="Ramsey\Uuid\UuidInterface",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     */
    private $userId;

    /**
     * @var bool
     *
     * @Assert\Type(
     *     type="bool",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     */
    private $state;

    /**
     * @return UuidInterface
     */
    public function getUserId(): UuidInterface
    {
        return $this->userId;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }
}