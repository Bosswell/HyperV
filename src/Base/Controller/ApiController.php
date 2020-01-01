<?php

namespace App\Base\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Messenger\MessageBusInterface;
use League\Fractal\Manager;

abstract class ApiController extends AbstractController
{
    /** @var MessageBusInterface */
    protected $messageBus;

    /** @var Manager */
    protected $fractalManager;

    public function __construct(MessageBusInterface $messageBus, Manager $fractalManager)
    {
        $this->messageBus = $messageBus;
        $this->fractalManager = $fractalManager;
    }
}