<?php

namespace App\Middleware;

use App\Exception\ValidationException;
use App\Service\MessageValidator;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final class MessageValidatorMiddleware implements MiddlewareInterface
{
    private MessageValidator $messageValidator;

    public function __construct(MessageValidator $messageValidator)
    {
        $this->messageValidator = $messageValidator;
    }

    /**
     * @throws ValidationException
     */
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();
        $this->messageValidator->validate($message);

        return $stack->next()->handle($envelope, $stack);
    }
}

