<?php

namespace Cundd\CustomRest\Domain\Validator;

use Cundd\CustomRest\Domain\Model\Person;
use Cundd\CustomRest\Domain\Repository\PersonRepository;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Class PersonValidator
 */
class PersonValidator extends AbstractValidator
{
    /**
     * personRepository
     *
     * @var PersonRepository
     */
    protected $personRepository;

    /**
     * Person Validator constructor
     *
     * @param PersonRepository $personRepository
     */
    public function __construct(PersonRepository $personRepository)
    {
        $this->personRepository = $personRepository;
    }

    /**
     * Validation of given Params
     *
     * @param $person
     * @return void
     */
    public function isValid($person)
    {
        if ($person instanceof Person) {
            if (!$this->validateCustom($person)) {
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
    protected function validateCustom($person)
    {
        return ($person->getFirstName() == $person->getLastName());
    }
}
