<?php

namespace Luminee\Migrations\Repositories;

trait OtherTrait
{
    /**
     * @return $this
     */
    public function distinct()
    {
        $this->_model = $this->_model->distinct();
        return $this;
    }

    /**
     * @param $select
     * @return $this
     */
    public function select($select)
    {
        if (!is_array($select)) {
            $select = $this->getTableField($select);
        } else {
            foreach ($select as &$field) {
                $field = $this->getTableField($field);
            }
        }
        $this->_model = $this->_model->select($select);
        return $this;
    }

    /**
     * @param $field
     * @return $this
     */
    public function selectDistinct($field)
    {
        $field = $this->getTableField($field);
        $this->_model = $this->_model->selectRaw("distinct ($field)");
        return $this;
    }

    /**
     * @param $query
     * @param array $param
     * @return $this
     */
    public function selectRaw($query, array $param = [])
    {
        $this->_model = $this->_model->selectRaw($query, $param);
        return $this;
    }

    /**
     * @param $field
     * @param $value
     * @param null $equal
     * @return $this
     */
    public function havingField($field, $value, $equal = null)
    {
        $this->_model = $this->_model->having($field, $equal, $value);
        return $this;
    }

    /**
     * @param $field
     * @param $value
     * @param null $equal
     * @return $this
     */
    public function orHavingField($field, $value, $equal = null)
    {
        $this->_model = $this->_model->orHaving($field, $equal, $value);
        return $this;
    }

    /**
     * @param $query
     * @param array $param
     * @return $this
     */
    public function havingRaw($query, array $param = [])
    {
        $this->_model = $this->_model->havingRaw($query, $param);
        return $this;
    }

    /**
     * @param $rows
     * @param int $offset
     * @return $this
     */
    public function limit($rows, $offset = 0)
    {
        $this->_model = $this->_model->skip($offset)->take($rows);
        return $this;
    }

    /**
     * @param $rows
     * @return $this
     */
    public function inRandomOrder($rows)
    {
        $this->_model = $this->_model->orderByRaw('RAND()')->take($rows);
        return $this;
    }


    /**
     * @param $field
     * @param string $sort
     * @return $this
     */
    public function orderBy($field, $sort = 'asc')
    {
        $field = $this->getTableField($field);
        $this->_model = $this->_model->orderBy($field, $sort);
        return $this;
    }

    /**
     * @param $query
     * @param array $param
     * @return $this
     */
    public function orderByRaw($query, array $param = [])
    {
        $this->_model = $this->_model->orderByRaw($query, $param);
        return $this;
    }

    /**
     * @param $field
     * @return $this
     */
    public function groupBy($field)
    {
        $field = $this->getTableField($field);
        $this->_model = $this->_model->groupBy($field);
        return $this;
    }


    /**
     * @param $field
     * @param string $sort
     * @return $this
     */
    public function orderByStringAsInt($field, $sort = 'asc')
    {
        $this->_model = $this->_model->orderByRaw("CAST(`$field` AS DECIMAL) $sort");
        return $this;
    }

    /**
     * @param $field
     * @param $array
     * @param string $sort
     * @return $this
     */
    public function orderByArrayList($field, $array, $sort = 'asc')
    {
        $field = $this->getTableField($field);
        $this->_model = $this->_model->orderByRaw("FIND_IN_SET($field,'$array') $sort");
        return $this;
    }

    /**
     * @param $query
     * @return $this
     */
    public function union($query)
    {
        $this->_model = $this->_model->union($query);
        return $this;
    }

    /**
     * @param $query
     * @param $order_by
     * @return mixed
     */
    public function queryOrderBy($query, $order_by)
    {
        foreach ($order_by as $field => $sort) {
            $query->orderBy($field, $sort);
        }
        return $query;
    }

    /**
     * @param $query
     * @param null $time
     * @param null $time_node
     * @param string $created_at
     * @return mixed
     */
    public function setTimeQuery($query, $time = null, $time_node = null, $created_at = 'created_at')
    {
        if ($time !== null)
            $query = $query->whereBetween($created_at, $time);
        if ($time_node !== null)
            $query = $query->whereField($created_at, $time_node, '<=');
        return $query;
    }

}