<?php
declare(strict_types=1);

namespace Cundd\Rest\Handler;

use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\ResponseFactoryInterface;
use Cundd\Rest\Router\Route;
use Cundd\Rest\Router\RouterInterface;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use function class_exists;
use function date;

/**
 * Handler to show a nice greeting message
 */
class GreetingHandler implements HandlerInterface, HandlerDescriptionInterface
{
    /**
     * @var ResponseFactoryInterface
     */
    protected $responseFactory;

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function getDescription(): string
    {
        return 'Handler to display a nice greeting';
    }

    public function show(RestRequestInterface $request): ResponseInterface
    {
//        var_dump($request->getAttribute('language'));
        if (class_exists(LocalizationUtility::class)) {
            return $this->showTYPO3Greeting($request);
        } else {
            return $this->showBuiltinGreeting($request);
        }
    }

    /**
     * @return bool
     */
    public function options(): bool
    {
        // TODO: Respond with the correct preflight headers
        return true;
    }

    public function configureRoutes(RouterInterface $router, RestRequestInterface $request)
    {
        $router->add(Route::get('/?', [$this, 'show']));
        $router->add(Route::options('/?', [$this, 'options']));
    }

    /**
     * @param RestRequestInterface $request
     * @return ResponseInterface
     */
    protected function showTYPO3Greeting(RestRequestInterface $request): ResponseInterface
    {
        $greeting = LocalizationUtility::translate('message.greeting', 'rest');

        return $this->responseFactory->createSuccessResponse($greeting, 200, $request);
    }

    /**
     * @param RestRequestInterface $request
     * @return ResponseInterface
     */
    protected function showBuiltinGreeting(RestRequestInterface $request): ResponseInterface
    {
        $greeting = 'What\'s up?';
        $hour = date('H');
        if ($hour <= '10') {
            $greeting = 'Good Morning!';
        } elseif ($hour >= '23') {
            $greeting = 'Hy! Still awake?';
        }

        return $this->responseFactory->createSuccessResponse($greeting, 200, $request);
    }
}
