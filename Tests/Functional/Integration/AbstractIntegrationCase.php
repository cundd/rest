<?php


namespace Cundd\Rest\Tests\Functional\Integration;


use Cundd\Rest\Dispatcher;
use Cundd\Rest\Dispatcher\DispatcherInterface;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Log\Logger;
use Cundd\Rest\ObjectManagerInterface;
use Cundd\Rest\RequestFactoryInterface;
use Cundd\Rest\ResponseFactoryInterface;
use Cundd\Rest\Tests\Functional\AbstractCase;
use Cundd\Rest\Tests\RequestBuilderTrait;
use Psr\Log\NullLogger;
use TYPO3\CMS\Core\Http\Response;

class AbstractIntegrationCase extends AbstractCase
{
    use RequestBuilderTrait;

    /**
     * @var DispatcherInterface
     */
    private $dispatcher;

    public function setUp()
    {
        parent::setUp();

        $this->dispatcher = new Dispatcher(
            $this->objectManager->get(ObjectManagerInterface::class),
            $this->objectManager->get(RequestFactoryInterface::class),
            $this->objectManager->get(ResponseFactoryInterface::class),
            new Logger(new NullLogger())
        );
    }


    public function dispatch(RestRequestInterface $request)
    {
        return $this->dispatcher->dispatch($request, new Response());
    }
}
