<?php

namespace Luminee\Migrations\Repositories;

use DB;
use Illuminate\Database\Eloquent\Builder;

class _BaseRepository
{
    use WithTrait, JoinTrait, WhereTrait, HasTrait, OtherTrait, GetTrait, DMLTrait;

    protected $_project;

    protected $_module;

    protected $_model;

    /**
     * @param $project
     * @return $this
     */
    public function bindProject($project)
    {
        $this->_project = $project;
        return $this;
    }

    /**
     * @param $module
     * @return $this
     */
    public function bindModule($module)
    {
        $this->_module = $module;
        return $this;
    }

    /**
     * @noinspection PhpIncludeInspection
     * @param $model
     * @return mixed
     */
    protected function bindModel($model)
    {
        $_models = include database_path('models') . '/' . $this->_project
            . '/' . $this->_module . '/_models.php';
        $Model = $_models[$model];
        app()->singleton($Model, function () use ($Model) {
            return new $Model;
        });
        return app($Model);
    }

    /**
     * @return mixed
     */
    protected function getModel()
    {
        return $this->_model;
    }

    /**
     * @param $model_field
     * @return string
     */
    protected function getTableField($model_field)
    {
        if (strpos($model_field, ':') === false)
            return $model_field;
        if (count($ex_field = explode('.', $model_field)) != 2)
            return $model_field;
        $table = $this->structureModel($ex_field[0])->getTable();
        return $table . '.' . $ex_field[1];
    }

    /**
     * @param $model_name
     * @return mixed
     */
    protected function structureModel($model_name)
    {
        $string = explode(':', $model_name);
        $class = get_class($this->get_model());
        $str_arr = explode('\\', $class);
        if (count($string) == 1) {
            $str_arr[3] = ucfirst($string[0]);
        } else {
            $str_arr[2] = ucfirst($string[0]);
            $str_arr[3] = ucfirst($string[1]);
        }
        $class = implode('\\', $str_arr);
        return new $class;
    }

    /**
     * @return mixed
     */
    protected function get_model()
    {
        if ($this->_model instanceof Builder)
            return $this->_model->getModel();
        return $this->_model;
    }

    /**
     * @param $model
     * @return $this
     */
    public function setModel($model)
    {
        $this->_model = $this->bindModel($model);
        return $this;
    }

    /**
     * @param $query
     * @param $alias
     * @return $this
     */
    public function setSubTable($query, $alias)
    {
        $model = DB::table(DB::raw("({$query->toSql()}) as $alias"));
        $this->_model = $model->addBinding($query->getBindings());
        return $this;
    }

    public function toSql()
    {
        return $this->_model->toSql();
    }

    public function getBindings()
    {
        return $this->_model->getBindings();
    }

    public function mergeBindings($query)
    {
        $this->_model->mergeBindings($query);
        return $this;
    }

    public function addBindings($value, $type = 'where')
    {
        $this->_model->addBinding($value, $type);
    }

    /**
     * @param $data
     * @param string $format
     * @return array
     */
    public function success($data, $format = 'model')
    {
        $array = ['status' => 1, 'format' => $format, 'data' => $data];
        if ($format == 'pagination')
            return array_merge($array, ['_total' => $data->_total]);
        return $array;
    }

    /**
     * @param $message
     * @return array
     */
    public function error($message)
    {
        return ['status' => 0, 'message' => $message];
    }

}