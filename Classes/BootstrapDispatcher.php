<?php

namespace Cundd\Rest;

use Cundd\Rest\DataProvider\Utility;
use Cundd\Rest\Dispatcher\DispatcherInterface;
use Cundd\Rest\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\Container\Container;

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
     * Initialize
     *
     * @param ObjectManager $objectManager
     * @param array         $configuration
     */
    public function __construct(ObjectManager $objectManager = null, array $configuration = [])
    {
        (new Bootstrap())->init();

        if ($objectManager === null) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        }

        $this->objectManager = $objectManager;
        $this->initializeConfiguration($configuration);
        $this->configureObjectManager();
        $this->registerSingularToPlural($objectManager);
        $this->configureDispatcher($objectManager);
    }

    /**
     * Initializes the Object framework
     *
     * @param array $configuration
     */
    private function initializeConfiguration(array $configuration)
    {
        $this->configurationManager = $this->objectManager->get(ConfigurationManagerInterface::class);
        $this->configurationManager->setConfiguration($configuration);
    }

    /**
     * Configures the object manager object configuration from
     * config.tx_extbase.objects and plugin.tx_foo.objects
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
        /** @var LoggerInterface $logger */
        $logger = $objectManager->get(LoggerInterface::class);

        $this->dispatcher = new Dispatcher($objectManager, $requestFactory, $responseFactory, $logger);
    }

    /**
     * Process the raw request
     *
     * Entry point for the PSR 7 middleware
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response Prepared response @deprecated will be removed in 4.0.0
     * @return ResponseInterface
     */
    public function processRequest(ServerRequestInterface $request, ResponseInterface $response = null)
    {
        return $this->dispatcher->processRequest($request, $response);
    }
}
