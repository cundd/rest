<?php

declare(strict_types=1);

namespace Cundd\Rest;

use Cundd\Rest\DataProvider\Utility;
use Cundd\Rest\Dispatcher\DispatcherFactory;
use Cundd\Rest\Dispatcher\DispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * Main entry point into the REST application
 *
 * @deprecated will be removed in 6.0
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
     * @param ObjectManagerInterface|null $objectManager
     * @param array                       $configuration
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
     */
    private function bootstrap(ServerRequestInterface $request)
    {
        if (!$this->isInitialized) {
            $this->initializeObjectManager();

            $coreBootstrapFactory = $this->objectManager->get(Bootstrap\CoreBootstrapFactory::class);

            $coreBootstrap = $coreBootstrapFactory->build();
            $coreBootstrap->initialize($request);

            $this->initializeConfiguration($this->configuration);
            $this->registerSingularToPlural($this->objectManager);
            $dispatcherFactory = new DispatcherFactory($this->objectManager);
            $this->dispatcher = $dispatcherFactory->build();

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
            $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
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
}
