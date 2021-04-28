<?php

namespace Luminee\Migrations\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class MakeModuleMigration extends Command
{
    /**
     * @var Filesystem $files
     */
    protected $files;

    /**
     * @var string
     */
    protected $baseDir;

    protected $baseNamespace;

    protected $stubDir;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:module:migration {project} {module} {migration} {--table=} {--o|optimize}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make Migration to Project Module Direction';

    /**
     * Create a new command instance.
     *
     * @param Filesystem $file
     * @return void
     */
    public function __construct(Filesystem $file)
    {
        $this->files = $file;
        $this->stubDir = realpath(__DIR__ . '/../Stub');
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
        $project = $this->argument('project');
        $module = $this->argument('module');
        $dir = $this->baseDir . '/' . $project . '/' . $module;
        if (!is_dir($dir)) {
            $this->error("Project [$project] or Module [$module] folder doesn't exist! Please create and try again.");
        } else {
            $this->createMigration(ucfirst($project), ucfirst($module), $dir);
        }
    }

    /**
     * @param $Project
     * @param $Module
     * @param $path
     * @throws FileNotFoundException
     */
    protected function createMigration($Project, $Module, $path)
    {
        $migration = $this->argument('migration');
        $name = date('Y_m_d_His') . '_' . $migration;
        $file = $path . '/' . $name . '.php';
        $namespace = $this->baseNamespace . '\\' . $Project . '\\' . $Module;
        $class = $this->camelStr($migration);
        $full_class = $namespace . '\\' . $class;
        if (class_exists($full_class)) {
            $this->error("Class $full_class Has Exist!");
            return;
        }

        list($type, $table) = $this->getTable($migration);
        $sub_file = $type == 'create' ? 'migration' : 'migrationU';
        $stub = $this->files->get($this->stubDir . "/$sub_file.stub");
        $search = ['{$Namespace}', '{$Class}', '{$Table}'];
        $replace = [$namespace, $class, $table];
        $stub = str_replace($search, $replace, $stub);
        $this->files->put($file, $stub);
        if ($this->option('optimize'))
            exec("composer -o dump");
        $this->info("File $name.php Create Success!");
    }

    protected function getTable($migration)
    {
        $table = $type = false;
        if (preg_match('/^create_(\w+)_table$/', $migration, $matches)) {
            $table = $matches[1];
            $type = 'create';
        }
        if (preg_match('/^update_(\w+)_table/', $migration, $matches)) {
            $table = $matches[1];
            $type = 'table';
        }
        if ($this->option('table')) {
            $table = $this->option('table');
        }
        return [$type ?: 'table', $table ?: ''];
    }

    protected function camelStr($str)
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $str)));
    }
}
