<?php /** @noinspection PhpUnusedParameterInspection */

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
     * @var Helper
     */
    protected $helper;

    /**
     * Handler constructor
     *
     * @param ObjectManagerInterface   $objectManager
     * @param ResponseFactoryInterface $responseFactory
     * @param Helper                   $helper
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ResponseFactoryInterface $responseFactory,
        Helper $helper
    ) {
        $this->objectManager = $objectManager;
        $this->responseFactory = $responseFactory;
        $this->helper = $helper;
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
         * Sample Routes for Controller "Person"
         *-----------------------------------------------------*/

        /*
         * To define a new "base" route, a specific path is assigned to Route::get
         * instead of the universal $request->getResourceType(). Here it is the path
         * "/cundd-custom_rest-person"
         */

        /* ------------ GET ------------- */

        # curl -X GET http://localhost:8888/rest/cundd-custom_rest-person
        $router->add(
            Route::get(
                '/cundd-custom_rest-person/?',
                function (RestRequestInterface $request) {
                    return $this->helper->callExtbasePlugin(
                        'customRest',
                        'Cundd',
                        'CustomRest',
                        'Person',
                        'list',
                        []
                    );
                }
            )
        );

        # curl -X GET http://localhost:8888/rest/cundd-custom_rest-person/show/12
        $router->add(
            Route::get(
                '/cundd-custom_rest-person/show/{int}/?',
                function (RestRequestInterface $request, $int) {
                    $arguments = [
                        'uid' => $int,
                    ];

                    return $this->helper->callExtbasePlugin(
                        'customRest',
                        'Cundd',
                        'CustomRest',
                        'Person',
                        'show',
                        $arguments
                    );
                }
            )
        );

        # curl -X GET http://localhost:8888/rest/cundd-custom_rest-person/firstname/daniel
        $router->add(
            Route::get(
                '/cundd-custom_rest-person/firstname/{slug}/?',
                function (RestRequestInterface $request, $slug) {
                    $arguments = [
                        'firstName' => $slug,
                    ];

                    return $this->helper->callExtbasePlugin(
                        'customRest',
                        'Cundd',
                        'CustomRest',
                        'Person',
                        'firstName',
                        $arguments
                    );
                }
            )
        );

        # curl -X GET http://localhost:8888/rest/cundd-custom_rest-person/lastname/corn
        $router->add(
            Route::get(
                '/cundd-custom_rest-person/lastname/{slug}/?',
                function (RestRequestInterface $request, $slug) {
                    $arguments = [
                        'lastName' => $slug,
                    ];

                    return $this->helper->callExtbasePlugin(
                        'customRest',
                        'Cundd',
                        'CustomRest',
                        'Person',
                        'lastName',
                        $arguments
                    );
                }
            )
        );

        # curl -X GET http://localhost:8888/rest/cundd-custom_rest-person/birthday/0000-00-00
        $router->add(
            Route::get(
                '/cundd-custom_rest-person/birthday/{slug}/?',
                function (RestRequestInterface $request, $slug) {
                    $arguments = [
                        'date' => $slug,
                    ];

                    return $this->helper->callExtbasePlugin(
                        'customRest',
                        'Cundd',
                        'CustomRest',
                        'Person',
                        'birthday',
                        $arguments
                    );
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

        /* ------------ POST ------------- */

        # curl -X POST -H "Content-Type: application/json" -d '{"firstName":"john","lastName":"john"}' http://localhost:8888/rest/customhandler/create
        $router->add(
            Route::post(
                $request->getResourceType() . '/create/?',
                function (RestRequestInterface $request) {
                    $arguments = [
                        'person' => $request->getSentData(),
                    ];

                    return $this->helper->callExtbasePlugin(
                        'customRest',
                        'Cundd',
                        'CustomRest',
                        'Person',
                        'create',
                        $arguments
                    );
                }
            )
        );

        # curl -X PATCH -H "Content-Type: application/json" -d '{"firstName":"john","lastName":"john"}' http://localhost:8888/rest/customhandler/update/1
        $router->add(
            Route::patch(
                $request->getResourceType() . '/update/{int}/?',
                function (RestRequestInterface $request, $id) {
                    $arguments = [
                        'person' => $request->getSentData(),
                    ];
                    $arguments['person']['__identity'] = $id;

                    return $this->helper->callExtbasePlugin(
                        'customRest',
                        'Cundd',
                        'CustomRest',
                        'Person',
                        'update',
                        $arguments
                    );
                }
            )
        );
    }
}
