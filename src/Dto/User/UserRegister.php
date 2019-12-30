<?php

namespace App\Dto\User;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints\UniqueMessage;

/**
 * @UniqueDto(
 *     entityClass = "App\Entity\User",
 *     fields = {"email"},
 *     message = "You can't use this email"
 * )
 */
final class UserRegister
{
    /**
     * @var string
     *
     * @Assert\NotNull(
     *     message = "You need to specify your email"
     * )
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email.",
     *     checkMX = true,
     * )
     * @Assert\Length(
     *     min = 7,
     *     max = 180,
     *     minMessage = "Your email must be at least {{ limit }} characters long",
     *     maxMessage = "Your email cannot be longer than {{ limit }} characters"
     * )
     */
    private $email;

    /**
     * @var string
     *
     * @Assert\NotNull(
     *     message = "You need to specify your first name"
     * )
     * @Assert\Length(
     *     min = 3,
     *     max = 20,
     *     minMessage = "Your first name must be at least {{ limit }} characters long",
     *     maxMessage = "Your first name be longer than {{ limit }} characters"
     * )
     */
    private $firstName;

    /**
     * @var string
     *
     * @Assert\NotNull(
     *     message = "You need to specify your surname"
     * )
     * @Assert\Length(
     *     min = 3,
     *     max = 20,
     *     minMessage = "Your surname name must be at least {{ limit }} characters long",
     *     maxMessage = "Your surname name be longer than {{ limit }} characters"
     * )
     */
    private $surname;

    /**
     * @var string
     *
     * @Assert\NotNull(
     *     message = "You need to specify your password"
     * )
     * @Assert\Length(
     *     min = 8,
     *     minMessage = "Your password must be at least {{ limit }} characters long",
     * )
     */
    private $password;

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getSurname(): string
    {
        return $this->surname;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }
}