<?php

namespace App\Service;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Exception\ValidationException;

class MessageValidator
{
    /** @var ValidatorInterface */
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @throws ValidationException
     */
    public function validate(object $message)
    {
        $violations = $this->validator->validate($message);

        if ($violations->count() !== 0) {
            $errors = [];
            foreach ($violations as $violation) {
                array_push($errors, [
                    'propertyPath' => $violation->getPropertyPath(),
                    'message' => $violation->getMessage(),
                ]);
            }

            throw new ValidationException(json_encode($errors));
        }
    }
}
