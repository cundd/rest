<?php

declare(strict_types=1);

namespace Cundd\Rest\Http;

use Cundd\Rest\Bootstrap\MiddlewareBootstrap;
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
        if ($this->isRestRequest($request)) {
            $GLOBALS['TYPO3_REQUEST'] = $request;
            $middlewareBootstrap = new MiddlewareBootstrap(GeneralUtility::makeInstance(ObjectManagerInterface::class));
            $request = $middlewareBootstrap->bootstrapCore($request);
            $languageEnhancedRequest = $middlewareBootstrap->bootstrapLanguage(
                $request->getAttribute('frontend.controller'),
                $request
            );

            return $middlewareBootstrap->buildDispatcher()->processRequest($languageEnhancedRequest);
        } else {
            return $handler->handle($request);
        }
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
