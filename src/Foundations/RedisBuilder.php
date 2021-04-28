<?php

namespace Luminee\Migrations\Foundations;

use Exception;
use Predis\Client;

trait RedisBuilder
{
    protected $env = [];

    private function getEnv_()
    {
        if (empty($this->env))
            $this->env = include config('migrations.redis.conf');
        return $this->env;
    }

    protected function getConf_($project, $conn)
    {
        return $this->getEnv_()[$project][$conn];
    }

    public function getRedis($project, $conn)
    {
        $conf = $this->getConf_($project, $conn);
        $option = ['parameters' => ['password' => $conf['password']]];
        if (isset($conf['database']))
            $option['parameters']['database'] = $conf['database'];
        foreach ($conf['host'] as $host) {
            $redis = $this->getMasterRedis($host, $option);
            if (!is_null($redis)) return $redis;
        }
        return null;
    }

    private function getMasterRedis($host, $option)
    {
        try {
            $redis = new Client($host, $option);
            $redis->setex('master_redis', 30, $host);
        } catch (Exception $e) {
            return null;
        }
        return $redis;
    }

}