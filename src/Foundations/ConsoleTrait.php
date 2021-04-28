<?php

namespace Luminee\Migrations\Foundations;

use ReflectionClass;
use ReflectionException;

trait ConsoleTrait
{
    /**
     * @return string
     */
    public function getProject()
    {
        return $this->option('common') ? 'common' : $this->argument('project');
    }

    /**
     * @param $dir
     * @param null $project
     * @param null $module
     * @return string
     */
    public function getDir($dir, $project = null, $module = null)
    {
        if (is_null($project))
            return $dir;

        if (is_null($module))
            return $dir . '/' . $project;

        return $dir . '/' . $project . '/' . $module;
    }

    /**
     * @param $base
     * @param $project
     * @param $module
     * @return string
     */
    public function getNamespace($base, $project, $module)
    {
        return ucfirst($base) . '\\' . ucfirst($project) . '\\' . ucfirst($module);
    }

    /**
     * @param string $className
     * @return string
     * @throws ReflectionException
     */
    public function getFileName(string $className)
    {
        $ref = new ReflectionClass($className);
        $names = explode(DIRECTORY_SEPARATOR, $ref->getFileName());
        return str_replace('.php', '', end($names));
    }

    /**
     * @param $str
     * @return string
     */
    public function convertUnderline($str)
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $str)));
    }

    /**
     * error & die
     * @param $string
     */
    public function dError($string)
    {
        $this->error($string);
        exit();
    }

    /**
     * error & continue
     * @param $string
     * @return boolean
     */
    public function cError($string)
    {
        $this->error($string);
        return false;
    }
}