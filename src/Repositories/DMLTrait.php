<?php

namespace Luminee\Migrations\Repositories;

use DB;
use Exception;

trait DMLTrait
{
    /**
     * @param array $data
     * @return mixed
     */
    public function createEntityWithData(array $data)
    {
        return $this->_model->create($data);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function batchInsert($data)
    {
        return $this->_model->insert($data);
    }

    /**
     * @param $data
     * @return array|null
     */
    public function insertAndGetIds($data)
    {
        if (!$this->_model->insert($data)) return null;
        $last = (int)DB::getPdo()->lastInsertId();
        $ids = [];
        for ($i = 0; $i < count($data); $i++) {
            $ids[] = $last + $i;
        }
        return $ids;
    }

    /**
     * @param $data
     * @return string|null
     */
    public function insertAndGetId($data)
    {
        if (!$this->_model->insert($data))
            return null;
        return DB::getPdo()->lastInsertId();
    }

    /**
     * @param $data
     * @return mixed
     */
    public function firstOrCreate($data)
    {
        return $this->_model->firstOrCreate($data);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function updateAction($data)
    {
        return $this->_model->update($data);
    }

    /**
     * @param $column
     * @param $search
     * @param $replace
     * @return mixed
     */
    public function replaceColumn($column, $search, $replace)
    {
        return $this->_model->update([$column =>
            DB::raw("REPLACE($column, '$search', '$replace')")]);
    }

    /**
     * @param array $multipleData
     * @return false|int
     */
    public function batchUpdate($multipleData = array())
    {
        if (empty($multipleData))
            return false;
        $updateColumn = array_keys($multipleData[0]);
        $referenceColumn = array_shift($updateColumn); //e.g id
        $whereIn = "";

        $q = "UPDATE " . $this->get_model()->getTable() . " SET ";
        foreach ($updateColumn as $uColumn) {
            $q .= $uColumn . " = CASE ";
            foreach ($multipleData as $data) {
                $q .= "WHEN " . $referenceColumn . " = " . $data[$referenceColumn] .
                    " THEN '" . $this->sqlInjectionCheck($data[$uColumn]) . "' ";
            }
            $q .= "ELSE " . $uColumn . " END, ";
        }
        foreach ($multipleData as $data) {
            $whereIn .= "'" . $data[$referenceColumn] . "', ";
        }
        $q = rtrim($q, ", ") . " WHERE " . $referenceColumn . " IN (" . rtrim($whereIn, ', ') . ")";

        // Update
        return DB::update(DB::raw($q));
    }

    /**
     * 防止sql 数据校验  注入问题
     *
     * @param $data
     * @return string|string[]
     */
    public function sqlInjectionCheck($data)
    {
        //单引号转两双单引号
        return str_replace("'", "''", $data);
    }

    /**
     * 字段反撇号 包裹
     *
     * @param array $multipleData
     * @return false|int
     */
    public function batchUpdateStrict($multipleData = array())
    {
        if (empty($multipleData))
            return false;
        $updateColumn = array_keys($multipleData[0]);
        $referenceColumn = array_shift($updateColumn); //e.g id
        $whereIn = "";
        $q = "UPDATE " . $this->get_model()->getTable() . " SET ";
        foreach ($updateColumn as $uColumn) {
            $q .= '`' . $uColumn . '`' . " = CASE ";
            foreach ($multipleData as $data) {
                $q .= "WHEN " . $referenceColumn . " = " . $data[$referenceColumn] .
                    " THEN '" . $data[$uColumn] . "' ";
            }
            $q .= "ELSE `" . $uColumn . "` END, ";
        }
        foreach ($multipleData as $data) {
            $whereIn .= "'" . $data[$referenceColumn] . "', ";
        }
        $q = rtrim($q, ", ") . " WHERE " . $referenceColumn . " IN (" . rtrim($whereIn, ', ') . ")";
        // Update
        return DB::update(DB::raw($q));
    }

    /**
     * 批量更新 ' 号 与 sql 冲突
     *
     * @param array $multipleData
     * @return false|int
     */
    public function batchUpdateWithOutEscape($multipleData = array())
    {
        if (empty($multipleData))
            return false;
        $updateColumn = array_keys($multipleData[0]);
        $referenceColumn = array_shift($updateColumn); //e.g id
        $whereIn = "";
        $q = "UPDATE " . $this->get_model()->getTable() . " SET ";
        foreach ($updateColumn as $uColumn) {
            $q .= '`' . $uColumn . '`' . " = CASE ";
            foreach ($multipleData as $data) {
                $q .= "WHEN " . $referenceColumn . " = " . $data[$referenceColumn] .
                    ' THEN "' . $data[$uColumn] . '"';
            }
            $q .= "ELSE `" . $uColumn . "` END, ";
        }
        foreach ($multipleData as $data) {
            $whereIn .= "'" . $data[$referenceColumn] . "', ";
        }
        $q = rtrim($q, ", ") . " WHERE " . $referenceColumn . " IN (" . rtrim($whereIn, ', ') . ")";
        // Update
        return DB::update(DB::raw($q));
    }

    /**
     * @param array $multipleData
     * @param array $fields
     * @return false|int
     */
    public function batchUpdateByFields($multipleData = array(), $fields = [])
    {
        if (empty($multipleData))
            return false;
        $updateColumn = array_keys($multipleData[0]);
        $referenceColumn = array_shift($updateColumn); //e.g id
        $whereIn = "";
        $q = "UPDATE " . $this->get_model()->getTable() . " SET ";
        foreach ($updateColumn as $uColumn) {
            $q .= $uColumn . " = CASE ";
            foreach ($multipleData as $data) {
                $q .= "WHEN " . $referenceColumn . " = " . $data[$referenceColumn] .
                    " THEN '" . $data[$uColumn] . "' ";
            }
            $q .= "ELSE " . $uColumn . " END, ";
        }
        foreach ($multipleData as $data) {
            $whereIn .= "'" . $data[$referenceColumn] . "', ";
        }
        $q = rtrim($q, ", ") . " WHERE " . $referenceColumn . " IN (" . rtrim($whereIn, ', ') . ")";
        foreach ($fields as $field) {
            foreach ($field as $key => $value) {
                $q .= " AND " . $key . "  = '" . $value . "'";
            }
        }
        // Update
        return DB::update(DB::raw($q));
    }


    /**
     * @param $fields
     * @param $id
     * @param int $number
     * @return mixed
     */
    public function increment($fields, $id, $number = 1)
    {
        return $this->_model->where("id", $id)->update(array(
            $fields => DB::raw("{$fields} + {$number}")
        ));
    }

    /**
     * @param $fields
     * @param $id
     * @param int $number
     * @return mixed
     */
    public function reduce($fields, $id, $number = 1)
    {
        return $this->_model->where("id", $id)->update(array(
            $fields => DB::raw("{$fields} - {$number}")
        ));
    }

    /**
     * @param $model_instance
     * @param $field
     * @param int $count
     * @param array $array
     * @return mixed
     * @throws Exception
     */
    public function updateIncrement($model_instance, $field, $count = 1, array $array = [])
    {
        if (empty($model_instance) || !is_object($model_instance))
            throw new Exception('Update Null Error!');
        $model_instance->increment($field, $count, $array);
        return $model_instance;
    }

    /**
     * @param $field
     * @param int $count
     * @param array $array
     * @return mixed
     */
    public function batchIncrement($field, $count = 1, array $array = [])
    {
        return $this->_model->increment($field, $count, $array);
    }

    /**
     * @param $model_instance
     * @param $field
     * @param int $count
     * @param array $array
     * @return mixed
     * @throws Exception
     */
    public function updateDecrement($model_instance, $field, $count = 1, array $array = [])
    {
        if (empty($model_instance) || !is_object($model_instance))
            throw new Exception('Update Null Error!');
        $model_instance->decrement($field, $count, $array);
        return $model_instance;
    }

    /**
     * @param $field
     * @param int $count
     * @param array $array
     * @return mixed
     */
    public function batchDecrement($field, $count = 1, array $array = [])
    {
        return $this->_model->decrement($field, $count, $array);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function batchUpdateByData($data)
    {
        return $this->_model->update($data);
    }

    /**
     * @param $ids
     * @param $data
     * @return int
     */
    public function updateRawByIdsAndData($ids, $data)
    {
        return DB::table($this->_model->getTable())->whereIn('id', $ids)->update($data);
    }

    /**
     * @param $model_instance
     * @param array $data
     * @return mixed
     * @throws Exception
     */
    public function updateEntityByModelInstanceWithData($model_instance, array $data)
    {
        if (empty($model_instance) || !is_object($model_instance))
            throw new Exception('Update Null Error!');
        $model_instance->fill($data)->save();
        return $model_instance;
    }

    /**
     * @param $model_instance
     * @return mixed
     * @throws Exception
     */
    public function refreshUpdated($model_instance)
    {
        if (empty($model_instance) || !is_object($model_instance))
            throw new Exception('Update Null Error!');
        return $model_instance->touch();
    }

    /**
     * @param $model_instance
     * @return mixed
     * @throws Exception
     */
    public function refreshCreated($model_instance)
    {
        if (empty($model_instance) || !is_object($model_instance))
            throw new Exception('Update Null Error!');
        $model_instance->created_at = date('Y-m-d H:i:s');
        $model_instance->save();
        return $model_instance;
    }

    /**
     * @param $id
     * @return bool|null
     */
    public function deleteEntityById($id)
    {
        if (!is_numeric($id))
            return null;
        return (bool)$this->_model->destroy($id);
    }

    /**
     * @param $ids
     * @return bool|null
     */
    public function deleteEntityByIds($ids)
    {
        if (!is_array($ids))
            return null;
        return (bool)$this->_model->destroy($ids);
    }

    /**
     * @param false $return_count
     * @return bool|int
     */
    public function deleteWhere($return_count = false)
    {
        if (strstr($this->_model->toSql(), ' 0 = 1 ') !== false)
            return 0;
        $delete = $this->_model->delete();
        return $return_count ? $delete : (bool)$delete;
    }

    /**
     * @return bool
     */
    public function forceDeleteWhere()
    {
        return (bool)$this->_model->forceDelete();
    }


    /**
     * @param array $multipleData
     * @return false|int
     */
    public function batchUpdateJsonUnicode($multipleData = array())
    {
        if (empty($multipleData))
            return false;
        $updateColumn = array_keys($multipleData[0]);
        $referenceColumn = array_shift($updateColumn); //e.g id
        $whereIn = "";
        $q = "UPDATE " . $this->get_model()->getTable() . " SET ";
        foreach ($updateColumn as $uColumn) {
            $q .= $uColumn . " = CASE ";
            foreach ($multipleData as $data) {
                $q .= "WHEN " . $referenceColumn . " = " . $data[$referenceColumn] .
                    " THEN '" . $this->sqlInjectionCheck($data[$uColumn]) . "' ";
            }
            $q .= "ELSE " . $uColumn . " END, ";
        }
        foreach ($multipleData as $data) {
            $whereIn .= "'" . $data[$referenceColumn] . "', ";
        }
        $q = rtrim($q, ", ") . " WHERE " . $referenceColumn . " IN (" . rtrim($whereIn, ', ') . ")";
        $q = $this->sqlForJsonUnicode($q);
        // Update
        return DB::update(DB::raw($q));
    }

    /**
     * 修改数据时对json数据转义得处理
     *
     * @param $data
     * @return string|string[]
     */
    public function sqlForJsonUnicode($data)
    {
        //单引号转两双单引号
        return str_replace('\\', '\\\\', $data);
    }

    /**
     * @param array $multipleData
     * @return false|int
     */
    public function batchUpdateAboutNull($multipleData = array())
    {
        if (empty($multipleData))
            return false;
        $updateColumn = array_keys($multipleData[0]);
        $referenceColumn = array_shift($updateColumn); //e.g id
        $whereIn = "";
        $q = "UPDATE " . $this->get_model()->getTable() . " SET ";
        foreach ($updateColumn as $uColumn) {
            $q .= $uColumn . " = CASE ";
            foreach ($multipleData as $data) {
                $q .= "WHEN " . $referenceColumn . " = " . $data[$referenceColumn] . " THEN ";
                $q .= is_null($data[$uColumn]) ? "null " :
                    "'" . $this->sqlInjectionCheck($data[$uColumn]) . "' ";
            }
            $q .= "ELSE " . $uColumn . " END, ";
        }
        foreach ($multipleData as $data) {
            $whereIn .= "'" . $data[$referenceColumn] . "', ";
        }
        $q = rtrim($q, ", ") . " WHERE " . $referenceColumn . " IN (" . rtrim($whereIn, ', ') . ")";
        $q = $this->sqlForJsonUnicode($q);
        // Update
        return DB::update(DB::raw($q));
    }

}