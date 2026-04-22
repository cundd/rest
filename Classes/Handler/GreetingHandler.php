<?php

declare(strict_types=1);

namespace Cundd\Rest\Handler;

use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\ResponseFactoryInterface;
use Cundd\Rest\Router\Route;
use Cundd\Rest\Router\RouterInterface;
use Cundd\Rest\Utility\SiteLanguageUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

use function class_exists;
use function date;

/**
 * Handler to show a nice greeting message
 */
class GreetingHandler implements HandlerInterface, HandlerDescriptionInterface
{
    public function __construct(
        protected readonly ResponseFactoryInterface $responseFactory,
    ) {
    }

    public function getDescription(): string
    {
        return 'Handler to display a nice greeting';
    }

    public function show(RestRequestInterface $request): ResponseInterface
    {
        if (class_exists(LocalizationUtility::class)) {
            return $this->showTYPO3Greeting($request);
        }

        return $this->showBuiltinGreeting($request);
    }

    public function options(): bool
    {
        // TODO: Respond with the correct preflight headers
        return true;
    }

    public function configureRoutes(
        RouterInterface $router,
        RestRequestInterface $request,
    ): void {
        $router->add(Route::get('/?', $this->show(...)));
        $router->add(Route::options('/?', $this->options(...)));
    }

    protected function showTYPO3Greeting(
        RestRequestInterface $request,
    ): ResponseInterface {
        $siteLanguage = SiteLanguageUtility::detectSiteLanguage($request);
        $greeting = LocalizationUtility::translate(
            'message.greeting',
            'rest',
            [],
            // TODO: Check why passing the full `Locale`-object does not work
            $siteLanguage?->getLocale()->getLanguageCode()
        );

        return $this->responseFactory->createSuccessResponse(
            $greeting,
            200,
            $request
        );
    }

    protected function showBuiltinGreeting(
        RestRequestInterface $request,
    ): ResponseInterface {
        $greeting = 'What\'s up?';
        $hour = date('H');
        if ($hour <= '10') {
            $greeting = 'Good Morning!';
        } elseif ($hour >= '23') {
            $greeting = 'Hy! Still awake?';
        }

        return $this->responseFactory->createSuccessResponse(
            $greeting,
            200,
            $request
        );
    }
}
