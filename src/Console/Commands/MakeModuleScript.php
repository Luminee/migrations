<?php

namespace Luminee\Migrations\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

/**
 * @author LuminEe
 */
class MakeModuleScript extends Command
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
    protected $signature = 'make:module:script {project} {module} {script}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make Script to Module Direction';

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
        $this->baseDir = config('migrations.scripts.dir');
        $this->baseNamespace = config('migrations.scripts.namespace');
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
            $this->createScript(ucfirst($project), ucfirst($module), $dir);
        }
    }

    /**
     * @param $Project
     * @param $Module
     * @param $path
     * @throws FileNotFoundException
     */
    protected function createScript($Project, $Module, $path)
    {
        $script = str_replace(':', '_', $this->argument('script'));
        $name = date('Y_m_d_His') . '_' . $script;
        $file = $path . '/' . $name . '.php';
        $namespace = $this->baseNamespace . '\\' . $Project . '\\' . $Module;
        $class = $this->convertUnderline($script);
        $full_class = $namespace . '\\' . $class;
        if (class_exists($full_class)) {
            $this->error("Class $full_class Has Exist!");
            return;
        }
        $stub = $this->files->get($this->stubDir. '/script.stub');
        $search = ['{$Namespace}', '{$Class}', '{$sign}'];
        $replace = [$namespace, $class, $this->convertSignature($script)];
        $stub = str_replace($search, $replace, $stub);
        $this->files->put($file, $stub);
        $this->info('Calling [composer -o dump]');
        exec("composer -o dump");
        $this->info("File $name.php Create Success!");
    }

    protected function convertUnderline($str)
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $str)));
    }

    protected function convertSignature($str)
    {
        return str_replace(['-', '_'], ':', $str);
    }
}
