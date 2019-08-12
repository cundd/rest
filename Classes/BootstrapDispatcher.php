<?php
declare(strict_types=1);

namespace Cundd\Rest;

use Cundd\Rest\Bootstrap\Core;
use Cundd\Rest\DataProvider\Utility;
use Cundd\Rest\Dispatcher\DispatcherInterface;
use Cundd\Rest\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Error\Http\ServiceUnavailableException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\Container\Container;

/**
 * Main entry point into the REST application
 */
class BootstrapDispatcher
{
    /**
     * @var ConfigurationManagerInterface
     */
    private $configurationManager;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var DispatcherInterface
     */
    private $dispatcher;

    /**
     * @var array
     */
    private $configuration;

    /**
     * @var bool
     */
    private $isInitialized = false;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array                  $configuration
     */
    public function __construct(ObjectManagerInterface $objectManager = null, array $configuration = [])
    {
        $this->objectManager = $objectManager;
        $this->configuration = $configuration;
    }

    /**
     * Process the raw request
     *
     * Entry point for the PSR 7 middleware
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function processRequest(ServerRequestInterface $request)
    {
        $this->bootstrap($request);

        return $this->dispatcher->processRequest($request);
    }

    /**
     * Bootstrap the TYPO3 environment
     *
     * @param ServerRequestInterface $request
     * @throws ServiceUnavailableException
     */
    private function bootstrap(ServerRequestInterface $request)
    {
        if (!$this->isInitialized) {
            $this->initializeObjectManager();
            $coreBootstrap = $this->objectManager->get(Core::class);
            $coreBootstrap->initialize($request);

            $this->initializeConfiguration($this->configuration);
            $this->configureObjectManager();
            $this->registerSingularToPlural($this->objectManager);
            $this->configureDispatcher($this->objectManager);

            $this->isInitialized = true;
        }
    }

    /**
     * Initialize the Configuration Manager instance
     *
     * @param array $configuration
     */
    private function initializeConfiguration(array $configuration)
    {
        $this->configurationManager = $this->objectManager->get(ConfigurationManagerInterface::class);
        $this->configurationManager->setConfiguration($configuration);
    }

    /**
     * Initialize the Object Manager instance
     */
    private function initializeObjectManager()
    {
        if (!$this->objectManager) {
            $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);;
        }
    }

    /**
     * Configures the object manager object configuration from
     * config.tx_extbase.objects and plugin.tx_foo.objects
     *
     * @deprecated will be removed in 5.0
     */
    private function configureObjectManager()
    {
        $frameworkSetup = $this->configurationManager
            ->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        if (!is_array($frameworkSetup['objects'])) {
            return;
        }
        $objectContainer = GeneralUtility::makeInstance(Container::class);
        foreach ($frameworkSetup['objects'] as $classNameWithDot => $classConfiguration) {
            if (isset($classConfiguration['className'])) {
                $originalClassName = rtrim($classNameWithDot, '.');
                $objectContainer->registerImplementation($originalClassName, $classConfiguration['className']);
            }
        }
    }

    /**
     * Register singulars to the plural
     *
     * @param ObjectManagerInterface $objectManager
     */
    private function registerSingularToPlural(ObjectManagerInterface $objectManager)
    {
        $singularToPlural = $objectManager->getConfigurationProvider()->getSetting('singularToPlural');
        if ($singularToPlural) {
            foreach ($singularToPlural as $singular => $plural) {
                Utility::registerSingularForPlural($singular, $plural);
            }
        }
    }

    /**
     * @param ObjectManagerInterface $objectManager
     */
    private function configureDispatcher(ObjectManagerInterface $objectManager)
    {
        $requestFactory = $objectManager->getRequestFactory();
        $responseFactory = $objectManager->getResponseFactory();
        $logger = $objectManager->get(LoggerInterface::class);

        $this->dispatcher = new Dispatcher($objectManager, $requestFactory, $responseFactory, $logger);
    }
}
