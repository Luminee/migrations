<?php

namespace Luminee\Migrations\Foundations;

use PDO;
use Exception;

trait PdoBuilder
{
    protected $env = [];

    private function getEnv()
    {
        if (empty($this->env))
            $this->env = include config('migrations.pdo.conf');
        return $this->env;
    }

    public function getProjects()
    {
        return array_keys($this->getEnv());
    }

    public function getConnections($project)
    {
        return array_keys($this->getEnv()[$project]);
    }

    public function getDatabase($project, $conn)
    {
        return $this->getEnv()[$project][$conn]['database'];
    }

    public function getConf($project, $conn)
    {
        $conf = $this->getEnv()[$project][$conn];
        $_conf = [];
        if (isset($conf['_extend'])) {
            list($p, $c) = explode(':', $conf['_extend']);
            $_conf = $this->getConf($p, $c);
        }
        $_conf = array_merge($_conf, $conf);
        unset($_conf['_extend']);
        return $_conf;
    }

    public function getPdo($project, $conn)
    {
        $conf = $this->getConf($project, $conn);
        return $this->newPdo($conf['host'], $conf['database'], $conf['username'], $conf['password']);
    }

    private function newPdo($host, $database, $username, $password)
    {
        try {
            return new PDO("mysql:host={$host};dbname={$database};charset=utf8", $username, $password);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @param $table
     * @param PDO $pdo
     * @return mixed
     */
    public function fetchCreateTable($table, PDO $pdo)
    {
        if (!$stmt = $pdo->query("SHOW CREATE TABLE `$table`")) return null;
        $table = $stmt->fetch()['Create Table'];
        return $table;
    }

    public function modifyDatabaseConfig($project, $conn)
    {
        $conf = $this->getConf($project, $conn);
        $default = config('database.default');
        foreach ($conf as $key => $vale) {
            config(["database.connections.$default.$key" => $vale]);
        }
    }

}