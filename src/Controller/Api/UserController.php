<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Base\Controller\ApiController;;

use App\EventListener\UserListener;
use App\Exception\ValidationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use AutoMapperPlus\Exception\UnregisteredMappingException;
use App\Transformer\UserTransformer;
use League\Fractal\Resource\Item;
use App\Dto\User\UserRegister;
use App\Event\User\UserRegisteredEvent;

/**
 * TODO implement confirm()
 * Class UserController
 * @package App\Controller\Api
 * @Route("/api/users", name="user_")
 */
class UserController extends ApiController
{
    const REGISTER_MESSAGE = 'The user has been successfully created';
    const ACTIVATE_MESSAGE = 'The user has been successfully activated';

    /**
     * @throws ValidationException
     * @throws UnregisteredMappingException
     *
     * @Route(name="register", methods={"POST"})
     * @ParamConverter("userRegister", converter="dto_converter", class="App\Dto\User\UserRegister")
     */
    public function register(
        UserRegister $userRegister,
        UserPasswordEncoderInterface $encoder,
        EntityManagerInterface $em,
        EventDispatcherInterface $eventDispatcher
    ): JsonResponse
    {
        $this->dtoValidator->validate($userRegister);

        $user = $this->mapper->mapToObject($userRegister, new User());
        $user->encodePassword($encoder);

        $em->persist($user);
        $em->flush();
        $eventDispatcher->dispatch(new UserRegisteredEvent($userRegister));

        $resource = new Item($user, new UserTransformer());

        $response['message'] = self::REGISTER_MESSAGE;
        $response = array_merge($response, $this->fractalManager->createData($resource)->toArray());

        return new JsonResponse(
            $response,
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", name="get", methods={"GET"})
     * @ParamConverter("user", converter="doctrine.orm", class="App\Entity\User")
     */
    public function getOne(User $user): JsonResponse
    {
        $resource = new Item($user, new UserTransformer());

        return new JsonResponse(
            $this->fractalManager->createData($resource)->toArray(),
            Response::HTTP_FOUND
        );
    }

    /**
     * Activation does not need any hash or checksum cuz of UUID
     *
     * @Route("/activate/{id}", name="activate", methods={"PATCH"})
     * @ParamConverter("user", converter="doctrine.orm", class="App\Entity\User")
     */
    public function activate(User $user, EntityManagerInterface $em): JsonResponse
    {
        $user->activate();
        $em->persist($user);
        $em->flush();

        return new JsonResponse([
            'message' => self::ACTIVATE_MESSAGE
            ],
            Response::HTTP_OK
        );
    }

//    /**
//     * @param User $user
//     * @param UserUpdate $userUpdate
//     * @throws ValidationException
//     * @throws UnregisteredMappingException
//     *
//     * @Route("/{id}", name="update", methods={"PUT"})
//     * @ParamConverter("user", converter="doctrine.orm", class="App\Entity\User")
//     * @ParamConverter("userUpdate", converter="command_converter", class="App\Application\Command\User\UserUpdate")
//     */
//    public function update(User $user, UserUpdate $userUpdate)
//    {
//        $this->validateCommand($user);
//        $user = $this->mapper->mapToObject($userUpdate, $user);

//        $resource = new Item($user, new UserTransformer());
//
//        return new JsonResponse(
//            $this->fractalManager->createData($resource)->toArray(),
//            Response::HTTP_FOUND
//        );
//    }

//    public function edit()
//    {
//
//    }
}
