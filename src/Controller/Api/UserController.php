<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Base\Controller\ApiController;;
use App\Exception\ValidationException;
use App\WebCrawler\Selector;
use App\WebCrawler\SelectorCollection;
use App\WebCrawler\WebCrawlerFacade;
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
 * @Route("/api/users", name="user_")
 */
class UserController extends ApiController
{
    const REGISTER_MESSAGE = 'The user has been successfully created';
    const ACTIVATE_MESSAGE = 'The user has been successfully activated';
    const DEACTIVATE_MESSAGE = 'The user has been successfully deactivated';

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

    /**
     * @Route("/activate/{id}", name="deactivate", methods={"PATCH"})
     * @ParamConverter("user", converter="doctrine.orm", class="App\Entity\User")
     */
    public function deactivate(User $user, EntityManagerInterface $em): JsonResponse
    {
        $user->deactivate();
        $em->persist($user);
        $em->flush();

        return new JsonResponse([
            'message' => self::DEACTIVATE_MESSAGE
        ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/test", name="test", methods={"GET"})
     */
    public function testCrawler()
    {
        $collection = new SelectorCollection();
        $selector = new Selector('Hello', '.header__nav > ul:nth-child(1) > li:nth-child(3) > a:nth-child(1)', Selector::CSS_TYPE);
        $collection->add($selector);

        (new WebCrawlerFacade())->extractSelectorsFromWebPage($collection, 'https://symfony.com/');


        return new JsonResponse([
            'message' => self::DEACTIVATE_MESSAGE
        ],
            Response::HTTP_OK
        );
    }
}
