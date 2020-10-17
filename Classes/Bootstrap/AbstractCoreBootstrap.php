<?php
declare(strict_types=1);

namespace Cundd\Rest\Bootstrap;

use Cundd\Rest\ObjectManagerInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class to bootstrap TYPO3 frontend controller
 */
abstract class AbstractCoreBootstrap implements CoreBootstrapInterface
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Core constructor.
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Build the TSFE object
     *
     * @param int                    $pageUid
     * @param ServerRequestInterface $request
     * @return TypoScriptFrontendController
     */
    abstract protected function buildFrontendController(
        int $pageUid,
        ServerRequestInterface $request
    ): TypoScriptFrontendController;

    /**
     * Configure the given frontend controller
     *
     * @param TypoScriptFrontendController $frontendController
     * @param ServerRequestInterface       $request
     */
    abstract protected function configureFrontendController(
        TypoScriptFrontendController $frontendController,
        ServerRequestInterface $request
    );

    /**
     * @param ServerRequestInterface $request
     * @return int
     */
    protected function getPageUid(ServerRequestInterface $request): int
    {
        return (int)($request->getQueryParams()['pid'] ?? 0);
    }
}
