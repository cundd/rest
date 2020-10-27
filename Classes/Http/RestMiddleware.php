<?php
declare(strict_types=1);

namespace Cundd\Rest\Http;

use Cundd\Rest\Bootstrap\V10\MiddlewareBootstrap;
use Cundd\Rest\Bootstrap\V10\V10LanguageBootstrap;
use Cundd\Rest\Utility\SiteLanguageUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function getenv;
use function strlen;
use function substr;
use function trim;

class RestMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->isRestRequest($request)) {
            return $handler->handle($request);
        }

        $middlewareBootstrap = new MiddlewareBootstrap();
        $frontendController = $middlewareBootstrap->bootstrap($request);

        $languageBootstrap = new V10LanguageBootstrap();
        $languageEnhancedRequest = $languageBootstrap->prepareRequest($frontendController, $request);

        // Store the enhanced/patched request so that e.g. the LocalizationUtility can read the requested
        // language
        $GLOBALS['TYPO3_REQUEST'] = $languageEnhancedRequest;

        return $middlewareBootstrap->buildDispatcher()->processRequest($languageEnhancedRequest ?? $request);
    }

    /**
     * @param ServerRequestInterface $request
     * @return bool
     */
    private function isRestRequest(ServerRequestInterface $request): bool
    {
        $requestUri = (string)$request->getUri()->getPath();

        $restRequestBasePath = (string)(getenv(
            'TYPO3_REST_REQUEST_BASE_PATH'
        ) ?: getenv(
            'REDIRECT_TYPO3_REST_REQUEST_BASE_PATH'
        ));

        if ($restRequestBasePath) {
            $restRequestBasePath = '/' . trim($restRequestBasePath, '/');
        }

        $siteLanguagePrefix = SiteLanguageUtility::detectSiteLanguagePrefix($request);

        $restRequestPrefix = $restRequestBasePath . $siteLanguagePrefix . 'rest/';
        $restRequestPrefixLength = strlen($restRequestPrefix);

        return substr($requestUri, 0, $restRequestPrefixLength) === $restRequestPrefix;
    }
}
