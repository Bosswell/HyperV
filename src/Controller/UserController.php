<?php

namespace App\Controller;

use App\Entity\User;
use App\Base\Controller\ApiController;;
use App\Message\User\ChangeUserStateMessage;
use App\Message\User\RegisterUserMessage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\Transformer\UserTransformer;
use League\Fractal\Resource\Item;

/**
 * @Route("/users", name="user_")
 */
class UserController extends ApiController
{
    /**
     * @Route(name="register", methods={"POST"})
     * @ParamConverter("registerUserMessage", converter="message_converter", class="App\Message\User\RegisterUserMessage")
     */
    public function register(RegisterUserMessage $registerUserMessage): JsonResponse
    {
        $this->messageBus->dispatch($registerUserMessage);

        return new JsonResponse(
            ['message' => 'You have been successfully registered'],
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
     * Activation/Deactivation does not need any hash or checksum cuz of UUID
     *
     * @Route("/change-state/{id}", name="changeState", methods={"PATCH"})
     * @ParamConverter("changeUserStateMessage", converter="message_converter", class="App\Message\User\ChangeUserStateMessage")
     */
    public function changeState(ChangeUserStateMessage $changeUserStateMessage): JsonResponse
    {
        $this->messageBus->dispatch($changeUserStateMessage);

        return new JsonResponse([
            'message' => sprintf('Your account have been successfully %s', $changeUserStateMessage->getState() ? 'activated' : 'deactivated')
        ],
            Response::HTTP_OK
        );
    }
}
