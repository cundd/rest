<?php
declare(strict_types=1);

namespace Cundd\CustomRest\Controller;

use Cundd\CustomRest\Domain\Model\Person;
use Cundd\CustomRest\Domain\Repository\PersonRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Controller\MvcPropertyMappingConfiguration;
use TYPO3\CMS\Extbase\Mvc\View\JsonView;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationBuilder;
use TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter;
use function get_parent_class;
use function method_exists;

/**
 * An example Extbase controller that will be called through REST
 */
class PersonController extends ActionController
{
    /**
     * @var JsonView
     */
    protected $view;

    /**
     * @var string
     */
    protected $defaultViewObjectName = JsonView::class;

    /**
     * Person Repository
     *
     * @var PersonRepository
     */
    protected $personRepository;

    /**
     * Person Controller constructor
     *
     * @param PersonRepository $personRepository
     */
    public function __construct(PersonRepository $personRepository)
    {
        if (method_exists(get_parent_class($this), '__construct')) {
            /** @noinspection PhpUndefinedMethodInspection */
            parent::__construct();
        }
        $this->personRepository = $personRepository;
    }

    /* ----------------- GET -------------*/

    /**
     * action list
     *
     * @return void
     */
    public function listAction()
    {
        $this->view->assign('value', $this->personRepository->findAll());
    }

    /**
     * action show
     *
     * @param integer $uid
     * @return void
     */
    public function showAction($uid)
    {
        $this->view->assign('value', $this->personRepository->findByUid($uid));
    }

    /**
     * action firstName
     *
     * @param string $firstName
     * @return void
     */
    public function firstNameAction($firstName)
    {
        $this->view->assign('value', $this->personRepository->findByFirstName($firstName));
    }

    /**
     * action lastName
     *
     * @param string $lastName
     * @return void
     */
    public function lastNameAction($lastName)
    {
        $this->view->assign('value', $this->personRepository->findByLastName($lastName));
    }

    /**
     * action birthday
     *
     * @param string $date
     * @return void
     */
    public function birthdayAction($date)
    {
        $this->view->assign('value', $this->personRepository->findByBirthday($date));
    }


    /* ----------------- POST -------------*/

    /**
     * initialize action create
     */
    public function initializeCreateAction()
    {
        // If the request does not come from a fluid form the properties which are allowed to map must be set manually
        if (!$this->request->getInternalArgument('__trustedProperties')) {
            $this->addPropertyMappingConfiguration();
        }
    }

    /**
     * action create
     *
     * @param Person $person
     */
    public function createAction(Person $person)
    {
        $this->personRepository->add($person);

        $this->view->assign('value', ['success' => 1]);
    }

    /* ----------------- PATCH -------------*/

    /**
     * initialize action update
     */
    public function initializeUpdateAction()
    {
        // If the request does not come from a fluid form the properties which are allowed to map must be set manually
        if (!$this->request->getInternalArgument('__trustedProperties')) {
            $this->addPropertyMappingConfiguration();
        }
    }

    /**
     * action update
     *
     * @param Person $person
     */
    public function updateAction(Person $person)
    {
        $this->personRepository->update($person);

        $this->view->assign('value', ['success' => 1]);
    }

    /*-----------------------------------------------------------------*/

    /**
     * error action
     */
    protected function errorAction()
    {
        $flattenedValidationErrors = $this->arguments->validate()->getFlattenedErrors();

        $response = [
            'success' => 0,
            'errors'  => $flattenedValidationErrors['person'],
        ];

        $this->view->assign('value', $response);
    }

    /**
     * addPropertyMappingConfiguration
     */
    protected function addPropertyMappingConfiguration()
    {
        if ($this->request->hasArgument('person')) {
            /** @var MvcPropertyMappingConfiguration $propertyMappingConfiguration */
            $propertyMappingConfiguration = (new PropertyMappingConfigurationBuilder())->build(
                MvcPropertyMappingConfiguration::class
            );
            $propertyMappingConfiguration->setTypeConverterOption(
                PersistentObjectConverter::class,
                PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED,
                true
            );

            /** @noinspection PhpUnhandledExceptionInspection */
            foreach ((array)$this->request->getArgument('person') as $propertyName => $value) {
                $propertyMappingConfiguration->allowProperties($propertyName);
            }

            /** @noinspection PhpUnhandledExceptionInspection */
            $this->arguments->getArgument('person')->injectPropertyMappingConfiguration($propertyMappingConfiguration);
        }
    }
}
