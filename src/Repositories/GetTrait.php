<?php

namespace Luminee\Migrations\Repositories;

trait GetTrait
{
    /**
     * @param $id
     * @return mixed
     */
    public function findById($id)
    {
        if (!is_numeric($id))
            return null;
        return $this->_model->find($id);
    }

    /**
     * @param $field
     * @param null $alias
     * @return mixed
     */
    public function listField($field, $alias = null)
    {
        return $this->_model->lists($field, $alias);
    }

    /**
     * @return mixed
     */
    public function getFirst()
    {
        return $this->_model->first();
    }

    /**
     * @param string $columns
     * @return mixed
     */
    public function getCount($columns = '*')
    {
        return $this->_model->count($columns);
    }

    /**
     * @param $columns
     * @return mixed
     */
    public function getSum($columns)
    {
        return $this->_model->sum($columns);
    }

    /**
     * @param $columns
     * @return mixed
     */
    public function getMax($columns)
    {
        return $this->_model->max($columns);
    }

    /**
     * @param $columns
     * @return mixed
     */
    public function getMin($columns)
    {
        return $this->_model->min($columns);
    }

    /**
     * @param $columns
     * @return mixed
     */
    public function getAvg($columns)
    {
        return $this->_model->avg($columns);
    }

    /**
     * @return mixed
     */
    public function getCollection()
    {
        return $this->_model->get();
    }

    /**
     * @param array $fields
     * @return mixed
     */
    public function getCollectionByFields($fields = [])
    {
        return count($fields) ? $this->_model->get($fields) : $this->_model->get();
    }

    /**
     * @param $perPage
     * @param int $nowPage
     * @param string[] $columns
     * @param string $pageName
     * @return mixed
     */
    public function getPagination($perPage, $nowPage = 1, $columns = ['*'], $pageName = 'page')
    {
        $_total = $this->_model->count($columns);
        $paginate = $this->_model->paginate($perPage, $columns, $pageName, $nowPage);
        $paginate->_total = $_total;
        return $paginate;
    }

    /**
     * @param $perPage
     * @param int $nowPage
     * @param string[] $columns
     * @param string $pageName
     * @return mixed
     */
    public function getPaginate($perPage, $nowPage = 1, $columns = ['*'], $pageName = 'page')
    {
        $paginate = $this->_model->paginate($perPage, $columns, $pageName, $nowPage);
        $paginate->_total = $paginate->total();
        return $paginate;
    }

    /**
     * @param self $query
     * @param $params
     * @return array
     */
    public function getCollectionOrPaginate(self $query, $params)
    {
        if (!isset($params['perPage']))
            return $this->success($query->getCollection(), 'collection');
        $pagination = $query->getPaginate($params['perPage'], $params['nowPage'] ?? 1);
        return $this->success($pagination, 'pagination');
    }

    /**
     * @param $data
     * @param $model
     * @return mixed
     */
    public function toModelCollection($data, $model)
    {
        $collection = [];
        foreach ($data as $item) {
            $Model = new $model;
            foreach ($item as $key => $value) {
                $Model->$key = $value;
            }
            $collection[] = $Model;
        }
        return collect($collection);
    }

}