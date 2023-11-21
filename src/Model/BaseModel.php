<?php

declare(strict_types=1);

/*
 * BaseModel
 * Author : Cecep Rokani
*/

namespace App\Model;

use App\Helper\General;

abstract class BaseModel
{
    protected $builder;
    protected $query;
    protected $container;
    protected $request;
    protected $general;
    
    public function __construct() {
        $this->general = new General($this->container);
    }

    public function db() {
        $builder  = $this->container->get('db')->getQueryBuilder();
        return $builder;
    }

    public function whereSoftDeleteFieldIsNull(\Pecee\Pixie\QueryBuilder\QueryBuilderHandler $build, $alias='') {
        return $build->whereNull($alias ? $alias . '.deleted_at' : 'deleted_at');
    }

    public function getLastQuery(\Pecee\Pixie\QueryBuilder\QueryBuilderHandler $build) {
        $build->get();
        return $build->getLastQuery()->getRawSql();
    }

    public function fetchAll($table) {
        $getQuery = $this->db()->table($table);
        $getQuery = $this->whereSoftDeleteFieldIsNull($getQuery);
        return $getQuery->get();
    }

    public function fetchById($id, $table) {
        $result = $this->db()->table($table)->where('id', $id);
        return $result->first();
    }

    public function fetchWhere($where, $table, $type="WHERE", $result="ALL", $sortBy=[]) {
        $getQuery = $this->db()->table($table);
        if (!empty($where)) {
            foreach ($where as $key=>$item) {
                if ($type == "WHERE")
                    $getQuery->where($key, $item);
                elseif ($type == "IN")
                    $getQuery->whereIn($key, $item);
                elseif ($type == "NOT_IN")
                    $getQuery->whereNotIn($key, $item);
            }
        }
        
        if (!empty($sortBy)) {
            $getQuery->orderBy($sortBy[0], $sortBy[1]);
        }
        if ($result == "FIRST") {
            return $getQuery->first();
        } else if ($result == "COUNT") {
            return $getQuery->count();
        } else {
            return $getQuery->get();
        }
    }

    public function insert($table, $data) {
        return $this->db()->table($table)->insert($data);
    }

    public function update($id, $table, $data) {
        return $this->db()->table($table)->where('id', $id)->update($data);
    }

    public function updateWhere($where=array(), $table, $data) {
        $getQuery = $this->db()->table($table);
        foreach($where as $key=>$value) {
            $getQuery->where($key, $value);
        }
        return $getQuery->update($data);
    }

    public function delete($id, $table) {
        return $this->db()->table($table)->where('id', $id)->delete();
    }

    public function deleteWhere($where, $table) {
        $processDelete = $this->db()->table($table);
        foreach (array_filter($where) as $row) {
            if ($row['type'] == 'NOT_IN') {
                $processDelete->whereNotIn($row['field'], $row['value']);
            } elseif ($row['type'] == 'IN') {
                $processDelete->whereIn($row['field'], $row['value']);
            } elseif ($row['type'] === NULL) {
                $processDelete->whereNull($row['field']);
            } else {
                $processDelete->where($row['field'], $row['value']);
            }
        }

        return $processDelete->delete();
    }
}
