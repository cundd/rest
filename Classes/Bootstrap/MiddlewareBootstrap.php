<?php

declare(strict_types=1);

namespace Cundd\Rest\Bootstrap;

use Cundd\Rest\Dispatcher\DispatcherFactory;
use Cundd\Rest\Dispatcher\DispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class MiddlewareBootstrap
{
    public function __construct(
        private LanguageBootstrapFactory $languageBootstrapFactory,
        private DispatcherFactory $dispatcherFactory,
    ) {
    }

    /**
     * Initialize the system language
     */
    public function bootstrapLanguage(
        ServerRequestInterface $request,
    ): ServerRequestInterface {
        $languageEnhancedRequest = $this->languageBootstrapFactory
            ->build()
            ->prepareRequest($request);

        // Store the enhanced/patched request so that e.g. the LocalizationUtility can read the requested
        // language //dontcommit
        // $GLOBALS['TYPO3_REQUEST'] = $languageEnhancedRequest;

        return $languageEnhancedRequest;
    }

    public function buildDispatcher(): DispatcherInterface
    {
        return $this->dispatcherFactory->build();
    }
}
