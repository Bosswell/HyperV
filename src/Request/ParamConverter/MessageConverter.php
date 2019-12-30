<?php

namespace App\Request\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception;

final class MessageConverter implements ParamConverterInterface
{
    /**
     * @param Request $request
     * @param ParamConverter $configuration
     * @return bool
     * @throws Exception\ExceptionInterface
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $serializer = new Serializer([new PropertyNormalizer()], [new JsonEncoder()]);
        $data = $request->getContent();
        $dto = $serializer->deserialize($data, $configuration->getClass(), 'json');
        $request->attributes->set($configuration->getName(), $dto);

        return true;
    }

    /**
     * @param ParamConverter $configuration
     * @return bool
     */
    public function supports(ParamConverter $configuration)
    {
        return true;
    }
}