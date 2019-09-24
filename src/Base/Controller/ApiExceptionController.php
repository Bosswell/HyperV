<?php

namespace App\Base\Controller;

use App\Exception\ValidationException;
use Symfony\Bundle\TwigBundle\Controller\ExceptionController;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;

class ApiExceptionController extends ExceptionController
{
    private $errorMap = [
        AccessDeniedException::class => [
            'code' => 'ACCESS_DENIED',
            'message' => 'Access denied',
        ],
        ValidationException::class => [
            'code' => 'VALIDATION_FAIL',
            'message' => 'Validation failed',
        ],
        MethodNotAllowedHttpException::class => [
            'code' => 'METHOD_NOT_ALLOWED',
            'message' => 'Method is not allowed for the requested route',
        ],
        NotFoundHttpException::class => [
            'code' => 'NOT_FOUND',
            'message' => 'Not found',
        ]
    ];

    public function showAction(Request $request, FlattenException $exception, DebugLoggerInterface $logger = null)
    {
        $response = new JsonResponse($this->getError($exception));

        if ($exception->getCode() !== 0) {
            $response->setStatusCode($exception->getCode());
        }

        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');

        return $response;
    }

    public function getError(FlattenException $exception): array
    {
        $error = [];

        $error['code'] = $this->errorMap[$exception->getClass()]['code'] ?? 'UNKNOWN_ERROR';
        $error['message'] = $this->errorMap[$exception->getClass()]['message'] ?? 'Unknown error';

        if ($exception->getClass() === ValidationException::class) {
            $error['errors'] = unserialize($exception->getMessage());
        }

        if ($this->twig->isDebug()) {
            $error['debug'] = sprintf('%s in file %s on line %d',
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            );
        }

        return $error;
    }
}