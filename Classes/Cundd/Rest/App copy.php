<?php


class DataProvider {
    protected $data = array();

    public function __construct() {
        #$this->data = unserialize($this->getPersistanceLibrary()->get('data'));
        $this->data = $this->getPersistanceLibrary()->get('data_obj');
        if (!$this->data) {
            $this->data = array(
                1 => array(
                    'id' => 1,
                    'firstName' => 'Daniel',
                    'lastName'  => 'Corn'
                )
            );
        }

        register_shutdown_function(array($this, 'persistAll'));
    }

    /**
     * Adds an object to this repository.
     *
     * @param object $object The object to add
     * @return void
     * @api
     */
    public function add(&$object) {
        $uid = NULL;
        if (isset($object['id'])) {
            $uid = $object['id'];
        } else {
            $uid = $object['id'] =  (count($this->data) + 1);
        }
        $this->data[$uid] = $object;
    }

    /**
     * Removes an object from this repository.
     *
     * @param object $object The object to remove
     * @return void
     * @api
     */
    public function remove($object) {
        $uid = isset($object['id']) ? $object['id'] : count($this->data);
        unset($this->data[$uid]);
    }

    /**
     * Replaces an object by another.
     *
     * @param object $existingObject The existing object
     * @param object $newObject The new object
     * @return void
     * @api
     */
    public function replace($existingObject, $newObject) {
        $uid = isset($existingObject['id']) ? $existingObject['id'] : $newObject['id'];
        $this->data[$uid] = $newObject;
    }

    /**
     * Replaces an existing object with the same identifier by the given object
     *
     * @param object $modifiedObject The modified object
     * @api
     */
    public function update($modifiedObject) {
        $this->replace($modifiedObject, $modifiedObject);
    }

    public function findAll() {
        return $this->data;
    }

    /**
     * Finds an object matching the given identifier.
     *
     * @param integer $uid The identifier of the object to find
     * @return object The matching object if found, otherwise NULL
     * @api
     */
    public function findByUid($uid) {
        return isset($this->data[$uid]) ? $this->data[$uid] : NULL;
    }

    public function findByProperty($property, $value) {
        foreach ($this->data as $user) {
            if ($user[$property] === $value) {
                return $user;
            }
        }
        return NULL;
    }

    public function persistAll() {
        $this->getPersistanceLibrary()->set('data', serialize($this->data));
        $this->getPersistanceLibrary()->set('data_obj', $this->data);
    }

    public function getPersistanceLibrary() {
        static $persistanceLibrary = NULL;
        if (!$persistanceLibrary) {
            $persistanceLibrary = new Predis\Client();
        }
        return $persistanceLibrary;
    }

    static public function instance() {
        static $instance = NULL;
        if (!$instance) {
            $instance = new static;
        }
        return $instance;
    }
}



// Your App
$app = new Bullet\App();
$app->path('/', function($request) {
    return "Hello World!";
});


$app->path('app', function($request) use($app) {
    $app->path('users', function($request) use($app) {
        $app->param('int', function($request, $id) use($app) {
            $app->get(function($request) use($id) {
                return DataProvider::instance()->findByUid($id);
            });
            $app->put(function($request) use($id) {
                // Update resource
                $postData = $request->post();
                DataProvider::instance()->update($postData);
                var_dump($postData);
                return 'update_' . $postData['id'];
            });
            $app->delete(function($request) use($id) {
                // Delete resource
                DataProvider::instance()->remove(DataProvider::instance()->findByUid($id));
                return array('success' => TRUE, 'id' => $id);
            });
        });

        // URL slug (alphanumeric with dashes and underscores)
        $app->param('slug', function($request, $slug) use($app) {
            return DataProvider::instance()->findByProperty('firstName', $slug);
        });

        $app->post(function($request) {
            // Update resource
            $postData = $request->post();
            DataProvider::instance()->add($postData);
            return $postData;
        });

        $app->get(function($request) {
            return DataProvider::instance()->findAll();
        });
    });
});


$objectManager = new \TYPO3\CMS\Extbase\Object\ObjectManager();

$GLOBALS['typo3CacheManager'] = new TYPO3\CMS\Core\Cache\CacheManager();
$bootstrap = $objectManager->get('\\TYPO3\\CMS\\Extbase\\Core\\BootstrapInterface');


echo $app->run(new Bullet\Request());


?>