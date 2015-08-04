<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 24.12.14
 * Time: 12:45
 */

namespace Cundd\Rest\Test;
require_once __DIR__ . '/../Bootstrap.php';

class AbstractCase extends \TYPO3\CMS\Core\Tests\FunctionalTestCase {
    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected $objectManager;

    public function setUp() {
        parent::setUp();
        $this->objectManager = new \TYPO3\CMS\Extbase\Object\ObjectManager();
    }

    /**
     * Build a new request with the given URI
     *
     * @param string $uri
     * @param string $format
     * @return \Cundd\Rest\Request
     */
    public function buildRequestWithUri($uri, $format = null) {
        $uri = filter_var($uri, FILTER_SANITIZE_URL);

//        // Strip the format from the URI
//        $resourceName = basename($uri);
//        $lastDotPosition = strrpos($resourceName, '.');
//        if ($lastDotPosition !== FALSE) {
//            $newUri = '';
//            if ($uri !== $resourceName) {
//                $newUri = dirname($uri) . '/';
//            }
//            $newUri .= substr($resourceName, 0, $lastDotPosition);
//            $uri = $newUri;
//
//            $format = substr($resourceName, $lastDotPosition + 1);
//        }



        $path = strtok($uri, '/');

        $request = new \Cundd\Rest\Request(NULL, $uri);
        $request->initWithPathAndOriginalPath($path, $path);
        $request->injectConfigurationProvider($this->objectManager->get('Cundd\\Rest\\ObjectManager')->getConfigurationProvider());
        if ($format) {
            $request->format($format);
        }
        return $request;
    }
}
