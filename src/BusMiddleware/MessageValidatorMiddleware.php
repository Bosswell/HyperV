<?php

namespace App\BusMiddleware;

use App\Exception\ValidationException;
use App\Service\MessageValidator;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class MessageValidatorMiddleware implements MiddlewareInterface
{
    /** @var MessageValidator */
    private $messageValidator;

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