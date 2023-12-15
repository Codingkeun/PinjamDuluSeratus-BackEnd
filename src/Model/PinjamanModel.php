<?php

declare(strict_types=1);

namespace App\Model;

use Pimple\Psr11\Container;
use App\Helper\General;

/**
 * PinjamanModel class
 */
final class PinjamanModel extends BaseModel
{
    public function __construct(Container $container) {
        $this->container    = $container;

        $this->general      = new General($container);

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

    public function buildQueryListRiwayatPinjaman($params) {
        $getQuery = $this->db()->table('request_pinjaman_cicilan')
                            ->where('id_request_pinjaman', $params['pinjaman_id'])
                            ->orderBy('date', 'ASC');

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

    public function detail($id) {
        $numberString = ['', 'Pertama', 'Kedua', 'Ketiga', 'Keempat', 'Kelima', 'Keenam', 'Ketujuh',
                        'Kedelapan', 'Kesembilan', 'Kesepuluh', 'Kesebelas', 'Keduabelas'];
        $result = $this->db()->table('request_pinjaman')
                ->where('id', $id)->first();

        if (!empty($result)) {
            $totalTrxPaid = $this->db()->table('request_pinjaman_cicilan')
                                    ->where('id_request_pinjaman', $id)
                                    ->where('status', '!=', 'belum')
                                    ->orderBy('id', 'asc')
                                    ->count();;
            $trxUnPaid = $this->getListTrxUnpaid($id);
            $getInvestor = $this->db()->table('transaction')
                                    ->select($this->db()->raw('investor.name'))
                                    ->join('investor', 'investor.id', '=', 'transaction.id_investor')
                                    ->first();

            $result->investor = $getInvestor;
            $result->count_payment = $totalTrxPaid + 1;
            $result->trx_unpaid = $trxUnPaid;
            $result->count_payment_string = $numberString[$result->count_payment];
            $result->time_remaining_in_millisecond = $this->general->millisecsBetween($result->deadline, date('Y-m-d H:i:s'));
        }

        return $result;
    }

    public function getListTrxUnpaid($pinjamanId) {
        return $this->db()->table('request_pinjaman_cicilan')
                ->where('id_request_pinjaman', $pinjamanId)
                ->where('status', '=', 'belum')
                ->orderBy('id', 'asc')
                ->first();
    }
}
