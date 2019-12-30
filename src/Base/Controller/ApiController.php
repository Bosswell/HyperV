<?php

namespace App\Base\Controller;

use App\Dto\User\UserRegister;
use App\Entity\User;
use App\Service\MessageValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use AutoMapperPlus\Configuration\AutoMapperConfig;
use AutoMapperPlus\AutoMapper;
use League\Fractal\Manager;

abstract class ApiController extends AbstractController
{
    /** @var MessageValidator */
    protected $dtoValidator;

    /** @var AutoMapper */
    protected $mapper;

    /** @var Manager */
    protected $fractalManager;


    public function __construct(MessageValidator $dtoValidator, Manager $fractalManager)
    {
        $this->dtoValidator = $dtoValidator;
        $this->fractalManager = $fractalManager;

        $this->registerMapper();
    }

    private function registerMapper(): void
    {
        $config = new AutoMapperConfig();
        $config->registerMapping(UserRegister::class, User::class);

        $this->mapper = new AutoMapper($config);
    }
}