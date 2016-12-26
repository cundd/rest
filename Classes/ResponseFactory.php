<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 24.04.14
 * Time: 21:08
 */

namespace Cundd\Rest;

use \Bullet\Response;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Factory class to create Response objects
 *
 * @package Cundd\Rest
 */
class ResponseFactory implements SingletonInterface, ResponseFactoryInterface
{
    /**
     * @var \Cundd\Rest\RequestFactoryInterface
     */
    protected $requestFactory;

    /**
     * Returns a response with the given message and status code
     *
     * @param string|array $data
     * @param int $status
     * @return Response
     */
    public function createErrorResponse($data, $status)
    {
        return $this->createResponse($data, $status, true);
    }

    /**
     * Returns a response with the given message and status code
     *
     * @param string|array $data
     * @param int $status
     * @return Response
     */
    public function createSuccessResponse($data, $status)
    {
        return $this->createResponse($data, $status);
    }

    /**
     * Returns a response with the given message and status code
     *
     * @param string|array $data Data to send
     * @param int $status Status code of the response
     * @param bool $forceError If TRUE the response will be treated as an error, otherwise any status below 400 will be a normal response
     * @return Response
     * @internal
     */
    public function createResponse($data, $status, $forceError = false)
    {
        $body = null;
        $response = new Response(null, $status);
        $format = $this->requestFactory->getRequest()->format();
        if (!$format) {
            $format = 'json';
        }

        $messageKey = 'message';
        if ($forceError || $status >= 400) {
            $messageKey = 'error';
        }

        switch ($format) {
            case 'json':

                switch (gettype($data)) {
                    case 'string':
                        $body = array(
                            $messageKey => $data
                        );
                        break;

                    case 'array':
                        $body = $data;
                        break;

                    case 'NULL':
                        $body = array(
                            $messageKey => $response->statusText($status)
                        );
                        break;
                }

                $response->contentType('application/json');
                $response->content(json_encode($body));
                break;

            case 'xml':
                // TODO: support more response formats

            default:
                $body = sprintf('Unsupported format: %s. Please set the Accept header to application/json', $format);
                $response->content($body);
        }
        return $response;
    }

    /**
     * @param \Cundd\Rest\RequestFactoryInterface $requestFactory
     */
    public function injectRequestFactory(\Cundd\Rest\RequestFactoryInterface $requestFactory)
    {
        $this->requestFactory = $requestFactory;
    }
}
