<?php

declare(strict_types=1);

namespace Cundd\Rest\Bootstrap;

use Cundd\Rest\ObjectManagerInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Utility\EidUtility;

/**
 * Class to bootstrap TYPO3 frontend controller
 *
 * @deprecated use \Cundd\Rest\Bootstrap\Core\Factory instead. Will be removed in 6.0
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
     */
    public function initialize(ServerRequestInterface $request): TypoScriptFrontendController
    {
        $factory = new CoreBootstrapFactory($this->objectManager);

        return $factory->build()->initialize($request);
    }
}
