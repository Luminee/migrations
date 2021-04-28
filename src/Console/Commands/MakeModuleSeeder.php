<?php

namespace Luminee\Migrations\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

/**
 * @author LuminEe
 */
class MakeModuleSeeder extends Command
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
    protected $signature = 'make:module:seeder {project} {module} {seeder}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make Seeder to Module Direction';

    /**
     * Create a new command instance.
     *
     * @param $file Filesystem
     * @return void
     */
    public function __construct(Filesystem $file)
    {
        $this->files = $file;
        $this->stubDir = realpath(__DIR__ . '/../Stub');
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
        $project = $this->argument('project');
        $module = $this->argument('module');
        $dir = $this->baseDir . '/' . $project . '/' . $module;
        if (!is_dir($dir)) {
            $this->error("Project [$project] or Module [$module] folder doesn't exist! Please create and try again.");
        } else {
            $this->createSeeder(ucfirst($project), ucfirst($module), $dir);
        }
    }

    /**
     * @param $Project
     * @param $Module
     * @param $path
     * @throws FileNotFoundException
     */
    protected function createSeeder($Project, $Module, $path)
    {
        $seeder = $this->argument('seeder');
        $name = date('Y_m_d_His') . '_' . $seeder;
        $file = $path . '/' . $name . '.php';
        $namespace = $this->baseNamespace . '\\' . $Project . '\\' . $Module;
        $class = $this->convertUnderline($seeder);
        $full_class = $namespace . '\\' . $class;
        if (class_exists($full_class)) {
            $this->error("Class $full_class Has Exist!");
            return;
        }
        $stub = $this->files->get($this->stubDir . '/seeder.stub');
        $stub = str_replace(['{$Namespace}', '{$Class}'], [$namespace, $class], $stub);
        $this->files->put($file, $stub);
        $this->info('Calling [composer -o dump]');
        exec("composer -o dump");
        $this->info("File $name.php Create Success!");
    }

    protected function convertUnderline($str)
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $str)));
    }
}
