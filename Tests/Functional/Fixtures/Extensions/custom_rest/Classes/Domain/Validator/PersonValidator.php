<?php

declare(strict_types=1);

namespace Cundd\CustomRest\Domain\Validator;

use Cundd\CustomRest\Domain\Model\Person;
use Cundd\CustomRest\Domain\Repository\PersonRepository;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class PersonValidator extends AbstractValidator
{
    protected PersonRepository $personRepository;

    public function __construct(PersonRepository $personRepository)
    {
        $this->personRepository = $personRepository;
    }

    public function isValid(mixed $value): void
    {
        if ($value instanceof Person) {
            if (!$this->validateCustom($value)) {
                $this->addError('validation failed!', 1472506812);
            }
        }
    }

    /**
     * Custom validation
     *
     * @param Person $person
     * @return bool
     */
    protected function validateCustom(Person $person): bool
    {
        return ($person->getFirstName() == $person->getLastName());
    }
}
