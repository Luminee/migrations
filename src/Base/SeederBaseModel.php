<?php

namespace Luminee\Migrations\Base;

use DB;

class SeederBaseModel
{
    public $table;

    public $timestamp = null;

    protected $timeFields = ['created_at', 'updated_at'];

    public $database;

    public $conn;

    public function __construct($database, $conn)
    {
        $this->database = $database;
        $this->conn = $conn;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

    }

    /**
     * @param array $attributes
     * @param array | null $fields = null
     * @return int
     */
    public function firstIdOrCreate(array $attributes, $fields = null)
    {
        if (!is_null($record = DB::table($this->table)->where(is_null($fields) ? $attributes : $fields)->first(['id']))) {
            return $record->id;
        }
        return $this->create($attributes);
    }

    public function count()
    {
        return DB::table($this->table)->count();
    }

    public function isEmpty()
    {
        return $this->count() == 0;
    }

    public function insert($values)
    {
        return DB::table($this->table)->insert($values);
    }

    public function create($attributes)
    {
        $attributes = $this->checkColumns($attributes);
        if (!$res = DB::table($this->table)->insertGetId($attributes)) die(new \Exception('Insert Error!'));
        return $res;
    }

    public function checkTimestamp()
    {
        if (is_null($this->timestamp)) {
            $columns = \DB::table("information_schema.COLUMNS")->where('table_schema', $this->database)->where('table_name', $this->table)->pluck('column_name')->toArray();
            $this->timestamp = count(array_diff($this->timeFields, $columns)) == 0;
        }
        return $this->timestamp;
    }

    public function checkColumns($attributes)
    {
        $now = date('Y-m-d H:i:s');
        $_columns = [];
        $raw = 'column_name, is_nullable, character_maximum_length, numeric_precision, datetime_precision';
        $columns = \DB::table("information_schema.COLUMNS")->where('table_schema', $this->database)->where('table_name', $this->table)->selectRaw($raw)->get()->toArray();
        foreach ($columns as $column) {
            $name = $column->column_name;
            $_columns[] = $name;
            if ($name == 'id') continue;
            if ($column->is_nullable == 'NO' && !isset($attributes[$name])) {
                if (!is_null($column->character_maximum_length)) $attributes[$name] = '';
                if (!is_null($column->numeric_precision)) $attributes[$name] = 0;
                if (!is_null($column->datetime_precision)) $attributes[$name] = $now;
            }
            if (in_array($name, $this->timeFields) && !isset($attributes[$name]) && $this->checkTimestamp()) {
                $attributes[$name] = $now;
            }
        }
        $diff = array_diff(array_keys($attributes), $_columns);
        if (!empty($diff)) die(new \Exception('Column [' . implode(', ', $diff) . '] not exists!'));
        return $attributes;
    }

}