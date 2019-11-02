<?php

namespace App\Service;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Exception\ValidationException;

class DtoValidator
{
    /** @var ValidatorInterface */
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param $dto
     * @throws ValidationException
     */
    public function validate($dto)
    {
        $violations = $this->validator->validate($dto);

        if ($violations->count() !== 0) {
            $errors = [];
            foreach ($violations as $violation) {
                array_push($errors, [
                    'propertyPath' => $violation->getPropertyPath(),
                    'message' => $violation->getMessage(),
                ]);
            }

            throw new ValidationException(serialize($errors));
        }
    }
}