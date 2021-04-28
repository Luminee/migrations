<?php

namespace Luminee\Migrations\Repositories;

use Illuminate\Database\Eloquent\Relations\Relation;

trait HasTrait
{
    /**
     * @param $relation
     * @param $field
     * @return $this
     */
    public function whereHasNull($relation, $field)
    {
        $field = $this->getTableField($field);
        $this->_model = $this->_model->whereHas($relation,
            function ($query) use ($field) {
                $query->whereNull($field);
            });
        return $this;
    }

    /**
     * @param $relation
     * @param $field
     * @return $this
     */
    public function whereHasNotNull($relation, $field)
    {
        $field = $this->getTableField($field);
        $this->_model = $this->_model->whereHas($relation,
            function ($query) use ($field) {
                $query->whereNotNull($field);
            });
        return $this;
    }

    /**
     * @param $relation
     * @param $field
     * @return $this
     */
    public function whereHasEmpty($relation, $field)
    {
        $field = $this->getTableField($field);
        $this->_model = $this->_model->whereHas($relation,
            function ($query) use ($field) {
                $query->whereRaw("($field is NULL or $field = '')");
            });
        return $this;
    }

    /**
     * @param $relation
     * @param $field
     * @return $this
     */
    public function whereHasNotEmpty($relation, $field)
    {
        $field = $this->getTableField($field);
        $this->_model = $this->_model->whereHas($relation,
            function ($query) use ($field) {
                $query->whereRaw("($field is not NULL and $field <> '')");
            });
        return $this;
    }


    /**
     * @param $relation
     * @param null $count
     * @param string $ope
     * @return $this
     */
    public function hasRelation($relation, $count = null, $ope = '=')
    {
        $this->_model = is_null($count) ? $this->_model->has($relation) :
            $this->_model->has($relation, $ope, $count);
        return $this;
    }

    /**
     * @param $relation
     * @param int $count
     * @param string $ope
     * @return $this
     */
    public function hasRelationMorph($relation, $count = 1, $ope = '>=')
    {
        $this->_model = $this->_model->hasRelationMorph($relation, $count, $ope);
        return $this;
    }

    /**
     * @param $relation
     * @param $field
     * @param $value
     * @param string $ope
     * @return $this
     */
    public function whereHas($relation, $field, $value, $ope = '=')
    {
        $this->_model = $this->_model->whereHas($relation,
            function ($query) use ($field, $value, $ope) {
                $query->where($field, $ope, $value);
            });
        return $this;
    }

    /**
     * @param $relation
     * @param $field
     * @param $value
     * @param string $ope
     * @return $this
     */
    public function whereHasMorph($relation, $field, $value, $ope = '=')
    {
        $this->_model = $this->_model->whereHasMorph($relation, $field, $value, $ope);
        return $this;
    }

    /**
     * @param $relation
     * @param $field
     * @param $value_array
     * @return $this
     */
    public function whereHasIn($relation, $field, $value_array)
    {
        $this->_model = $this->_model->whereHas($relation,
            function ($query) use ($field, $value_array) {
                $query->whereIn($field, $value_array);
            });
        return $this;
    }

    /**
     * @param $relation
     * @param $field
     * @param $value_array
     * @return $this
     */
    public function whereHasNotIn($relation, $field, $value_array)
    {
        $this->_model = $this->_model->whereHas($relation,
            function ($query) use ($field, $value_array) {
                $query->whereNotIn($field, $value_array);
            });
        return $this;
    }

    /**
     * @param $relation
     * @param $field
     * @param $between
     * @return $this
     */
    public function whereHasBetween($relation, $field, $between)
    {
        $this->_model = $this->_model->whereHas($relation,
            function ($query) use ($field, $between) {
                $query->whereBetween($field, $between);
            });
        return $this;
    }

    /**
     * @param $relation
     * @param $key
     * @param $between
     * @return $this
     */
    public function whereHasKeyBetween($relation, $key, $between)
    {
        $this->_model = $this->_model->whereHas($relation,
            function ($query) use ($key, $between) {
                $query->where('key', $key)->whereBetween('value', $between);
            });
        return $this;
    }

    /**
     * @param $relation
     * @param $key
     * @param $value
     * @param string $ope
     * @return $this
     */
    public function whereHasKeyValue($relation, $key, $value, $ope = '=')
    {
        $this->_model = $this->_model->whereHas($relation,
            function ($query) use ($key, $value, $ope) {
                $query->where('key', $key)->where('value', $ope, $value);
            });
        return $this;
    }

    /**
     * @param $relation
     * @param $field
     * @param $value
     * @return $this
     */
    public function whereHasCommaExpressArray($relation, $field, $value)
    {
        $this->_model = $this->_model
            ->whereHas($relation, function ($query) use ($field, $value) {
                $query->whereRaw("FIND_IN_SET('$value',$field)");
            });
        return $this;
    }

    /**
     * @param $relation
     * @param $field
     * @param $value
     * @return $this
     */
    public function whereHasCommaExpressArrayMorph($relation, $field, $value)
    {
        $this->_model = $this->_model->where(function ($query) use ($relation, $field, $value) {
            $morphType = $query->getModel()->$relation()->getMorphType();
            foreach (array_keys(Relation::morphMap()) as $key => $type) {
                $where = $key == 0 ? 'where' : 'orWhere';
                $query->$where(function ($query) use ($relation, $morphType, $type, $field, $value) {
                    $query->where($morphType, $type)->whereHas($type,
                        function ($query) use ($field, $value) {
                            $query->whereRaw("FIND_IN_SET('$value',$field)");
                        });
                });
            }
        });
        return $this;
    }

    /**
     * @param $relation
     * @param $first
     * @param $array
     * @param $second
     * @param $value
     * @param string $equal
     * @return $this
     */
    public function whereHasInOrWhere($relation, $first, $array, $second, $value, $equal = '=')
    {
        $this->_model = $this->_model->where(
            function ($query) use ($relation, $first, $array, $second, $value, $equal) {
                $query->whereHas($relation, function ($query) use ($first, $array) {
                    $query->whereIn($first, $array);
                })->orWhere($second, $equal, $value);
            });
        return $this;
    }

    /**
     * @param $relation
     * @param $first
     * @param $second
     * @param $third
     * @return $this
     */
    public function whereHasInAndWhere_Or_Where($relation, $first, $second, $third)
    {
        $this->_model = $this->_model->where(
            function ($query) use ($relation, $first, $second, $third) {
                $query->whereHas($relation, function ($query) use ($first, $second) {
                    $query->whereIn($first[0], $first[1]);
                    is_array($second[1]) ? $query->whereIn($second[0], $second[1]) :
                        $query->where($second[0], $second[2] ?? '=', $second[1]);
                });
                is_array($third[1]) ? $query->orWhereIn($third[0], $third[1]) :
                    $query->orWhere($third[0], $third[2] ?? '=', $third[1]);
            });
        return $this;
    }

}