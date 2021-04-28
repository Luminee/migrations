<?php

namespace Luminee\Migrations\Repositories;

trait WhereTrait
{
    /**
     * @param $field
     * @param $value
     * @param string $equal
     * @return $this
     */
    public function whereField($field, $value, $equal = '=')
    {
        $field = $this->getTableField($field);
        $this->_model = $this->_model->where($field, $equal, $value);
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @param string $equal
     * @return $this
     */
    public function whereKeyValue($key, $value, $equal = '=')
    {
        $this->_model = $this->_model
            ->where('key', $key)->where('value', $equal, $value);
        return $this;
    }

    /**
     * @param $field
     * @param $value
     * @param string $equal
     * @return $this
     */
    public function orWhereField($field, $value, $equal = '=')
    {
        $field = $this->getTableField($field);
        $this->_model = $this->_model->orWhere($field, $equal, $value);
        return $this;
    }

    /**
     * @param $field
     * @param $value
     * @param $orField
     * @param $orValue
     * @param string $equal
     * @param string $orEqual
     * @return $this
     */
    public function whereOrWhere($field, $value, $orField, $orValue, $equal = '=', $orEqual = '=')
    {
        $this->_model = $this->_model
            ->where(function ($query) use ($field, $equal, $value, $orField, $orEqual, $orValue) {
                $query->where($field, $equal, $value)->orWhere($orField, $orEqual, $orValue);
            });
        return $this;
    }

    /**
     * @param $where_fields
     * @return $this
     */
    public function orWhereOrFields($where_fields)
    {
        $this->_model = $this->_model->orWhere(
            function ($query) use ($where_fields) {
                foreach ($where_fields as $item) {
                    $query->orWhere($item[0], $item[1], $item[2]);
                }
            });
        return $this;
    }


    /**
     * @param $where_fields
     * @return $this
     */
    public function orWhereAndFields($where_fields)
    {
        $this->_model = $this->_model->orWhere(
            function ($query) use ($where_fields) {
                foreach ($where_fields as $item) {
                    $query->where($item[0], $item[1], $item[2]);
                }
            });
        return $this;
    }

    /**
     * @param $where_fields
     * @return $this
     */
    public function andWhereOrFields($where_fields)
    {
        $this->_model = $this->_model->where(
            function ($query) use ($where_fields) {
                foreach ($where_fields as $item) {
                    $query->orWhere($item[0], $item[1], $item[2]);
                }
            });
        return $this;
    }


    /**
     * @param $fields
     * @param $values
     * @param array $equals
     * @return $this
     */
    public function whereOr($fields, $values, $equals = [])
    {
        $this->_model = $this->_model->where(
            function ($query) use ($fields, $values, $equals) {
                foreach ($fields as $key => $item) {
                    $equal = $equals[$key] ?? '=';
                    $key == 0 ? $query->where($fields[$key], $equal, $values[$key]) :
                        $query->orWhere($fields[$key], $equal, $values[$key]);
                }
            });
        return $this;
    }

    /**
     * @param array $whereArray
     * @return $this
     */
    public function whereFields(array $whereArray)
    {
        $this->_model = $this->_model->where($whereArray);
        return $this;
    }

    /**
     * @param $field
     * @param array $valueArray
     * @return $this
     */
    public function whereBetween($field, array $valueArray)
    {
        $field = $this->getTableField($field);
        $this->_model = $this->_model->whereBetween($field, $valueArray);
        return $this;
    }

    /**
     * @param $field
     * @param array $valueArray
     * @return $this
     */
    public function whereNotBetween($field, array $valueArray)
    {
        $field = $this->getTableField($field);
        $this->_model = $this->_model->whereNotBetween($field, $valueArray);
        return $this;
    }

    /**
     * @param $field
     * @param array $valueArray
     * @return $this
     */
    public function whereIn($field, array $valueArray)
    {
        $field = $this->getTableField($field);
        $this->_model = $this->_model->whereIn($field, $valueArray);
        return $this;
    }

    /**
     * @param $field
     * @param array $valueArray
     * @return $this
     */
    public function whereNotIn($field, array $valueArray)
    {
        $field = $this->getTableField($field);
        $this->_model = $this->_model->whereNotIn($field, $valueArray);
        return $this;
    }

    /**
     * @param $query
     * @param array $param
     * @return $this
     */
    public function whereRaw($query, array $param = [])
    {
        $this->_model = $this->_model->whereRaw($query, $param);
        return $this;
    }

    /**
     * @param $field
     * @return $this
     */
    public function whereNull($field)
    {
        $field = $this->getTableField($field);
        $this->_model = $this->_model->whereNull($field);
        return $this;
    }

    /**
     * @param $field
     * @return $this
     */
    public function whereNotNull($field)
    {
        $field = $this->getTableField($field);
        $this->_model = $this->_model->whereNotNull($field);
        return $this;
    }

    /**
     * @param $field
     * @return $this
     */
    public function whereEmpty($field)
    {
        $field = $this->getTableField($field);
        $this->_model = $this->_model->whereRaw("($field is NULL or $field = '')");
        return $this;
    }

    /**
     * @param $field
     * @return $this
     */
    public function whereNotEmpty($field)
    {
        $field = $this->getTableField($field);
        $this->_model = $this->_model->whereRaw("($field is not NULL and $field <> '')");
        return $this;
    }

    /**
     * @param bool $set
     * @return $this
     */
    public function isActive($set = true)
    {
        $this->_model = $this->_model->where('is_active', $set ? 1 : 0);
        return $this;
    }

    /**
     * @param bool $set
     * @return $this
     */
    public function isAvailable($set = true)
    {
        $this->_model = $this->_model->where('is_available', $set ? 1 : 0);
        return $this;
    }

    /**
     * @param bool $set
     * @return $this
     */
    public function joinAvailable($set = true)
    {
        $this->_model = $this->_model->where('join_available', $set ? 1 : 0);
        return $this;
    }


    /**
     * @param $field
     * @param $value
     * @return $this
     */
    public function whereCommaExpressArray($field, $value)
    {
        $this->_model = $this->_model->whereRaw("FIND_IN_SET('$value',$field)");
        return $this;
    }

    /**
     * sql注入处理版
     *
     * @param $field
     * @param $value
     * @return $this
     */
    public function whereCommaExpressArray_bindValue($field, $value)
    {
        $this->_model = $this->_model->whereRaw("FIND_IN_SET(?,$field)");
        $this->addBindings($value);
        return $this;
    }

    /**
     * @param $field
     * @param $value
     * @return $this
     */
    public function whereNotCommaExpressArray($field, $value)
    {
        $this->_model = $this->_model->whereRaw("(NOT FIND_IN_SET('$value',$field) OR $field IS NULL)");
        return $this;
    }

}