<?php

namespace Luminee\Migrations\Foundations;

use DB;

trait DBBuilder
{
    /**
     * @param $table
     * @param array $data
     * @return mixed
     */
    public function batchUpdate($table, $data = array())
    {
        if (empty($data))
            return false;

        $col = array_keys($data[0]);
        $idC = array_shift($col);

        $query = "UPDATE `{$table}`";
        $set = $whereIn = [];
        foreach ($col as $k) {
            $case = [];
            foreach ($data as $item) {
                $case[] = "WHEN {$idC} = {$item[$idC]} THEN '{$item[$k]} '";
            }
            $set[] = "{$k} = CASE " . implode(' ', $case) . " ELSE {$k} END";
        }
        foreach ($data as $item) {
            $whereIn[] = "'{$item[$idC]}'";
        }
        $query .= " SET " . implode(', ', $set) . " WHERE {$idC} IN(" . implode(',', $whereIn) . ")";
        return DB::update(DB::raw($query));
    }
}