<?php

namespace Luminee\Migrations\Base;

use DB;
use Illuminate\Support\Fluent;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\Grammar;
use Illuminate\Database\Schema\Grammars\MySqlGrammar;

/**
 * Class MigrationBaseModel
 *
 * @method $this increments($column)
 * @method $this bigIncrements($column)
 * @method $this char($column, $length = null)
 * @method $this string($column, $length = null)
 * @method $this text($column)
 * @method $this longText($column)
 * @method $this json($column)
 * @method $this integer($column, $autoIncrement = false, $unsigned = false)
 * @method $this tinyInteger($column, $autoIncrement = false, $unsigned = false)
 * @method $this smallInteger($column, $autoIncrement = false, $unsigned = false)
 * @method $this bigInteger($column, $autoIncrement = false, $unsigned = false)
 * @method $this unsignedInteger($column, $autoIncrement = false)
 * @method $this unsignedBigInteger($column, $autoIncrement = false)
 * @method $this unsignedTinyInteger($column, $autoIncrement = false)
 * @method $this float($column, $total = 8, $places = 2)
 * @method $this decimal($column, $total = 8, $places = 2)
 * @method $this boolean($column)
 * @method $this morphs($name, $indexName = null)
 * @method $this date($column)
 * @method $this dateTime($column, $precision = 0)
 * @method $this time($column, $precision = 0)
 * @method $this timestamp($column, $precision = 0)
 * @method $this softDeletes($column = 'deleted_at', $precision = 0)
 * @method $this rememberToken()
 * @method $this renameColumn($from, $to)
 * @method $this dropColumn($columns)
 *
 * @method $this index($columns, $name = null, $algorithm = null)
 * @method $this primary($columns, $name = null, $algorithm = null)
 * @method $this unique($columns, $name = null, $algorithm = null)
 * @method $this dropIndex($index)
 * @method $this dropUnique($index)
 *
 * @method $this after($column)
 * @method $this change()
 * @method $this autoIncrement()
 * @method $this default($value)
 * @method $this nullable()
 * @method $this unsigned()
 * @method $this useCurrent()
 *
 * @see \Illuminate\Database\Schema\Blueprint
 * @package Migrations
 */
class MigrationBaseModel
{
    protected $database;

    protected $conn;

    protected $iteration;

    protected $localIte = 0;

    /**
     * The database connection instance.
     *
     * @var Connection
     */
    protected $connection;

    /**
     * The schema grammar instance.
     *
     * @var Grammar
     */
    protected $grammar;

    /**
     * @var Blueprint
     */
    protected $blueprint;

    /**
     * @var Fluent
     */
    protected $column;

    /**
     * @var array
     */
    protected $statements = [];

    /**
     * @var bool
     */
    protected $need_to_sql = false;

    /**
     * @var bool
     */
    protected $updateAddition = false;

    protected $strIndex = false;

    private $modify = [];

    private $removeModifyPrimaryKey = false;

    protected $blueprintFuncList = [
        'bigIncrements', 'bigInteger', 'binary', 'boolean', 'char', 'date', 'dateTime', 'dateTimeTz',
        'decimal', 'double', 'enum', 'float', 'geometry', 'geometryCollection', 'increments', 'integer',
        'ipAddress', 'json', 'jsonb', 'lineString', 'longText', 'macAddress', 'mediumIncrements',
        'mediumInteger', 'mediumText', 'morphs', 'multiLineString', 'multiPoint', 'multiPolygon',
        'nullableMorphs', 'nullableTimestamps', 'point', 'polygon', 'rememberToken', 'renameColumn',
        'smallIncrements', 'smallInteger', 'softDeletes', 'softDeletesTz', 'string', 'text', 'time',
        'timeTz', 'timestamp', 'timestampTz', 'timestamps', 'tinyIncrements', 'tinyInteger',
        'unsignedBigInteger', 'unsignedDecimal', 'unsignedInteger', 'unsignedMediumInteger',
        'unsignedSmallInteger', 'unsignedTinyInteger', 'uuid', 'year', 'index', 'unique',
        'primary', 'dropUnique', 'dropIndex', 'dropColumn'
    ];

    protected $fluentFuncList = [
        'after', 'autoIncrement', 'charset', 'collation', 'default', 'first',
        'nullable', 'storedAs', 'unsigned', 'useCurrent', 'change'
    ];

    protected $fluentIgnoreFuncList = [
        'comment'
    ];

    /**
     * MigrationBaseModel constructor.
     * @param $database
     * @param $conn
     * @param $iteration
     */
    public function __construct($database, $conn, $iteration)
    {
        $this->connection = empty(DB::getConnections()) ? DB::connection() :
            DB::getConnections()[DB::getDefaultConnection()];
        $this->grammar = new MySqlGrammar();
        $this->database = $database;
        $this->conn = $conn;
        $this->iteration = $iteration;
    }

    /**
     * Run the database migrations.
     *
     * @return void
     */
    public function run()
    {

    }

    public function build()
    {
        foreach ($this->getStatements() as $statement) {
            $this->connection->statement($statement);
        }
        return $this->localIte;
    }

    public function prepare()
    {
        $output = '';
        foreach ($this->getStatements() as $statement) {
            $output .= '::=> ' . $statement . "\r\n";
        }
        return [$output, $this->localIte];
    }

    public function getSql()
    {
        return $this->getStatements();
    }

    protected function toSql()
    {
        foreach ($this->blueprint->toSql($this->connection, $this->grammar) as $statement) {
            if ($this->strIndex && strpos($statement, 'alter') === 0)
                $statement = preg_replace('/`\(`(\w+)\((\d+)\)`\)/', '`(`$1`($2))', $statement);
            if ($this->updateAddition && strpos($statement, 'alter') === 0)
                $statement .= ', ALGORITHM=INPLACE, LOCK=NONE';
            if (!empty($this->modify) && strpos($statement, 'alter') === 0)
                $statement = $this->modifyStatement($statement);

            $this->statements[] = $statement;
        }
        if ($this->localIte <= $this->iteration) $this->statements = [];
        $this->need_to_sql = false;
    }

    protected function getStatements()
    {
        if ($this->need_to_sql)
            $this->toSql();
        return $this->statements;
    }

    protected function table($table)
    {
        if ($this->need_to_sql)
            $this->toSql();
        $this->need_to_sql = true;
        $this->localIte++;

        $this->modify = [];
        $this->removeModifyPrimaryKey = false;
        $this->blueprint = new Blueprint($table);
        return $this;
    }

    protected function create($table)
    {
        $this->table($table);
        $this->blueprint->create();
        return $this;
    }

    protected function engine($engine = 'InnoDB')
    {
        $this->blueprint->engine = $engine;
        return $this;
    }

    protected function comment($string)
    {
        if ($this->conn == 'dev') {
            $this->column->comment($string);
        }
        return $this;
    }

    protected function t_id($string)
    {
        return $this->integer($string . '_id');
    }

    protected function idx_id($string)
    {
        return $this->index($string . '_id');
    }

    protected function kv_pair($k_null = false, $v_null = false)
    {
        $key = $this->string('key');
        if ($k_null) $key->nullable();

        $value = $this->text('value');
        if ($v_null) $value->nullable();
    }

    protected function sort()
    {
        return $this->decimal('sort', 15, 8);
    }

    protected function timestamps($useCurrent = false, $nullable = true, $on_update = false)
    {
        $created_at = $this->timestamp('created_at');
        if ($useCurrent) $created_at->useCurrent();
        if ($nullable) $created_at->nullable();

        $updated_at = $this->timestamp('updated_at');
        if ($on_update) {
            $updated_at->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            $useCurrent = false;
        }
        if ($useCurrent) $updated_at->useCurrent();
        if ($nullable) $updated_at->nullable();

    }

    protected function updateAddition()
    {
        $this->updateAddition = true;
        return $this;
    }

    protected function strIndex()
    {
        $this->strIndex = true;
        return $this;
    }

    protected function modify($columns)
    {
        is_array($columns) ?
            $this->modify = array_merge($this->modify, $columns) :
            $this->modify[] = $columns;
        return $this;
    }

    protected function removeModifyPrimaryKey()
    {
        $this->removeModifyPrimaryKey = true;
        return $this;
    }

    private function modifyStatement($statement)
    {
        if ($this->removeModifyPrimaryKey)
            $statement = str_replace(' primary key', '', $statement);
        $items = explode('add', $statement);
        foreach ($items as $key => $item) {
            if ($key == 0) continue;
            preg_match('/^ `(\S+)` /', $item, $match);
            $imp = !empty($match[1]) && in_array($match[1], $this->modify) ? 'modify' : 'add';
            $items[$key] = $imp . $item;
        }
        return implode('', $items);
    }

    /**
     * @param $method
     * @param $args
     * @return $this | Blueprint
     */
    public function __call($method, $args)
    {
        if (in_array($method, $this->blueprintFuncList))
            $this->column = $this->blueprint->$method(...$args);

        if (in_array($method, $this->fluentFuncList)) {
            $this->column->$method(...$args);
        }

        if (in_array($method, $this->fluentIgnoreFuncList) && $this->conn == 'dev')
            $this->column->$method(...$args);

        return $this;
    }
}