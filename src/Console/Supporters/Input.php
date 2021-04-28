<?php

namespace Luminee\Migrations\Console\Supporters;

class Input
{
    protected $command;

    protected $argv;

    protected $frame = [];

    protected $args = [];

    protected $opts = [];

    public function __construct()
    {
        $argv = isset($_SERVER['argv']) ? $_SERVER['argv'] : [];

        // Artisan or Chariot
        array_shift($argv);
        $this->argv = $argv;

        $this->command = array_shift($argv);

        foreach ($argv as $item) {
            if (strpos($item, '--') !== 0) {
                $this->args[] = $item;
            } else {
                $v = explode('=', $item);
                $this->opts[$v[0]] = count($v) > 1 ? $v[1] : true;
            }
        }
    }

    public function getCommand()
    {
        return $this->command;
    }

    public function getArguments()
    {
        return $this->args;
    }

    public function getOption($key, $default = null)
    {
        return $this->hasOption($key) ? $this->opts['--' . $key] : $default;
    }

    public function getFrame($key)
    {
        return $this->frame[$key];
    }

    public function hasOption($key)
    {
        return isset($this->opts['--' . $key]);
    }

    /**
     * @param string|array $key
     * @param null $value
     */
    public function setFrame($key, $value = null)
    {
        !is_array($key) ?
            $this->frame[$key] = $value :
            $this->frame = array_merge($this->frame, $key);
    }
}