<?php

namespace Luminee\Migrations\Repositories;

trait WithTrait
{
    /**
     * @param $relation
     * @return $this
     */
    public function withRelated($relation)
    {
        if (!empty($relation))
            $this->_model = $this->_model->with($relation);
        return $this;
    }

    /**
     * @param $relation
     * @param array $columns
     * @return $this
     */
    public function withCertain($relation, array $columns)
    {
        if (empty($relation))
            return $this;
        $this->_model = $this->_model->withCertain($relation, $columns);
        return $this;
    }

    /**
     * @param $relation
     * @param $field
     * @param $value
     * @param string $ope
     * @return $this
     */
    public function withRelatedWhere($relation, $field, $value, $ope = '=')
    {
        if (empty($relation))
            return $this;
        $this->_model = $this->_model->with([$relation =>
            function ($query) use ($field, $value, $ope) {
                $value != null ?
                    $query->where($field, $ope, $value) :
                    $query->whereRaw("($field is NULL or $field = '')");
            }]);
        return $this;
    }

    /**
     * @param $relation
     * @param $field
     * @param $array
     * @return $this
     */
    public function withRelatedWhereIn($relation, $field, $array)
    {
        if (empty($relation))
            return $this;
        $this->_model = $this->_model->with([$relation =>
            function ($query) use ($field, $array) {
                $query->whereIn($field, $array);
            }]);
        return $this;
    }

    /**
     * @param $relation
     * @param $field
     * @return $this
     */
    public function withRelatedWhereNotNull($relation, $field)
    {
        if (empty($relation))
            return $this;
        $this->_model = $this->_model->with([$relation =>
            function ($query) use ($field) {
                $query->whereNotNull($field);
            }]);
        return $this;
    }

    /**
     * @param $relation
     * @return $this
     */
    public function withRelationTrashed($relation)
    {
        $this->_model = $this->_model->with([$relation =>
            function ($query) {
                $query->withTrashed();
            }]);
        return $this;
    }

    /**
     * @param $relations
     * @return $this
     */
    public function withRelationsTrashed($relations)
    {
        foreach ($relations as $relation) {
            $this->_model = $this->_model->with([$relation =>
                function ($query) {
                    $query->withTrashed();
                }]);
        }
        return $this;
    }

    /**
     * @param $relation
     * @return $this
     */
    public function withRelationOnlyTrashed($relation)
    {
        $this->_model = $this->_model->with([$relation =>
            function ($query) {
                $query->onlyTrashed();
            }]);
        return $this;
    }

    /**
     * @return $this
     */
    public function withTrashed()
    {
        $this->_model = $this->_model->withTrashed();
        return $this;
    }

    /**
     * @return $this
     */
    public function onlyTrashed()
    {
        $this->_model = $this->_model->onlyTrashed();
        return $this;
    }

    /**
     * @param $relation
     * @return $this
     */
    public function withRelatedMaybeWhere($relation)
    {
        if (empty($relation))
            return $this;
        if (!is_array($relation)) {
            $this->_model = $this->_model->with($relation);
            return $this;
        }
        foreach ($relation as $item) {
            !is_array($item) ? $this->_model = $this->_model->with($item) :
                $this->withRelatedWhere($item[0], $item[1], $item[2], $item[3] ?? '=');
        }
        return $this;
    }


    /**
     * @param $relation
     * @param $order_by
     * @param string $sort
     * @return $this
     */
    public function withRelatedOrderBy($relation, $order_by, $sort = 'asc')
    {
        if (empty($relation))
            return $this;
        $this->_model = $this->_model->with([$relation =>
            function ($query) use ($order_by, $sort) {
                $query->orderBy($order_by, $sort);
            }]);
        return $this;
    }

}