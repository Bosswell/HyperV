<?php

namespace App\Base\Controller;

use App\Exception\ValidationException;
use App\Service\DtoValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use AutoMapperPlus\Configuration\AutoMapperConfig;
use AutoMapperPlus\AutoMapper;
use League\Fractal\Manager;

abstract class ApiController extends AbstractController
{
    /** @var DtoValidator */
    protected $dtoValidator;

    /** @var AutoMapper */
    protected $mapper;

    /** @var Manager */
    protected $fractalManager;

    /**
     * ApiController constructor.
     * @param DtoValidator $dtoValidator
     * @param Manager $fractalManager
     */
    public function __construct(DtoValidator $dtoValidator, Manager $fractalManager)
    {
        $this->dtoValidator = $dtoValidator;
        $this->fractalManager = $fractalManager;

        $this->registerMapper();
    }

    private function registerMapper()
    {
//        $config = new AutoMapperConfig();
//        $config->registerMapping(UserRegister::class, User::class);
//        $config->registerMapping(UserUpdate::class, User::class);
//
//        $this->mapper = new AutoMapper($config);
    }
}