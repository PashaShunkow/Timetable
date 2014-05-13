<?php

class App
{
    const SRC_FOLDER      = 'src';
    const SERVICES_FOLDER = 'System';
    const ENTITY_FOLDER   = 'Entities';
    const RESOURCE_FOLDER = 'resources';
    const TEMPLATE_FOLDER = 'templates';

    static private $_appRoot;
    static private $_app;

    protected $_services = array(
        'config'    => null,
        'router'    => null,
        'layout'    => null,
        'dbAdapter' => null
    );

    protected function __construct()
    {
    }

    /**
     * Return base app dir
     *
     * @return string
     */
    static public function getBaseDir()
    {
        if (self::$_appRoot) {
            return self::$_appRoot;
        }

        $appRoot = realpath(dirname(__FILE__));

        if (is_dir($appRoot) and is_readable($appRoot)) {
            self::$_appRoot = $appRoot;
            return self::$_appRoot;
        } else {
            self::error($appRoot . ' is not a directory or not readable by this user');
        }
    }

    /**
     * Return base url of the site
     *
     * @param null $route
     *
     * @return string
     */
    static public function getBaseUrl($route = null)
    {
        return sprintf(
            "%s://%s",
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
            $_SERVER['HTTP_HOST']
        ) . DIRECTORY_SEPARATOR .$route;
    }

    /**
     * Return folder for template files
     *
     * @return string
     */
    static public function getTemplateDir()
    {
        return self::getBaseDir() . DIRECTORY_SEPARATOR . self::RESOURCE_FOLDER . DIRECTORY_SEPARATOR . self::TEMPLATE_FOLDER;
    }


    /**
     * Return entity folder
     *
     * @return string
     */
    static public function getEntityDir()
    {
        return self::getSrcDir() . DIRECTORY_SEPARATOR . self::ENTITY_FOLDER;
    }

    /**
     * Return src folder
     *
     * @return string
     */
    static public function getSrcDir()
    {
        return self::getBaseDir() . DIRECTORY_SEPARATOR . self::SRC_FOLDER;
    }

    /**
     * Run main flow
     */
    static public function run()
    {
        if (!self::$_app) {
            self::$_app = new self();
        }
        self::$_app->_init();
        self::instance()->getService('layout')->renderLayout();
    }

    /**
     * Return app instance
     *
     * @return App
     */
    static public function instance()
    {
        if (!self::$_app) {
            self::run();
        }
        return self::$_app;
    }

    /**
     * Init the app
     */
    protected function _init()
    {
        $this->_registerAutoloder();
        $this->_initServices();
        $this->getService('router')->route();
    }

    protected function _registerAutoloder()
    {
        require_once self::SRC_FOLDER . DIRECTORY_SEPARATOR . self::SERVICES_FOLDER . DIRECTORY_SEPARATOR . 'Autoloader.php';
        spl_autoload_register('Autoloader::autoload');
    }

    /**
     * Init system services
     */
    protected function _initServices()
    {
        foreach ($this->_services as $name => $service) {
            if ($service == null) {
                $class = self::SERVICES_FOLDER . '\\' . ucfirst($name);
                $service = new $class();
                $this->_services[$name] = $service;
            }
        }
    }

    /**
     * Return defined system service
     *
     * @param  string $serviceName Name of system service
     * @return mixed
     */
    public function getService($serviceName)
    {
        if (isset($this->_services[$serviceName])) {
            return $this->_services[$serviceName];
        }
        self::error('There is no such : "' . $serviceName . '" service in the app');
    }

    /**
     * Wrapper for app errors
     *
     * @param string $errorText Error text
     */
    static public function error($errorText)
    {
        die($errorText);
    }

    /**
     * Convert input array into JSON string
     *
     * @param array $inputArray
     *
     * @return string
     */
    static public function JSONEncode($inputArray)
    {
        $outString = json_encode($inputArray);
        return $outString;
    }

    /**
     * Convert input JSON string into array
     *
     * @param      $inputString
     * @param bool $likeArray
     *
     * @return mixed
     */
    static public function JSONDecode($inputString, $likeArray = false)
    {
        $outArray = json_decode($inputString, $likeArray);
        return $outArray;
    }
}