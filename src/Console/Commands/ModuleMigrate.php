<?php

namespace Luminee\Migrations\Console\Commands;

use DB;
use ReflectionException;
use Illuminate\Console\Command;
use Luminee\Migrations\Foundations\PdoBuilder;
use Luminee\Migrations\Base\MigrationBaseModel;
use Luminee\Migrations\Foundations\ConsoleTrait;

class ModuleMigrate extends Command
{
    use PdoBuilder, ConsoleTrait;

    protected $database;

    protected $migrations;

    protected $batch;

    protected $count;

    protected $run = false;

    protected $baseDir;

    protected $baseNamespace;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:migrate {project} {module} {conn=dev} {--class=} {--except=} {--common} {--run} {--print} {--o|optimize}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate Database Like module:migrate';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->baseDir = config('migrations.migrations.dir');
        $this->baseNamespace = config('migrations.migrations.namespace');
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
        $module = $this->argument('module');
        $class = $this->option('class');
        $this->run = $this->option('run');
        $pdoProject = $this->argument('project');
        if ($print = $this->option('print'))
            $this->run = false;
        $project = $this->getProject();
        if ($this->option('optimize'))
            exec("composer -o dump");
        foreach (explode(',', $this->argument('conn')) as $_conn) {
            $this->count = 0;
            $this->comment('======== Conn on [' . $_conn . '] ========');
            $this->modifyConn($pdoProject, $_conn);
            $this->prepareMigrationsTable();

            if (!is_dir($this->getDir($this->baseDir, $project, $module))) {
                $this->error("Project [$project] or Module [$module] folder doesn't exist! Please check and try again");
                continue;
            }

            if (!is_null($class)) {
                $this->handleClassMigrate($project, $module, $_conn, $print, $class);
            } else {
                $this->handleModuleMigrate($project, $module, $_conn);
                if ($this->run) {
                    $this->count == 0 ?
                        $this->line("Module [$module] Nothing to migrate.") :
                        $this->info("Module [$module] Migrate done!");
                }
            }
        }
    }

    /**
     * @param $project
     * @param $module
     * @param $conn
     * @param $print
     * @param $class
     * @throws ReflectionException
     */
    protected function handleClassMigrate($project, $module, $conn, $print, $class)
    {
        $full_class = $this->getNamespace($this->baseNamespace, $project, $module) . '\\' . $class;
        $file = $this->getFileName($full_class);
        $ite = isset($this->migrations[$file]) ? $this->migrations[$file]->iteration : 0;
        if ($print) $ite = 0;
        $Class = new $full_class($this->database, $conn, $ite);
        $this->migrateClass($Class, $file, $class);
        if (!$print && $this->count == 0)
            $this->line("[$class] Has been migrate...");
    }

    protected function handleModuleMigrate($project, $module, $conn)
    {
        $except = $this->option('except') ?: null;
        foreach (scandir($this->getDir($this->baseDir, $project, $module)) as $migration) {
            if (in_array($migration, ['.', '..'])) continue;
            $name = str_replace('.php', '', $migration);
            $class = $this->convertUnderline(substr($name, 18));
            if ($except && $class == $except) continue;
            $full_class = $this->getNamespace($this->baseNamespace, $project, $module) . '\\' . $class;
            $ite = isset($this->migrations[$name]) ? $this->migrations[$name]->iteration : 0;
            $Class = new $full_class($this->database, $conn, $ite);
            $this->migrateClass($Class, $name, $class);
        }
    }

    protected function modifyConn($project, $conn)
    {
        $this->database = $this->getDatabase($project, $conn);
        $this->modifyDatabaseConfig($project, $conn);
    }

    protected function prepareMigrationsTable()
    {
        $this->createMigrationsTable();
        $this->migrations = DB::table('migrations')->get()->keyBy('migration')->toArray();
        $this->batch = DB::table('migrations')->max('batch') + 1;
    }

    protected function createMigrationsTable()
    {
        if (!empty(DB::select("Show tables like 'migrations'"))) return true;
        $schema = "CREATE TABLE IF NOT EXISTS `migrations` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT, `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, `iteration` TINYINT(4) UNSIGNED NOT NULL DEFAULT 1, `batch` int(11) NOT NULL, `record` text, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        return DB::select($schema);
    }

    /**
     * @param MigrationBaseModel $class
     * @param $name
     * @param $_class
     */
    protected function migrateClass(MigrationBaseModel $class, $name, $_class)
    {
        $class->run();
        if (!$this->run) {
            list($output, $ite) = $class->prepare();
            if (isset($this->migrations[$name]) &&
                $this->migrations[$name]->iteration >= $ite
                && !$this->option('print'))
                return;
            $this->comment($name . ' [' . $_class . '] Sql: ');
            echo $output;
            return;
        }
        $ite = $class->build();
        if (isset($this->migrations[$name]) &&
            $this->migrations[$name]->iteration >= $ite)
            return;
        $this->recordMigrate($name, $ite);
        $this->info($name . ' Migrate.');
        $this->count++;
    }

    protected function recordMigrate($class, $ite)
    {
        $append = $ite . ',' . date('Ymd_His') . ';';
        if (isset($this->migrations[$class])) {
            DB::table('migrations')->where('migration', $class)->update(['iteration' => $ite, 'record' => DB::raw("concat(IFNULL(record,''),'$append')")]);
        } else {
            DB::table('migrations')->insert(['migration' => $class, 'iteration' => $ite, 'batch' => $this->batch, 'record' => $append]);
        }
    }

}
