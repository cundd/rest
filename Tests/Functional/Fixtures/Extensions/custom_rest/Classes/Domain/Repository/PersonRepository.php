<?php
declare(strict_types=1);

namespace Cundd\CustomRest\Domain\Repository;

use Cundd\CustomRest\Domain\Model\Person;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * The repository for Persons
 *
 * @method Person[] findByLastName($lastName)
 * @method Person[] findByBirthday($date)
 * @method Person[] findByFirstName($firstName)
 */
class PersonRepository extends Repository
{
}
