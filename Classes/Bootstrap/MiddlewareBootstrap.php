<?php
declare(strict_types=1);

namespace Cundd\Rest\Bootstrap;

use Cundd\Rest\DataProvider\Utility;
use Cundd\Rest\Dispatcher;
use Cundd\Rest\Dispatcher\DispatcherInterface;
use Cundd\Rest\ObjectManager;
use Cundd\Rest\ObjectManagerInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class MiddlewareBootstrap
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
     * @var array
     */
    private $configuration;

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
     * Bootstrap the TYPO3 environment
     *
     * @param ServerRequestInterface $request
     * @return TypoScriptFrontendController
     */
    public function bootstrapCore(ServerRequestInterface $request): TypoScriptFrontendController
    {
        $this->initializeObjectManager();

        $coreBootstrapFactory = new CoreBootstrapFactory($this->objectManager);
        $coreBootstrap = $coreBootstrapFactory->build();
        $frontendController = $coreBootstrap->initialize($request);

        $this->initializeConfiguration($this->configuration);
        $this->registerSingularToPlural($this->objectManager);

        return $frontendController;
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
        $languageBootstrapFactory = new LanguageBootstrapFactory($this->objectManager);
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
