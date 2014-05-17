<?php

class App
{
    const APP_FOLDER      = 'app';
    const SRC_FOLDER      = 'src';
    const SERVICES_FOLDER = 'System';
    const ENTITY_FOLDER   = 'Entities';
    const RESOURCE_FOLDER = 'resources';
    const TEMPLATE_FOLDER = 'templates';
    const SKIN_FOLDER     = 'skin';

    static private $_appRoot;
    static private $_app;

    protected $_services = array(
        'request'   => null,
        'config'    => null,
        'router'    => null,
        'factory'    => null,
        'dbAdapter' => null
    );

    protected $_rootView;

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
     * Return base url of the site, add params if specified
     *
     * @param  null $route
     * @param  array  $params Get params
     *
     * @return string
     */
    static public function getBaseUrl($route = null, $params = array())
    {
        /** @var  $request System\Request */
        $request   = self::instance()->getService('request');
        $https     = $request->getSERVER('HTTPS');
        $httpHost  = $request->getSERVER('HTTP_HOST');
        $paramsStr = '';
        if (!empty($params)) {
            $paramsStr = self::_convertToString($params);
        }
        return sprintf(
            "%s://%s",
            isset($https) && $https != 'off' ? 'https' : 'http',
            $httpHost
        ) . '/' . $route . $paramsStr;
    }

    /**
     * Convert params array to string
     *
     * @param  array  $params params array ($key => $value)
     * @return string
     */
    protected function _convertToString($params)
    {
        $paramsStr = '';
        foreach ($params as $name => $value) {
            if ($name !== null && $value !== null) {
                $paramsStr .= '/' . $name . '-' . $value;
            }
        }
        return $paramsStr;
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
     * Return skin folder
     *
     * @return string
     */
    static public function getSkinDir()
    {
        return self::getBaseDir() . DIRECTORY_SEPARATOR .self::RESOURCE_FOLDER . DIRECTORY_SEPARATOR . self::SKIN_FOLDER;
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
        self::instance()->startRender();
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
     * @param  \Entities\Root\View $view View object
     * @return $this
     */
    public function setRootView($view)
    {
        $this->_rootView = $view;
        return $this;
    }

    /**
     * Return root view object
     *
     * @return mixed
     */
    public function getRootView()
    {
        return $this->_rootView;
    }

    /**
     * Render all views
     */
    public function startRender($output = null)
    {
        if (!$output) {
            $output = 'getHtml';
        }

        $outHtml = $this->_rootView->$output();
        echo $outHtml;
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
     * System log error method
     *
     * @param string $errorText   Log message
     * @param bool   $critical    Is error critical(will stop the script if true)
     * @param bool   $logFileName Specified log file
     */
    static public function error($errorText, $critical = false, $logFileName = false)
    {
        /** @var  $sysConfig  System\Config */
        $sysConfig = self::instance()->getService('config');
        $errorText = self::_prepareErrorMessage($errorText);
        if (!$logFileName) {
            $logFileName = $sysConfig->getConfigArea('debug/log_file');
        }
        $logDirectory = self::getBaseDir() . DIRECTORY_SEPARATOR . $sysConfig->getConfigArea('debug/log_dir');
        if (!is_dir($logDirectory)) {
            if (!mkdir($logDirectory, 0777)) {
                die('Cant create log directory in ' . $logDirectory);
            }
        }
        $logFile = $logDirectory . DIRECTORY_SEPARATOR . $logFileName;
        file_put_contents($logFile, $errorText, FILE_APPEND | LOCK_EX);
        if ($critical) {
            die('System crashed because of critical error :' . $errorText);
        }
    }

    /**
     * Prepare error message
     *
     * @param  string $message
     * @return string
     */
    protected function _prepareErrorMessage($message)
    {
        $message = 'SYSTEM LOG ( ' . $date = date('Y/m/d H:i:s', time()) . ' ): ' . $message ."\n";
        return $message;
    }

    /**
     * Redirect on specified url
     *
     * @param string $url                  Base url
     * @param bool   $removeExistingParams Set true fro get clear URL (without old get params)
     * @param null   $params Params array
     */
    static public function redirectOn($url, $params = null, $removeExistingParams = false)
    {
        if ($removeExistingParams || $params) {
            $url = self::_rebuildUrl($url);
        }
        if ($params) {
            $url .= self::_convertToString($params);
        }
        header('Location: ' . $url);
    }

    /**
     * Rebuild url for remove 'old' get params
     *
     * @param  string $url Url
     * @return string
     */
    protected function _rebuildUrl($url)
    {
        $requestUri = str_replace(self::getBaseUrl(), '', $url);
        if($requestUri && strpos($requestUri, '/') !== false){
            if (strpos($requestUri, '/') === 0) {
                $requestUri = substr($requestUri, 1);
            }
            $parts = explode('/', $requestUri);
            $requestUri = '';
            for ($i = 0; $i < 3; $i++) {
                if (isset($parts[$i])) {
                    $requestUri .= $parts[$i] . '/';
                }
            }
        }
        return self::getBaseUrl() . rtrim($requestUri, '/');
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