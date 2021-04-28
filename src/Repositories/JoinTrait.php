<?php

namespace Luminee\Migrations\Repositories;

use DB;

trait JoinTrait
{
    /**
     * @param $table
     * @param $one
     * @param $ope
     * @param $two
     * @return $this
     */
    public function innerJoin($table, $one, $ope, $two)
    {
        $this->_model = $this->_model->join($table, $one, $ope, $two);
        return $this;
    }

    /**
     * @param $table
     * @param $one
     * @param $ope
     * @param $two
     * @return $this
     */
    public function leftJoin($table, $one, $ope, $two)
    {
        $this->_model = $this->_model->join($table, $one, $ope, $two, 'left');
        return $this;
    }

    /**
     * @param $table
     * @param array $first_on
     * @param array $second_on
     * @return $this
     */
    public function innerJoinOnAnd($table, array $first_on, array $second_on)
    {
        $this->_model = $this->_model->join($table,
            function ($join) use ($first_on, $second_on) {
                $join->on($first_on[0], $first_on[1], $first_on[2])
                    ->where($second_on[0], $second_on[1], $second_on[2]);
            }, null, null, 'inner');
        return $this;
    }

    /**
     * @param $table
     * @param array $first_on
     * @param array $second_on
     * @return $this
     */
    public function leftJoinOnAnd($table, array $first_on, array $second_on)
    {
        $this->_model = $this->_model->join($table,
            function ($join) use ($first_on, $second_on) {
                $join->on($first_on[0], $first_on[1], $first_on[2])
                    ->where($second_on[0], $second_on[1], $second_on[2]);
            }, null, null, 'left');
        return $this;
    }

    /**
     * inner join 时关联多and, $and 为[$and1,$and2....]
     *
     * @param $table
     * @param array $on
     * @param array $and
     * @return $this
     */
    public function innerJoinOnSomeAnd($table, array $on, array $and)
    {
        $this->_model = $this->_model->join($table,
            function ($join) use ($on, $and) {
                $join->on($on[0], $on[1], $on[2]);
                foreach ($and as $one_and) {
                    $join->where($one_and[0], $one_and[1], $one_and[2]);
                }
            }, null, null, 'inner');
        return $this;
    }

    /**
     * left join 时关联多and, $and 为[$and1,$and2....]
     *
     * @param $table
     * @param array $on
     * @param array $and
     * @return $this
     */
    public function leftJoinOnSomeAnd($table, array $on, array $and)
    {
        $this->_model = $this->_model->join($table,
            function ($join) use ($on, $and) {
                $join->on($on[0], $on[1], $on[2]);
                foreach ($and as $one_and) {
                    $join->where($one_and[0], $one_and[1], $one_and[2]);
                }
            }, null, null, 'left');
        return $this;
    }

    /**
     * @param $table
     * @param array $first_on
     * @param array $second_on
     * @param array $third_on
     * @return $this
     */
    public function innerJoinOnAndAnd($table, array $first_on, array $second_on, array $third_on)
    {
        $this->_model = $this->_model->join($table,
            function ($join) use ($first_on, $second_on, $third_on) {
                $join->on($first_on[0], $first_on[1], $first_on[2])
                    ->where($second_on[0], $second_on[1], DB::raw($second_on[2]))
                    ->where($third_on[0], $third_on[1], DB::raw($third_on[2]));
            }, null, null, 'inner');
        return $this;
    }

    /**
     * @param $model
     * @param $one_column
     * @param string $ope
     * @param string $two_column
     * @param string $type
     * @return $this
     */
    public function joinModel($model, $one_column, $ope = '=', $two_column = 'id', $type = 'inner')
    {
        $one = $this->get_model()->getTable() . '.' . $one_column;
        $table_two = $this->structureModel($model)->getTable();
        $two = $table_two . '.' . $two_column;
        $this->_model = $this->_model->join($table_two, $one, $ope, $two, $type);
        return $this;
    }
}