<?php

namespace Luminee\Migrations\Console\Supporters;

use DB;
use Exception;
use Illuminate\Contracts\Container\Container;
use Luminee\Migrations\Foundations\PdoBuilder;

class Application
{
    use PdoBuilder;

    /**
     * The Laravel application instance.
     *
     * @var Container
     */
    protected $app;

    protected $baseNamespace;

    protected $multi_pdo;

    /**
     * Create a new Artisan console application.
     *
     * @param Container $app
     * @return void
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
        $this->multi_pdo = config('migrations.pdo.multi_pdo');
        $this->baseNamespace = config('migrations.scripts.namespace');
    }

    /**
     * @param Input|null $input
     * @param Output|null $output
     * @return int
     * @throws Exception
     */
    public function run(Input $input = null, Output $output = null)
    {
        $command = $input->getCommand();
        if (is_null($command)) return $this->chariotHelp($output);
        list($class, $p, $c) = $this->parseClass($command);
        $c = $input->getOption('CONN', $c);
        $input->setFrame('pdo', $this->buildPdo($p, $c));
        $input->setFrame(['project' => $p, 'conn' => $c]);
        if (!$input->getOption('run'))
            return $this->showPdo();
        $Class = $this->app->make($class);
        $Class->boot($input, $output)->run();
        return 0;
    }

    /**
     * @param $command
     * @return array
     * @throws Exception
     */
    protected function parseClass($command)
    {
        list($p, $m, $n, $c) = $this->parseCommand($command);
        $name = ucfirst($p) . '\\' . ucfirst($m) . '\\' .
            str_replace(':', '_', ucfirst($n));
        $class = $this->baseNamespace . '\\';
        foreach (explode('_', $name) as $a) {
            $class .= ucfirst($a);
        }
        return [$class, $p, $c];
    }

    /**
     * @param $commend
     * @return array|false|string[]
     * @throws Exception
     */
    protected function parseCommand($commend)
    {
        $items = explode('.', $commend);
        switch (count($items)) {
            case 4:
                return $items;
            case 3:
                return array_merge($items, ['dev']);
            case 2:
                return array_merge(['core'], $items, ['dev']);
            default:
                throw new Exception('Wrong Command!');
        }
    }

    protected function buildPdo($project, $conn)
    {
        if (!isset($this->multi_pdo[$project][$conn])) {
            DB::setPdo($pdo = $this->getPdo($project, $conn));
            return $pdo;
        }
        $c = $this->multi_pdo[$project][$conn];
        $pdo = [
            'read' => $this->getPdo($project, $c['r']),
            'write' => $this->getPdo($project, $c['w']),
        ];
        DB::setPdo($pdo['read']);
        return $pdo;
    }

    /**
     * @param Output $output
     * @return int
     */
    protected function chariotHelp(Output $output)
    {
        $string = 'Help';
        $output->writeln("<info>$string</info>");
        return 0;
    }

    protected function showPdo()
    {
        $host = str_replace(' via TCP/IP', '', DB::getPdo()->getAttribute(7));
        $user = current(DB::select("select user() as _u;"));
        $output = "\n   host: " . $this->warning($host);
        print_r($output . "\n   user: " . $this->warning($user->_u));
        return 0;
    }

    protected function warning($string)
    {
        return "\033[48;5;1m" . $string . "\033[0m";

    }

}