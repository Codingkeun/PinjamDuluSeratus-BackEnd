<?php

declare(strict_types=1);

namespace App\Model;

use App\Helper\General;

/**
 * BaseModel class
 */
abstract class BaseModel
{
    protected $builder;
    protected $query;
    protected $container;
    protected $request;
    protected $prefixTable;
    protected $general;
    
    public function __construct() {
        $this->prefixTable='module_raport_';
        $this->general = new General($this->container);
    }

    public function db($type='READ') {
        switch ($type) {
            case 'WRITE':
                $this->builder  = $this->container->get('db')->getQueryBuilder();
                break;
            default:
                $this->builder  = $this->container->get('db_read')->getQueryBuilder();
                break;
        }
        return $this->builder;
    }

    public function whereSoftDeleteFieldIsNull(\Pecee\Pixie\QueryBuilder\QueryBuilderHandler $build, $alias='') {
        return $build->whereNull($alias ? $alias . '.deleted_at' : 'deleted_at');
    }

    public function getLastQuery(\Pecee\Pixie\QueryBuilder\QueryBuilderHandler $build) {
        $build->get();
        return $build->getLastQuery()->getRawSql();
    }

    public function filterByClient(\Pecee\Pixie\QueryBuilder\QueryBuilderHandler $build, $alias=null) {
        if($this->auth()->parsedToken) {
            $claims = $this->auth()->parsedToken->claims();
            $build->where($alias ? $alias . '.client_id' : 'client_id', $claims->get('client_id'));
            $build->where($alias ? $alias . '.client_secret' : 'client_secret', $claims->get('client_secret'));        
        }
        return $build;
    }

    public function fetchAll($table, $filterByClient=true) {
        $getQuery = $this->db()->table($table);
        $getQuery = $this->whereSoftDeleteFieldIsNull($getQuery);
        if ($filterByClient) {
            $getQuery = $this->filterByClient($getQuery);
        }
        return $getQuery->get();
    }

    public function fetchById($id, $table, $whereNull=false) {
        $result = $this->db()->table($table)->where('id', $id);

        if ($whereNull) {
            $result = $this->whereSoftDeleteFieldIsNull($result);
        }

        return $result->first();
    }

    public function fetchWhere($where, $table, $type="WHERE", $result="ALL", $filterByClient=true, $sortBy=[]) {
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
        $getQuery = $this->whereSoftDeleteFieldIsNull($getQuery);
        if ($filterByClient) {
            $getQuery = $this->filterByClient($getQuery);
        } if (!empty($sortBy)) {
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
        return $this->db('WRITE')->table($table)->insert($data);
    }

    public function update($id, $table, $data) {
        return $this->db('WRITE')->table($table)->where('id', $id)->update($data);
    }

    public function updateWhere($where=array(), $table, $data) {
        $getQuery = $this->db('WRITE')->table($table);
        foreach($where as $key=>$value) {
            $getQuery->where($key, $value);
        }
        return $getQuery->update($data);
    }

    public function delete($id, $table, $mode='SOFT_DELETE') {
        if ($mode == 'SOFT_DELETE') {
            return $this->db('WRITE')->table($table)->where('id', $id)->update(['deleted_at' => date('Y-m-d H:i:s')]);
        } else {
            return $this->db('WRITE')->table($table)->where('id', $id)->delete();
        }
    }

    public function deleteWhere($where, $table, $mode='SOFT_DELETE') {
        $processDelete = $this->db('WRITE')->table($table);
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

        if ($mode == 'SOFT_DELETE') {
            return $processDelete->update(['deleted_at' => date('Y-m-d H:i:s')]);
        } else {
            return $processDelete->delete();
        }
    }

    public function getRaportSetting($field, $resultBoolean=true) {
        $query = $this->db()->table($this->prefixTable . 'settings');
        $query = $this->filterByClient($query, $this->prefixTable . 'settings');
        $query = $this->filterByClient($query, $this->prefixTable . 'settings');
        $query->whereNull($this->prefixTable . 'settings.deleted_at');
        $query->where($this->prefixTable . 'settings.code','=',$field);
        
        $getData = array();
        $getData = $query->first();
        
        $penilaian = 0;

        if(!empty($getData->value)) {
            if ($resultBoolean)
                $penilaian = 1;
            else
                $penilaian = $getData->value;
        }

        return $penilaian;
    }

    protected function auth() {
        return $this->container->get('auth');
    }
}
