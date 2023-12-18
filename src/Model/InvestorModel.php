<?php

declare(strict_types=1);

namespace App\Model;

use Pimple\Psr11\Container;
use App\Helper\General;

/**
 * PinjamanModel class
 */
final class InvestorModel extends BaseModel
{
    public function __construct(Container $container) {
        $this->container    = $container;

        $this->general      = new General($container);

        parent::__construct();
    }

    public function buildQueryPengajuan($params=null) {
        $getQuery = $this->db()->table('request_pinjaman');
        $getQuery->select($getQuery->raw('request_pinjaman.*, peminjam.name as peminjam_name, peminjam.npm as peminjam_npm, peminjam.phone as peminjam_phone'));
        $getQuery->leftJoin('peminjam', 'investor.id', '=', 'request_pinjaman.id_peminjam');
        $getQuery->where('request_pinjaman.stattus_approval', 'wait');
        $getQuery->groupBy('request_pinjaman.id');

        if(!empty($params['keywords'])) {
            $keywords = $params['keywords'];
            $getQuery->where('peminjam.name', 'LIKE', "%$keywords%");
        } if (!empty($params['sort'])) {
            $tmpSort = explode('###', $params['sort']);
            
            $getQuery->orderBy($tmpSort[0], $tmpSort[1]);
        } if (empty($params['sort'])) {
            $getQuery->orderBy('request_pinjaman.deadline', 'asc');
        }

        return $getQuery;
    }

    public function listPengajuan($params=null) {
        $getQuery = $this->buildQueryPengajuan($params);
        
        $totalData = $getQuery->count();

        if (!empty($params['page'])) {
            $page = $params['page'] == 1 ? $params['page'] - 1 : ($params['page'] * $params['limit']) - $params['limit'];

            $getQuery->limit((int) $params['limit']);
            $getQuery->offset((int) $page);
        }
        
        $list = $getQuery->get();
        return ['data' => $list, 'total' => $totalData];
    }

    public function buildQueryPiutang($params=null) {
        $getQuery = $this->db()->table('request_pinjaman');
        $getQuery->select($getQuery->raw('request_pinjaman.*, peminjam.name as peminjam_name, peminjam.npm as peminjam_npm, peminjam.phone as peminjam_phone, peminjam.major as peminjam_jurusan, peminjam.class as peminjam_kelas'));
        $getQuery->leftJoin('transaction', 'request_pinjaman.id', '=', 'transaction.id_request_pinjaman');
        $getQuery->leftJoin('peminjam', 'investor.id', '=', 'request_pinjaman.id_peminjam');
        $getQuery->where('request_pinjaman.stattus_approval', 'approve');
        $getQuery->where('transaction.id_investor', $params['id_investor']);
        $getQuery->groupBy('request_pinjaman.id');

        if(!empty($params['keywords'])) {
            $keywords = $params['keywords'];
            $getQuery->where('peminjam.name', 'LIKE', "%$keywords%");
        } if (!empty($params['sort'])) {
            $tmpSort = explode('###', $params['sort']);
            
            $getQuery->orderBy($tmpSort[0], $tmpSort[1]);
        } if (empty($params['sort'])) {
            $getQuery->orderBy('request_pinjaman.deadline', 'asc');
        }

        return $getQuery;
    }

    public function listPiutang($params=null) {
        $getQuery = $this->buildQueryPiutang($params);
        
        $totalData = $getQuery->count();

        if (!empty($params['page'])) {
            $page = $params['page'] == 1 ? $params['page'] - 1 : ($params['page'] * $params['limit']) - $params['limit'];

            $getQuery->limit((int) $params['limit']);
            $getQuery->offset((int) $page);
        }
        
        $list = $getQuery->get();
        return ['data' => $list, 'total' => $totalData];
    }

    public function buildQueryListRiwayatPinjaman($params) {
        $getQuery = $this->db()->table('request_pinjaman');
        $getQuery->select($getQuery->raw('request_pinjaman.*, peminjam.name as peminjam_name, peminjam.npm as peminjam_npm, peminjam.phone as peminjam_phone, peminjam.major as peminjam_jurusan, peminjam.class as peminjam_kelas'));
        $getQuery->leftJoin('transaction', 'request_pinjaman.id', '=', 'transaction.id_request_pinjaman');
        $getQuery->leftJoin('peminjam', 'investor.id', '=', 'request_pinjaman.id_peminjam');
        $getQuery->where('transaction.id_investor', $params['id_investor']);
        $getQuery->groupBy('request_pinjaman.id');

        if(!empty($params['keywords'])) {
            $keywords = $params['keywords'];
            $getQuery->where('peminjam.name', 'LIKE', "%$keywords%");
        } if (!empty($params['sort'])) {
            $tmpSort = explode('###', $params['sort']);
            
            $getQuery->orderBy($tmpSort[0], $tmpSort[1]);
        } if (empty($params['sort'])) {
            $getQuery->orderBy('request_pinjaman.deadline', 'asc');
        }

        return $getQuery;
    }

    public function listRiwayatPembayaran($params=null) {
        $getQuery = $this->buildQueryListRiwayatPinjaman($params);
        
        $totalData = $getQuery->count();

        if (!empty($params['page'])) {
            $page = $params['page'] == 1 ? $params['page'] - 1 : ($params['page'] * $params['limit']) - $params['limit'];

            $getQuery->limit((int) $params['limit']);
            $getQuery->offset((int) $page);
        }
        
        $list = $getQuery->get();
        return ['data' => $list, 'total' => $totalData];
    }

    public function detailPinjaman($id) {
        $numberString = ['', 'Pertama', 'Kedua', 'Ketiga', 'Keempat', 'Kelima', 'Keenam', 'Ketujuh',
                        'Kedelapan', 'Kesembilan', 'Kesepuluh', 'Kesebelas', 'Keduabelas'];
        $result = $this->db()->table('request_pinjaman')
                ->where('id', $id)->first();

        return $result;
    }

    public function checkSaldo($investor, $table) {
        return $this->db()->table($table)->where('id_investor', $investor)->first();
    }

    public function updateSaldo($investor, $table, $data) {
        return $this->db()->table($table)->where('id_investor', $investor)->update($data);
    }

    public function buildQueryHistoryTopUp($params) {
        $getQuery = $this->db()->table('transaction')
                            ->select($this->db()->raw('transaction.*, payment_method.bank_name, payment_method.account_number'))
                            ->join('payment_method', 'payment_method.id', '=', 'transaction.payment_method_id')
                            ->whereNull('transaction.id_request_pinjaman')
                            ->where('transaction.id_investor', $params['id_investor'])
                            ->where('transaction.siklus', 'masuk')
                            ->orderBy('transaction.created_at', 'desc');
        return $getQuery;
    }
    
    public function listHistoryTopUp($params=null) {
        $getQuery = $this->buildQueryHistoryTopUp($params);
        
        $totalData = $getQuery->count();

        if (!empty($params['page'])) {
            $page = $params['page'] == 1 ? $params['page'] - 1 : ($params['page'] * $params['limit']) - $params['limit'];

            $getQuery->limit((int) $params['limit']);
            $getQuery->offset((int) $page);
        }

        $list = $getQuery->get();

        foreach ($list as $item) {
            $date = new \DateTime($item->created_at);
            $item->deadline = $date->modify('+1 day')->format('Y-m-d H:i');
            $item->expired = (strtotime($item->created_at) - time()) <= 0;
        }

        return ['data' => $list, 'total' => $totalData];
    }

}
