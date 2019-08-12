<?php
declare(strict_types=1);

namespace Cundd\Rest\Bootstrap;

use Cundd\Rest\ObjectManagerInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;
use TYPO3\CMS\Core\Error\Http\ServiceUnavailableException;
use TYPO3\CMS\Core\TimeTracker\TimeTracker;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Utility\EidUtility;
use function class_exists;

/**
 * Class to bootstrap TYPO3 frontend controller
 */
class Core
{
    /**
     * @var LanguageBootstrap
     */
    private $languageBootstrap;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Core constructor.
     *
     * @param LanguageBootstrap      $languageBootstrap
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(LanguageBootstrap $languageBootstrap, ObjectManagerInterface $objectManager)
    {
        $this->languageBootstrap = $languageBootstrap;
        $this->objectManager = $objectManager;
    }

    /**
     * Initializes the TYPO3 environment
     *
     * @param ServerRequestInterface $request
     * @return TypoScriptFrontendController
     * @throws ServiceUnavailableException
     */
    public function initialize(ServerRequestInterface $request): TypoScriptFrontendController
    {
        $this->initializeTimeTracker();
        $this->languageBootstrap->initializeLanguageObject($request);

        if ($this->getFrontendControllerIsInitialized()) {
            return $GLOBALS['TSFE'];
        }
        $frontendController = $this->buildFrontendController($this->getPageUid($request));

        // Register the frontend controller as the global TSFE
        $GLOBALS['TSFE'] = $frontendController;
        $this->configureFrontendController($frontendController);
        $this->languageBootstrap->initializeFrontendController($frontendController, $request);

        return $frontendController;
    }

    private function initializeTimeTracker()
    {
        if (!isset($GLOBALS['TT']) || !is_object($GLOBALS['TT'])) {
            $GLOBALS['TT'] = new TimeTracker();
            $GLOBALS['TT']->start();
        }
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

    /**
     * Build the TSFE object
     *
     * @param int $pageUid
     * @return TypoScriptFrontendController
     */
    private function buildFrontendController(int $pageUid): TypoScriptFrontendController
    {
        $cHash = GeneralUtility::_GP('cHash') ?: 'cunddRestFakeHash';

        /** @var TypoScriptFrontendController $frontendController */
        $frontendController = $this->objectManager->get(
            TypoScriptFrontendController::class,
            null, // previously TYPO3_CONF_VARS
            $pageUid,
            0,  // Type
            0,  // no_cache
            $cHash, // cHash
            null, // previously jumpurl
            '', // MP,
            ''  // RDCT
        );

        return $frontendController;
    }

    /**
     * @param ServerRequestInterface $request
     * @return int
     */
    private function getPageUid(ServerRequestInterface $request): int
    {
        return (int)($request->getQueryParams()['pid'] ?? 0);
    }

    /**
     * Configure the given frontend controller
     *
     * @param TypoScriptFrontendController $frontendController
     * @throws ServiceUnavailableException
     */
    private function configureFrontendController(TypoScriptFrontendController $frontendController)
    {
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

        $frontendController->determineId();
        $frontendController->getConfigArray();
    }
}
