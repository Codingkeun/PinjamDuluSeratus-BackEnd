<?php

declare(strict_types=1);

namespace App\Model;

use Pimple\Psr11\Container;

/**
 * PinjamanModel class
 */
final class PinjamanModel extends BaseModel
{
    public function __construct(Container $container) {
        $this->container    = $container;

        parent::__construct();
    }

    public function buildQueryList($params=null) {
        $getQuery = $this->db()->table('request_pinjaman');
        $getQuery->select($getQuery->raw('request_pinjaman.*, investor.name as investor_name'));
        $getQuery->leftJoin('transaction', 'request_pinjaman.id', '=', 'transaction.id_request_pinjaman');
        $getQuery->leftJoin('investor', 'investor.id', '=', 'transaction.id_investor');
        $getQuery->where('request_pinjaman.id_peminjam', $params['user_id']);
        $getQuery->groupBy('request_pinjaman.id');
        $getQuery->orderBy('request_pinjaman.deadline', 'asc');

        if(!empty($params['keywords'])) {
            $keywords = $params['keywords'];
            $getQuery->where('investor.name', 'LIKE', "%$keywords%");
        } if (!empty($params['status'])) {
            $getQuery->where('request_pinjaman.instalment_status', $params['status']);
        }

        return $getQuery;
    }

    public function list($params=null) {
        $getQuery = $this->buildQueryList($params);
        
        $totalData = $getQuery->count();

        if (!empty($params['page'])) {
            $page = $params['page'] == 1 ? $params['page'] - 1 : ($params['page'] * $params['limit']) - $params['limit'];

            $getQuery->limit((int) $params['limit']);
            $getQuery->offset((int) $page);
        }
        
        $list = $getQuery->get();
        return ['data' => $list, 'total' => $totalData];
    }

    public function detail($id) {
        return $this->db()->table('request_pinjaman')
                ->where('id', $id)->first();
    }
}
