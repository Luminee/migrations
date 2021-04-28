<?php

namespace Luminee\Migrations\Console\Commands;

use DB;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Luminee\Migrations\Foundations\PdoBuilder;

/**
 * @author LuminEe
 */
class MakeModuleModel extends Command
{
    use PdoBuilder;

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
    protected $signature = 'make:module:model {project} {module} {table} {--wd}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make Model to Module Direction';

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
        $this->baseDir = config('migrations.models.dir');
        $this->baseNamespace = config('migrations.models.namespace');
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
            DB::setPdo($this->getPdo($project, 'dev'));
            $db = $this->getDatabase($project, 'dev');
            $this->createModel(ucfirst($project), $db, $dir);
        }
    }

    /**
     * @param $Project
     * @param $db
     * @param $path
     */
    protected function createModel($Project, $db, $path)
    {
        $table = $this->argument('table');
        $info = DB::table('information_schema.tables')->where('table_schema', $db)
            ->where('table_name', $table)->select('table_comment')->first();
        if (empty($comment = $info->table_comment)) {
            $this->error("Table $table have not build comment!");
            return;
        }
        $items = $this->getItems($comment);
        $model = $items['model'];
        $file = $path . '/' . $model . '.php';
        $namespace = $this->baseNamespace . '\\' . $Project . '\\' . $items['space'];
        $full_class = $namespace . '\\' . $model;
        if (class_exists($full_class)) {
            $this->error("Class $full_class Has Exist!");
            return;
        }

        $stub = "<?php\r\n\r\nnamespace {$namespace};\r\n\r\n";
        $stub .= "use Illuminate\Database\Eloquent\Model;\r\n";
        if ($items['soft'] == 'true')
            $stub .= "use Illuminate\Database\Eloquent\SoftDeletes;\r\n";
        $stub .= "\r\nClass {$model} extends Model\r\n{\r\n";
        if ($items['soft'] == 'true')
            $stub .= "    use SoftDeletes;\r\n    protected \$dates = ['deleted_at'];\r\n\r\n";
        $stub .= "    protected \$table = '{$table}';\r\n\r\n";
        if ($items['times'] == 'false')
            $stub .= "    public \$timestamps = false;\r\n\r\n";
        if (isset($items['relation'])) {
            foreach ($items['relation'] as $r) {
                if ($r['func'] == 'morphTo') continue;
                $stub .= "    public function {$r['name']}()\r\n    {\r\n        return";
                $class = str_replace('App\Models', $this->baseNamespace . '\\' . $Project, $r['class']);
                $stub .= " \$this->{$r['func']}('{$class}', '{$r['fK']}', '{$r['lK']}');\r\n    }\r\n\r\n";
            }
        }
        $this->files->put($file, $stub . '}');
        $this->info('Calling [composer -o dump]');
        if (!$this->option('wd')) exec("composer -o dump");
        $this->info("File $model.php Create Success!");
    }

    protected function getItems($comment)
    {
        list($_, $m) = explode('||', $comment);
        $items = [];
        foreach (explode('&', $m) as $item) {
            list($k, $v) = explode(':', $item);
            $this->buildModelItems($items, $k, $v);
        }
        return $items;
    }

    protected function buildModelItems(&$items, $k, $v)
    {
        switch ($k) {
            case 'NS':
                return $items['space'] = $v;
            case 'M':
                return $items['model'] = $v;
            case 'MN':
                return $items['mName'] = $v;
            case 'R':
                return $items['relation'][] = $this->getRelation($v);
            case 'SD':
                return $items['soft'] = $v;
            case 'TS':
                return $items['times'] = $v;
            default:
                dd($k, $v);
        }
    }

    protected function getRelation($value)
    {
        list($name, $v) = explode('->', $value);
        list($func, $class, $fK, $lK) = explode(',', $v);
        if (strstr($func, 'mp['))
            return $this->buildMorphRelation($value);
        $func = $this->transFunc($func);
        $class = $this->transClass($class);
        return ['name' => $name, 'func' => $func, 'class' => $class, 'fK' => $fK, 'lK' => $lK];
    }

    protected function transFunc($func)
    {
        switch ($func) {
            case 'bt':
                return 'belongsTo';
            case 'hm':
                return 'hasMany';
            case 'ho':
                return 'hasOne';
        }
        return $func;
    }

    protected function transClass($class)
    {
        return 'App\Models\\' . str_replace('+', '\\', $class);
    }

    protected function buildMorphRelation($value)
    {
        list($name, $_) = explode('->mp[', $value);
        list($map, $_) = explode(']', $_);
        $maps = [];
        foreach (explode(';', $map) as $item) {
            list($k, $v) = explode(',', $item);
            $maps[$k] = explode('+', $v);
        }
        list($n, $t, $i) = explode(',', $_);
        return ['name' => $name, 'func' => 'morphTo', 'maps' => $maps,
            'mn' => $n, 'mt' => $t, 'mi' => $i];
    }
}
