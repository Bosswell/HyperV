<?php
declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class UniqueDto extends Constraint
{
    public $message = 'This value is already used.';

    /**
     * @var string|null
     */
    public $em = null;

    /**
     * @var string
     */
    public $entityClass;

    /**
     * @var array
     */
    public $fields;

    /**
     * @var string|null
     */
    public $errorPath = null;

    /**
     * @var string|null
     */
    public $entityField = null;

    /**
     * @var array|null
     */
    public $idFields = null;


    public function __construct ($options = null)
    {
        parent::__construct($options);

        if (!is_array($this->fields)) {
            throw new UnexpectedTypeException($this->fields, 'array');
        }

        if (!is_string($this->entityClass)) {
            throw new UnexpectedTypeException($this->entityClass, 'string');
        }

        if ($this->errorPath !== null && !is_string($this->errorPath)) {
            throw new UnexpectedTypeException($this->errorPath, 'string or null');
        }

        if (count($this->fields) < 1) {
            throw new ConstraintDefinitionException('Please specify at least one field to check');
        }

        if ($this->entityField !== null && $this->idFields !== null) {
            throw new ConstraintDefinitionException('Cannot define both entityField and idFields');
        }

        if ($this->entityField !== null && !is_string($this->entityField)) {
            throw new UnexpectedTypeException($this->entityField, 'string or null');
        }

        if ($this->idFields !== null && !is_array($this->idFields)) {
            throw new UnexpectedTypeException($this->fields, 'array or null');
        }

        if ($this->idFields !== null && count($this->idFields) < 1) {
            throw new ConstraintDefinitionException('Please specify at least one id field to check');
        }
    }

    public function getRequiredOptions ()
    {
        return ['entityClass', 'fields'];
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
