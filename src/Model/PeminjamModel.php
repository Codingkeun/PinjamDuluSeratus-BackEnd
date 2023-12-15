<?php

declare(strict_types=1);

/*
 * PeminjamModel
 * Author : Cecep Sutisna
*/

namespace App\Model;

use DateTime;

final class PeminjamModel
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

    public function countAllPengajuan($keywords=null, $idUser=null)
    {
        $query = $this->db()->table("request_pinjaman");
        $query->select('id');
        if (!empty($keywords)) {
            $query->where('name', 'LIKE', '%'.$keywords.'%');
        }
        if (!empty($idUser)) {
            $query->where('id_peminjam', $idUser);
        }
        return $query->count();
    }

    public function fetchAllPengajuan($keywords=null, $limit=null, $page=null, $idUser=null)
    {
        $query =  $this->db()->table("request_pinjaman");
        $query->select('*');
        if (!empty($page)) {
            $query->limit($limit)->offset(($limit * $page) - $limit);
        } if (!empty($keywords)) {
            $query->where('name', 'LIKE', '%'.$keywords.'%');
        }
        if (!empty($idUser)) {
            $query->where('id_peminjam', $idUser);
        }
        return $query->get();
    }
}
