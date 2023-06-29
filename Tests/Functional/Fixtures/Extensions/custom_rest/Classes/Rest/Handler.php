<?php

/** @noinspection PhpUnusedParameterInspection */
declare(strict_types=1);

namespace Cundd\CustomRest\Rest;

use Cundd\Rest\Handler\HandlerInterface;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\ObjectManagerInterface;
use Cundd\Rest\ResponseFactoryInterface;
use Cundd\Rest\Router\Route;
use Cundd\Rest\Router\RouterInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Example handler
 */
class Handler implements HandlerInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var ResponseFactoryInterface
     */
    protected $responseFactory;

    /**
     * Handler constructor
     *
     * @param ObjectManagerInterface   $objectManager
     * @param ResponseFactoryInterface $responseFactory
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->objectManager = $objectManager;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @inheritDoc
     */
    public function configureRoutes(RouterInterface $router, RestRequestInterface $request)
    {
        /*------------------------------------------------------
         * Simple callback functions
         *-----------------------------------------------------*/

        /*
         * These custom handler example routes return hardcoded values. They do not call any
         * extbase controller functions. (For that, see PersonController and Routes below.)
         */

        # curl -X GET http://localhost:8888/rest/customhandler
        $router->add(
            Route::get(
                $request->getResourceType(),
                function (RestRequestInterface $request) {
                    return [
                        'path'         => $request->getPath(),
                        'uri'          => (string)$request->getUri(),
                        'resourceType' => (string)$request->getResourceType(),
                    ];
                }
            )
        );

        # curl -X GET http://localhost:8888/rest/customhandler/subpath
        $router->add(
            Route::get(
                $request->getResourceType() . '/subpath',
                function (RestRequestInterface $request) {
                    return [
                        'path'         => $request->getPath(),
                        'uri'          => (string)$request->getUri(),
                        'resourceType' => (string)$request->getResourceType(),
                    ];
                }
            )
        );

        # curl -X POST -d '{"username":"johndoe","password":"123456"}' http://localhost:8888/rest/customhandler/subpath
        $router->add(
            Route::post(
                $request->getResourceType() . '/subpath',
                function (RestRequestInterface $request) {
                    return [
                        'path'         => $request->getPath(),
                        'uri'          => (string)$request->getUri(),
                        'resourceType' => (string)$request->getResourceType(),
                        'data'         => $request->getSentData(),
                    ];
                }
            )
        );

        # curl -X GET http://localhost:8888/rest/customhandler/parameter/slug
        $router->add(
            Route::get(
                $request->getResourceType() . '/parameter/{slug}',
                function (RestRequestInterface $request, $slug) {
                    return [
                        'slug'         => $slug,
                        'path'         => $request->getPath(),
                        'uri'          => (string)$request->getUri(),
                        'resourceType' => (string)$request->getResourceType(),
                    ];
                }
            )
        );

        # curl -X GET http://localhost:8888/rest/customhandler/translate/tx_customrest_domain_model_person.first_name.json
        $router->add(
            Route::get(
                $request->getResourceType() . '/translate/{slug}',
                function (RestRequestInterface $request, $slug) {
                    /** @var SiteLanguage $siteLanguage */
                    $siteLanguage = isset($GLOBALS['TYPO3_REQUEST'])
                        ? $GLOBALS['TYPO3_REQUEST']->getAttribute('language')
                        : null;

                    return [
                        'locale'     => $siteLanguage ? $siteLanguage->getLocale() : '',
                        'original'   => $slug,
                        'translated' => LocalizationUtility::translate($slug, 'custom_rest'),
                    ];
                }
            )
        );

        # curl -X GET http://localhost:8888/rest/customhandler/12
        $router->add(
            Route::get(
                $request->getResourceType() . '/{int}',
                function (RestRequestInterface $request, $parameter) {
                    return [
                        'value'         => $parameter,
                        'parameterType' => gettype($parameter),
                        'path'          => $request->getPath(),
                        'uri'           => (string)$request->getUri(),
                        'resourceType'  => (string)$request->getResourceType(),
                    ];
                }
            )
        );

        # curl -X GET http://localhost:8888/rest/customhandler/decimal/10.8
        $router->add(
            Route::get(
                $request->getResourceType() . '/decimal/{float}',
                function (RestRequestInterface $request, $parameter) {
                    return [
                        'value'         => $parameter,
                        'parameterType' => gettype($parameter),
                        'path'          => $request->getPath(),
                        'uri'           => (string)$request->getUri(),
                        'resourceType'  => (string)$request->getResourceType(),
                    ];
                }
            )
        );

        # curl -X GET http://localhost:8888/rest/customhandler/bool/yes
        # curl -X GET http://localhost:8888/rest/customhandler/bool/no
        $router->add(
            Route::get(
                $request->getResourceType() . '/bool/{bool}',
                function (RestRequestInterface $request, $parameter) {
                    return [
                        'value'         => $parameter,
                        'parameterType' => gettype($parameter),
                        'path'          => $request->getPath(),
                        'uri'           => (string)$request->getUri(),
                        'resourceType'  => (string)$request->getResourceType(),
                    ];
                }
            )
        );

        /*------------------------------------------------------
         * Sample Route for a "require" path
         *-----------------------------------------------------*/

        /*
         * To access this route a valid login is required.
         * This requirement is defined in ext_typoscript_setup.txt line 9
         */
        # curl -X GET http://localhost:8888/rest/cundd-custom_rest-require
        $router->add(
            Route::get(
                'cundd-custom_rest-require',
                function () {
                    return 'Access Granted';
                }
            )
        );

        /*------------------------------------------------------
         * Detailed error routes for empty person path endpoints
         *-----------------------------------------------------*/

        /*
         * Don't do that. Better use an error Object that accepts detailed info. A base error
         * class ist implemented in rest anyway... overwrite/extend that?
         */

        # curl -X GET http://localhost:8888/rest/cundd-custom_rest-person/show
        $router->add(
            Route::get(
                '/cundd-custom_rest-person/show/?',
                function (RestRequestInterface $request) {
                    return [
                        'error' => 'Please add a unique id of the data you are looking for: /person/show/{uid}.',
                    ];
                }
            )
        );

        # curl -X GET http://localhost:8888/rest/cundd-custom_rest-person/lastname
        $router->add(
            Route::get(
                '/cundd-custom_rest-person/lastname/?',
                function (RestRequestInterface $request) {
                    return $this->responseFactory->createErrorResponse(
                        'Please add a last name: /cundd-custom_rest-person/lastname/{lastName}.',
                        404,
                        $request
                    );
                }
            )
        );

        # curl -X GET http://localhost:8888/rest/cundd-custom_rest-person/firstname
        $router->add(
            Route::get(
                '/cundd-custom_rest-person/firstname/?',
                function (RestRequestInterface $request) {
                    return $this->responseFactory->createErrorResponse(
                        'Please add a first name: /cundd-custom_rest-person/firstname/{firstName}.',
                        404,
                        $request
                    );
                }
            )
        );
    }
}
