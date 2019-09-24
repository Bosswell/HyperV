<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Base\Controller\ApiController;;
use App\Exception\ValidationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use AutoMapperPlus\Exception\UnregisteredMappingException;
use App\Transformer\UserTransformer;
use League\Fractal\Resource\Item;
use App\Dto\User\UserRegister;

/**
 * TODO implement confirm()
 * Class UserController
 * @package App\Controller\Api
 * @Route("/api/users", name="user_")
 */
class UserController extends ApiController
{
    const REGISTER_MESSAGE = 'The user has been successfully created';

    /**
     * TODO emmit event and send email to confirm
     * @param UserRegister $userRegister
     * @param UserPasswordEncoderInterface $encoder
     * @param EntityManagerInterface $em
     * @return JsonResponse
     * @throws ValidationException
     * @throws UnregisteredMappingException
     *
     * @Route(name="register", methods={"POST"})
     * @ParamConverter("userRegister", converter="command_converter", class="App\Application\Command\User\UserRegister")
     */
    public function register(UserRegister $userRegister, UserPasswordEncoderInterface $encoder, EntityManagerInterface $em)
    {
        $this->dtoValidator->validate($userRegister);

        $user = $this->mapper->mapToObject($userRegister, new User());
        $user->encodePassword($encoder);

        $em->persist($user);
        $em->flush();

        $resource = new Item($user, new UserTransformer());

        $response['message'] = self::REGISTER_MESSAGE;
        $response = array_merge($response, $this->fractalManager->createData($resource)->toArray());

        return new JsonResponse(
            $response,
            Response::HTTP_CREATED
        );
    }

    /**
     * @param User $user
     * @return JsonResponse
     *
     * @Route("/{id}", name="get_one", methods={"GET"})
     * @ParamConverter("user", converter="doctrine.orm", class="App\Entity\User")
     */
    public function getOne(User $user)
    {
        $resource = new Item($user, new UserTransformer());

        return new JsonResponse(
            $this->fractalManager->createData($resource)->toArray(),
            Response::HTTP_FOUND
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
