<?php

declare(strict_types=1);

namespace Cundd\Rest\Bootstrap;

use Cundd\Rest\DataProvider\Utility;
use Cundd\Rest\Dispatcher;
use Cundd\Rest\Dispatcher\DispatcherInterface;
use Cundd\Rest\ObjectManagerInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class MiddlewareBootstrap
{
    private ObjectManagerInterface $objectManager;

    private array $configuration;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array                  $configuration
     */
    public function __construct(ObjectManagerInterface $objectManager, array $configuration = [])
    {
        $this->objectManager = $objectManager;
        $this->configuration = $configuration;
    }

    /**
     * Bootstrap the TYPO3 environment
     *
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    public function bootstrapCore(ServerRequestInterface $request): ServerRequestInterface
    {
        $coreBootstrapFactory = new CoreBootstrapFactory($this->objectManager);
        $coreBootstrap = $coreBootstrapFactory->build();
        $request = $coreBootstrap->initialize($request);
//        $GLOBALS['TYPO3_REQUEST'] = $request->withAttribute('frontend.typoscript', $frontendController);

        $this->initializeConfiguration($this->configuration);

        $GLOBALS['TYPO3_REQUEST'] = $request;
        $this->registerSingularToPlural($this->objectManager);

        return $request;
    }

    /**
     * Initialize the system language
     *
     * @param TypoScriptFrontendController $frontendController
     * @param ServerRequestInterface       $request
     * @return ServerRequestInterface
     */
    public function bootstrapLanguage(
        TypoScriptFrontendController $frontendController,
        ServerRequestInterface $request
    ): ServerRequestInterface {
        $languageBootstrapFactory = new LanguageBootstrapFactory();
        $languageEnhancedRequest = $languageBootstrapFactory->build()->prepareRequest($frontendController, $request);

        // Store the enhanced/patched request so that e.g. the LocalizationUtility can read the requested
        // language
        $GLOBALS['TYPO3_REQUEST'] = $languageEnhancedRequest;

        return $languageEnhancedRequest;
    }

    /**
     * @return DispatcherInterface
     */
    public function buildDispatcher(): DispatcherInterface
    {
        $dispatcherFactory = new Dispatcher\DispatcherFactory($this->objectManager);

        return $dispatcherFactory->build();
    }

    /**
     * Initialize the Configuration Manager instance
     *
     * @param array $configuration
     */
    private function initializeConfiguration(array $configuration): void
    {
        $configurationManager = $this->objectManager->get(ConfigurationManagerInterface::class);
        $configurationManager->setConfiguration($configuration);
    }

    /**
     * Register singulars to the plural
     *
     * @param ObjectManagerInterface $objectManager
     */
    private function registerSingularToPlural(ObjectManagerInterface $objectManager): void
    {
        $singularToPlural = $objectManager->getConfigurationProvider()->getSetting('singularToPlural');
        if ($singularToPlural) {
            foreach ($singularToPlural as $singular => $plural) {
                Utility::registerSingularForPlural($singular, $plural);
            }
        }
    }
}
