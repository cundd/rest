<?php
declare(strict_types=1);

namespace Cundd\Rest;

use Cundd\Rest\Bootstrap\Core;
use Cundd\Rest\Bootstrap\LanguageBootstrap;
use TYPO3\CMS\Core\Error\Http\ServiceUnavailableException;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\ServerRequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @deprecated will be removed in 5.0. Use \Cundd\Rest\Bootstrap\Core instead
 */
class Bootstrap
{
    /**
     * @var Core
     */
    private $coreBootstrap;

    /**
     * @var ServerRequest
     */
    private $request;

    /**
     * @var LanguageBootstrap
     */
    private $languageBootstrap;

    /**
     * Bootstrap constructor
     */
    public function __construct()
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->coreBootstrap = $objectManager->get(Core::class);
        $this->languageBootstrap = $objectManager->get(LanguageBootstrap::class);
        $this->request = ServerRequestFactory::fromGlobals();
    }

    /**
     * Initializes the TYPO3 environment
     *
     * @param TypoScriptFrontendController|null $frontendController
     * @return TypoScriptFrontendController
     * @throws ServiceUnavailableException
     * @deprecated will be removed in 5.0. Use \Cundd\Rest\Bootstrap\Core::initialize() instead
     */
    public function init(
        /** @noinspection PhpUnusedParameterInspection */ TypoScriptFrontendController $frontendController = null
    ) {
        return $this->coreBootstrap->initialize($this->request);
    }

    /**
     * Initialize language object
     *
     * @deprecated will be removed in 5.0. Use \Cundd\Rest\Bootstrap\LanguageBootstrap::initializeLanguageObject() instead
     */
    public function initializeLanguageObject()
    {
        $this->languageBootstrap->initializeLanguageObject($this->request);
    }
}
