<?php

namespace Luminee\Migrations\Console\Supporters;

use DB;
use Exception;
use Luminee\Migrations\Foundations\DBBuilder;
use Luminee\Migrations\Foundations\PdoBuilder;
use Luminee\Migrations\Foundations\RepoBuilder;
use Luminee\Migrations\Foundations\RedisBuilder;

abstract class Command
{
    use PdoBuilder, RedisBuilder, InOutput, DBBuilder, RepoBuilder;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature;

    protected $description;

    private $pdo_pool = [];

    protected $conn;

    protected $args = [];

    protected $opts = [];

    protected $pdo = '';

    protected $origin_pc = '';

    public function __construct()
    {
    }

    /**
     * @param Input $input
     * @param Output $output
     * @return $this
     * @throws Exception
     */
    public function boot(Input $input, Output $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->handleInput();
        $p = $this->input->getFrame('project');
        $this->conn = $this->input->getFrame('conn');
        $this->pdo = $this->origin_pc = $p . '_' . $this->conn;
        if (is_array($pdo = $this->input->getFrame('pdo')))
            $this->pdo .= '.read';
        $this->pdo_pool[$this->origin_pc] = $pdo;
        return $this;
    }

    /**
     * @throws Exception
     */
    private function handleInput()
    {
        preg_match_all('/\{\s*(.*?)\s*\}/', $this->signature, $matches);
        $args = $opts = [];
        foreach ($matches[1] as $match) {
            if (strpos($match, '--') !== 0) {
                $args[] = $this->handleInputArg($match);
            } else {
                $v = explode('=', str_replace('--', '', $match));
                $opts[$v[0]] = count($v) > 1 ? $v[1] : false;
            }
        }
        $this->fillArgs($args);
        $this->fillOpts($opts);
    }

    private function handleInputArg($item)
    {
        if (strstr($item, '?'))
            return ['k' => str_replace('?', '', $item), 'm' => false, 'd' => null];
        if (count($v = explode('=', $item)) > 1)
            return ['k' => $v[0], 'm' => false, 'd' => $v[1]];
        return ['k' => $item, 'm' => true, 'd' => null];
    }

    /**
     * @param $args
     * @throws Exception
     */
    private function fillArgs($args)
    {
        $ia = $this->input->getArguments();
        foreach ($args as $k => $arg) {
            if (!isset($ia[$k]) && $arg['m'])
                throw new Exception('Wrong Args');
            $this->args[$arg['k']] = isset($ia[$k]) ? $ia[$k] : $arg['d'];
        }
    }

    private function fillOpts($opts)
    {
        foreach ($opts as $k => $d) {
            $this->opts[$k] = $this->input->getOption($k, $d);
        }
    }

    /**
     * @return mixed
     */
    abstract public function run();

    /**
     * @param $key
     * @return mixed
     */
    public function argument($key)
    {
        return $this->args[$key];
    }

    /**
     * @return array
     */
    public function arguments()
    {
        return $this->args;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function option($key)
    {
        return $this->opts[$key];
    }

    /**
     * @return array
     */
    public function options()
    {
        return $this->opts;
    }

    /**
     * @return void
     */
    public function switchRead()
    {
        $this->switchPdo('read');
    }

    /**
     * @return void
     */
    public function switchWrite()
    {
        $this->switchPdo('write');
    }

    /**
     * @param $project
     * @param $conn
     * @return void
     */
    public function switchProjectPdo($project, $conn)
    {
        $pc = $project . '_' . $conn;
        if ($this->pdo == $pc) return;
        DB::setPdo($this->pdo_pool[$pc] ?? $this->getPdo($project, $conn));
        $this->pdo = $pc;
    }

    private function switchPdo($type)
    {
        if (!isset($this->pdo_pool[$this->origin_pc][$type])
            || $this->pdo == $this->origin_pc . '.' . $type)
            return;
        DB::setPdo($this->pdo_pool[$this->origin_pc][$type]);
        $this->pdo = $this->origin_pc . '.' . $type;
    }


}