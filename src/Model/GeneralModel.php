<?php
declare(strict_types=1);

/*
 * GeneralModel
 * Author : Cecep Sutisna
*/

namespace App\Model;

 use DateTime;

final class GeneralModel
{
    protected $database;

    protected function db()
    {
        $pdo = new \Pecee\Pixie\QueryBuilder\QueryBuilderHandler($this->database);
        return $pdo;
    }

    public function __construct(\Pecee\Pixie\Connection $database)
    {
        $this->database = $database;
    }

    public function fetchBy($id, $table) {
        return $this->db()->table($table)->where('id', $id)->first();
    }

    public function getById($id, $table)
    {
        return $this->db()->table($table)->where('id', $id)->get();
    }

    public function fetchAll($table) {
        return $this->db()->table($table)->get();
    }

    public function countAll($table) {
        return $this->db()->table($table)->count();
    }

    public function insert($table, $data) {
        return $this->db()->table($table)->insert($data);
    }

    public function update($id, $table, $data) {
        return $this->db()->table($table)->where('id', $id)->update($data);
    }

    public function delete($id, $table) {
        return $this->db()->table($table)->where('id', $id)->delete();
    }

}
