<?php
/**
 * System Autoloader
 *
 * @category  TimetableTool
 * @package   TimetableTool
 * @author    Paul Shunkow
 * @copyright 2014 Paul Shunkow
 */
class Autoloader
{
    /**
     * System autoloader
     *
     * @param string $class Class name
     */
    static public function autoload($class)
    {
        $classFilePath  = App::getBaseDir() . DIRECTORY_SEPARATOR . App::SRC_FOLDER;
        $classPathParts = explode('\\', $class);


        for ($i = 0; $i < count($classPathParts); $i++) {
            $classFilePath .= DIRECTORY_SEPARATOR . $classPathParts[$i];
        }

        $classFilePath .= '.php';

        if (is_file($classFilePath)) {
            require_once $classFilePath;
        }else{
            //App::error('Class: ' . $class . ' could not be loaded, not file exist ' . $classFilePath);
        }
    }
}