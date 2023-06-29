<?php

declare(strict_types=1);

namespace Cundd\Rest\Bootstrap\V8;

use Cundd\Rest\Bootstrap\AbstractCoreBootstrap;
use Cundd\Rest\ObjectManagerInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;
use TYPO3\CMS\Core\Error\Http\ServiceUnavailableException;
use TYPO3\CMS\Core\TimeTracker\TimeTracker;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Utility\EidUtility;

use function class_exists;
use function is_array;
use function is_callable;
use function is_object;

/**
 * Class V8CoreBootstrap
 *
 * @deprecated will be removed in 6.0
 */
class V8CoreBootstrap extends AbstractCoreBootstrap
{
    /**
     * @var V8LanguageBootstrap
     */
    protected $languageBootstrap;

    /**
     * Core constructor.
     *
     * @param V8LanguageBootstrap    $languageBootstrap
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(V8LanguageBootstrap $languageBootstrap, ObjectManagerInterface $objectManager)
    {
        parent::__construct($objectManager);
        $this->languageBootstrap = $languageBootstrap;
    }

    /**
     * Initializes the TYPO3 environment
     *
     * @param ServerRequestInterface $request
     * @return TypoScriptFrontendController
     */
    public function initialize(ServerRequestInterface $request): TypoScriptFrontendController
    {
        $this->initializeTimeTracker();
        $this->languageBootstrap->initializeLanguageObject($request);

        if ($this->getFrontendControllerIsInitialized()) {
            return $GLOBALS['TSFE'];
        }
        $frontendController = $this->buildFrontendController($this->getPageUid($request), $request);

        // Register the frontend controller as the global TSFE
        $GLOBALS['TSFE'] = $frontendController;
        $this->configureFrontendController($frontendController, $request);
        $this->languageBootstrap->initializeFrontendController($frontendController, $request);

        return $frontendController;
    }

    /**
     * Build the TSFE object
     *
     * @param int                    $pageUid
     * @param ServerRequestInterface $request
     * @return TypoScriptFrontendController
     */
    protected function buildFrontendController(
        int $pageUid,
        ServerRequestInterface $request
    ): TypoScriptFrontendController {
        $cHash = GeneralUtility::_GP('cHash') ?: 'cunddRestFakeHash';

        return $this->objectManager->get(
            TypoScriptFrontendController::class,
            null,       // previously TYPO3_CONF_VARS
            $pageUid,
            0,          // Type
            0,          // no_cache
            $cHash,     // cHash
            null,       // previously jumpurl
            '',         // MP
            ''          // RDCT
        );
    }

    /**
     * Configure the given frontend controller
     *
     * @param TypoScriptFrontendController $frontendController
     * @param ServerRequestInterface       $request
     * @throws ServiceUnavailableException
     */
    protected function configureFrontendController(
        TypoScriptFrontendController $frontendController,
        ServerRequestInterface $request
    ) {
        if (is_callable([$frontendController, 'initTemplate'])) {
            $frontendController->initTemplate();
        }

        if (!is_array($frontendController->page)) {
            $frontendController->page = [];
        }

        // Build an instance of ContentObjectRenderer
        $frontendController->newCObj();

        // Add the FE user
        if (class_exists(EidUtility::class)) {
            $frontendController->fe_user = EidUtility::initFeUser();
        }

        $frontendController->determineId($request);
        $frontendController->getConfigArray();
    }

    /**
     * @return bool
     */
    private function getFrontendControllerIsInitialized(): bool
    {
        return isset($GLOBALS['TSFE'])
            && is_object($GLOBALS['TSFE'])
            && !($GLOBALS['TSFE'] instanceof stdClass);
    }

    private function initializeTimeTracker()
    {
        if (!isset($GLOBALS['TT']) || !is_object($GLOBALS['TT'])) {
            $GLOBALS['TT'] = new TimeTracker();
            $GLOBALS['TT']->start();
        }
    }
}
