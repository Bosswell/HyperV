<?php
declare(strict_types=1);

namespace App\Validator\Constraints;

use Closure;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Expr\Value;
use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueMessageValidator extends ConstraintValidator
{
    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @var Closure
     */
    private $privatePropertyAccessor;

    public function __construct (
        ManagerRegistry $doctrine,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->doctrine = $doctrine;
        $this->propertyAccessor = $propertyAccessor;
    }

    public function validate ($object, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueMessage) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\UniqueDto');
        }

        $this->privatePropertyAccessor = Closure::bind(
            function (string $property) {
                return $this->{$property};
            },
            $object,
            get_class($object)
        );

        $fields = $this->normalizeFields($constraint->fields);
        $values = $this->getFieldValues($fields, $object);
        $criteria = $this->addComparisonsToCriteria(
            Criteria::create(),
            $this->buildComparisons($fields, $values)
        );

        $idFields = null;

        if ($constraint->entityField !== null) {
            $entity = $this->getPropertyValue($object, $constraint->entityField);

            if ($entity !== null) {
                $em = $this->getManager($constraint);
                $metadata = $em->getClassMetadata($constraint->entityClass);

                // Fake that we have properties directly in DTO
                $idFields = $this->normalizeFields($metadata->getIdentifierFieldNames());
                $idValues = $metadata->getIdentifierValues($entity);
            }
        } elseif ($constraint->idFields !== null) {
            $idFields = $this->normalizeFields($constraint->idFields);
            $idValues = $this->getFieldValues($idFields, $object);
        }

        if ($idFields !== null) {
            /** @noinspection PhpUndefinedVariableInspection */
            $idComparisons = $this->buildComparisons($idFields, $idValues, true);
            $this->addComparisonsToCriteria($criteria, $idComparisons);
        }

        $results = $this->getRepository($constraint, $em ?? null)->matching($criteria);
        $resultsCount = $results->count();

        if ($resultsCount === 0) {
            return;
        }

        $builder = $this->context->buildViolation($constraint->message);
        $errorPath = $constraint->errorPath;

        if (count($constraint->fields) === 1) {
            $value = current($values);

            if (
                !is_array($value) && !is_resource($value) &&
                (!is_object($value) || $value instanceof \DateTimeInterface || method_exists($value, '__toString'))
            ) {
                $builder->setParameter(
                    '{{ value }}',
                    $this->formatValue($value, self::PRETTY_DATE & self::OBJECT_TO_STRING)
                );
            }

            $builder->setInvalidValue($value);

            if ($errorPath === null) {
                $errorPath = key($values);
            }
        }

        $builder
            ->atPath($errorPath)
            ->addViolation();
    }

    private function getPropertyValue ($object, $property)
    {
        if ($this->propertyAccessor->isReadable($object, $property)) {
            return $this->propertyAccessor->getValue($object, $property);
        } else {
            $accessor = $this->privatePropertyAccessor;
            return $accessor($property);
        }
    }

    private function getRepository (UniqueMessage $constraint, ?ObjectManager $manager = null): Selectable
    {
        if ($manager === null) {
            $manager = $this->getManager($constraint);
        }

        $repository = $manager->getRepository($constraint->entityClass);

        if (!$repository instanceof Selectable) {
            throw new \LogicException(sprintf(
                '%s does not implement %s which is required for UniqueDto validation',
                get_class($repository), Selectable::class
            ));
        }

        return $repository;
    }

    private function getManager (UniqueMessage $constraint): ObjectManager
    {
        if ($constraint->em !== null) {
            return $this->doctrine->getManager($constraint->em);
        }

        $em = $this->doctrine->getManagerForClass($constraint->entityClass);

        if ($em === null) {
            throw new ConstraintDefinitionException(sprintf(
                'Class "%s" is not managed by doctrine',
                $constraint->entityClass
            ));
        }

        return $em;
    }

    private function normalizeFields (array $initial): array
    {
        $normalized = [];

        foreach ($initial as $dtoProperty => $entityProperty) {
            if (is_numeric($dtoProperty)) {
                $dtoProperty = $entityProperty;
            }

            $normalized[$dtoProperty] = $entityProperty;
        }

        return $normalized;
    }

    private function getFieldValues (array $fields, $object): array
    {
        $values = [];

        foreach ($fields as $dtoProperty => $entityProperty) {
            $values[$dtoProperty] = $this->getPropertyValue($object, $dtoProperty);
        }

        return $values;
    }

    private function buildComparisons (array $fields, array $values, bool $negate = false): array
    {
        $comparisons = [];

        foreach ($fields as $dtoProperty => $entityProperty) {
            $operation = $negate ? Comparison::NEQ : Comparison::EQ;
            $comparisons[] = new Comparison($entityProperty, $operation, new Value($values[$dtoProperty]));
        }

        return $comparisons;
    }

    private function addComparisonsToCriteria (Criteria $criteria, array $comparisons): Criteria
    {
        if (count($comparisons) === 1) {
            $criteria->andWhere($comparisons[0]);
        } else {
            $criteria->andWhere(Criteria::expr()->andX(...$comparisons));
        }

        return $criteria;
    }
}
