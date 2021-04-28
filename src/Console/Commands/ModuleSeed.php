<?php

namespace Luminee\Migrations\Console\Commands;

use DB;
use ReflectionException;
use Illuminate\Console\Command;
use Luminee\Migrations\Base\SeederBaseModel;
use Luminee\Migrations\Foundations\PdoBuilder;
use Luminee\Migrations\Foundations\ConsoleTrait;

class ModuleSeed extends Command
{
    use PdoBuilder, ConsoleTrait;

    protected $batch;

    protected $seeders;

    protected $database;

    protected $count;

    protected $run = false;

    protected $dir;

    protected $baseDir;

    protected $baseNamespace;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:seed {project} {module} {conn=dev} {--common} {--class=} {--except=} {--run} {--force} {--o|optimize}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed Data Into Database By Project Modules';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->baseDir = config('migrations.seeders.dir');
        $this->baseNamespace = config('migrations.seeders.namespace');
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     * @throws
     */
    public function handle()
    {
        if ($this->option('optimize'))
            exec("composer -o dump");
        $module = $this->argument('module');
        $class = $this->option('class');
        $force = $this->option('force');
        $this->run = $this->option('run');
        $pdoProject = $this->argument('project');
        $project = $this->getProject();
        foreach (explode(',', $this->argument('conn')) as $_conn) {
            $this->count = 0;
            $this->comment('======== Conn on [' . $_conn . '] ========');
            $this->setPdo($pdoProject, $_conn);
            $this->prepareSeederTable();

            if (!is_dir($this->dir = $this->getDir($this->baseDir, $project, $module))) {
                $this->error("Project [$project] or Module [$module] folder doesn't exist! Please check and try again");
                continue;
            }

            if (!is_null($class)) {
                $this->handleClassSeed($project, $module, $_conn, $class, $force);
            } else {
                $this->handleModuleSeed($project, $module, $_conn);
            }
        }
        if ($this->run)
            $this->info('Module Seed Has Done! ^_^');
    }

    /**
     * @param $project
     * @param $module
     * @param $conn
     * @param $class
     * @param $force
     * @throws ReflectionException
     */
    protected function handleClassSeed($project, $module, $conn, $class, $force)
    {
        $full_class = $this->getNamespace($this->baseNamespace, $project, $module) . '\\' . $class;
        $file = $this->getFileName($full_class);
        if (!in_array($file, $this->seeders) || $force) {
            $Class = new $full_class($this->database, $conn);
            $this->seedClass($Class, $file, $class);
            if ($this->run)
                $this->info($class . ($force ? ' Force' : '') . ' Seed Success!');
        } else {
            $this->line($class . ' Has been seed... you can use [--force]');
        }
    }

    protected function handleModuleSeed($project, $module, $conn)
    {
        $except = $this->option('except') ?: null;
        foreach (scandir($this->getDir($this->baseDir, $project, $module)) as $seeder) {
            if (in_array($seeder, ['.', '..'])) continue;
            $name = str_replace('.php', '', $seeder);
            if (in_array($name, $this->seeders)) continue;
            $class = $this->convertUnderline(substr($name, 18));
            if ($except && $class == $except) continue;
            $full_class = $this->getNamespace($this->baseNamespace, $project, $module) . '\\' . $class;
            $Class = new $full_class($this->database, $conn);
            $this->seedClass($Class, $name, $class);
        }
        if ($this->run)
            $this->line("Module [$module] " . ($this->count == 0 ? 'Nothing to seed.' : 'Seed done!'));
    }

    /**
     * @param SeederBaseModel $class
     * @param $name
     * @param $_class
     */
    protected function seedClass($class, $name, $_class)
    {
        if ($this->run) {
            $class->run();
            $this->recordSeeder($name);
            $this->count++;
            $this->info($name . ' Seed.');
        } else {
            $content = file_get_contents($this->dir . '/' . $name . '.php');
            $this->comment($name . ' [' . $_class . '] Content: ');
            $this->printSeeder($content);
        }
    }

    protected function printSeeder($content)
    {
        list($_, $content) = explode("extends SeederBaseModel\n", $content);
        $table = $this->getPregStr($content, '/\$table = \'(\S+)\'/');
        $this->info('Table : ' . $table . "\r\n");
        list($_, $content) = explode("public function run()\n", $content);
        $function = ltrim(preg_replace("/}\n}$/", '', trim($content)), "{\n");
        $this->line($function);
    }

    protected function prepareSeederTable()
    {
        $this->createSeedersTable();
        $this->seeders = DB::table('seeders')->pluck('seeder')->toArray();
        $this->batch = DB::table('seeders')->max('batch') + 1;
    }

    protected function createSeedersTable()
    {
        if (!empty(DB::select("Show tables like 'seeders'"))) return true;
        $schema = "CREATE TABLE IF NOT EXISTS `seeders` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT, `seeder` varchar(255) COLLATE utf8_unicode_ci NOT NULL, `batch` int(11) NOT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        return DB::select($schema);
    }

    protected function recordSeeder($class)
    {
        if (!in_array($class, $this->seeders)) {
            DB::table('seeders')->insert(['seeder' => $class, 'batch' => $this->batch]);
        }
    }

    // Private Functions

    private function setPdo($project, $conn)
    {
        $this->database = $this->getDatabase($project, $conn);
        DB::setPdo($this->getPdo($project, $conn));
    }

    private function getPregStr($str, $pattern, $error = 'Preg')
    {
        preg_match($pattern, $str, $match);
        if (empty($match[1])) dd($error . ' Error: ' . $str . ' => ' . $pattern);
        return $match[1];
    }


}
