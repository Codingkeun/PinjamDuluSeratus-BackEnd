<?php

declare(strict_types=1);

/*
 * Users
 * Author : Cecep Sutisna
*/

namespace App\Controller;

use App\Helper\JsonResponse;
use App\Helper\General;
use App\Model\AuthModel;
use App\Model\GeneralModel;
use Pimple\Psr11\Container;
use App\Model\LogModel;
use App\Model\FileModel;
use App\Model\PinjamanModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class Peminjam
{
    private $container;
    private $auth;

    public function __construct(Container $container)
    {
        $this->container    = $container;
        $this->auth         = new AuthModel($this->container->get('db'));
        $this->generalModel = new GeneralModel($this->container->get('db'));
        $this->general      = new General($container);
        $this->log          = new LogModel($this->container->get('db'));
        $this->file         = new FileModel($this->container->get('db'));
        $this->pinjaman     = new PinjamanModel($this->container);
        $this->user         = $this->auth->validateToken();
    }

    public function statisticDashboard(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $result = ['status' => false, 'message' => 'Data tidak ditemukan', 'data' => array()];
        $params['user_id'] = $this->user->id;
        $params['sort'] = 'request_pinjaman.created_at###asc';
        $list   = $this->pinjaman->list($params);

        if (!empty($list['data'])) {
            $statisticLoanMonth = [];
            foreach ($list['data'] as $trx) {
                $date = date('Y-m-d', strtotime($trx->created_at));

                if (!isset($statisticLoanMonth[$date]))
                    $statisticLoanMonth[$date] = 0;
                $statisticLoanMonth[$date] += $trx->nominal;
            }

            $result = ['status' => true, 'message' => 'Data ditemukan', 'data' => [
                'statistic_loan_month' => array_values($statisticLoanMonth)
            ]];
        }
        return JsonResponse::withJson($response, $result, 200);
    }

    public function listPinjamanaAktif(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $result = ['status' => false, 'message' => 'Data tidak ditemukan', 'data' => array()];
        $params['user_id'] = $this->user->id;
        $list   = $this->pinjaman->list($params);

        if (!empty($list['data'])) {
            $result = ['status' => true, 'message' => 'Data ditemukan', 'data' => $list['data']];
        }

        $result['pagination'] = [
            'page' => (int) $params['page'],
            'prev' => $params['page'] > 1,
            'next' => ($list['total'] - ($params['page'] * $params['limit'])) > 0,
            'total' => $list['total']
        ];
        return JsonResponse::withJson($response, $result, 200);
    }

    public function detail(Request $request, Response $response, $parameters): Response
    {
        $result = ['status' => false, 'message' => 'Data tidak ditemukan'];
        $detail = (array) $this->pinjaman->detail($parameters['id']);

        if (!empty($detail)) {
            $result = ['status' => true, 'message' => 'Data ditemukan', 'data' => $detail];
        }
        return JsonResponse::withJson($response, $result, 200);
    }

    public function riwayatPinjaman(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $result = ['status' => false, 'message' => 'Data tidak ditemukan', 'data' => array()];
        $params['user_id'] = $this->user->id;
        $list   = $this->pinjaman->listRiwayatPembayaran($params);

        if (!empty($list['data'])) {
            $result = ['status' => true, 'message' => 'Data ditemukan', 'data' => $list['data']];
        }

        $result['pagination'] = [
            'page' => (int) $params['page'],
            'prev' => $params['page'] > 1,
            'next' => ($list['total'] - ($params['page'] * $params['limit'])) > 0,
            'total' => $list['total']
        ];
        return JsonResponse::withJson($response, $result, 200);
    }

    public function ajukanPinjaman(Request $request, Response $response): Response
    {
        $result         = array('status' => false, 'message' => 'Data gagal disimpan');
        
        $post                 = $request->getParsedBody();        
        $data['id_peminjam']  = $this->user->id;
        $data['nominal']  = isset($post["nominal"]) ? $post["nominal"] :'';
        $data['tip']      = isset($post["tip"]) ? $post["tip"] :'';
        $data['instalment_total']  = isset($post["jml_cicilan"]) ? $post["jml_cicilan"] :'';
        $data['bank_name']         = isset($post["bank_name"]) ? $post["bank_name"] :'';
        $data['account_number']    = isset($post["account_number"]) ? $post["account_number"] :'';

        // SIMULASI CICILAN
        $simNilaiCicilan  = ceil(($post['nominal']+$post['tip'])/$post['jml_cicilan']);

        $data['instalment_nominal']  = $simNilaiCicilan;
        $data['instalment_status']   = 'belum';
        $data['status_approval']     = 'wait';
        $data['deadline']            = date('Y-m-d H:i:s', strtotime('+'.$post['jml_cicilan']. ' month', strtotime(date("Y-m-d H:i:s"))));
        $data['created_at']          = date('Y-m-d H:i:s');
        $idPinjaman = $this->generalModel->insert("request_pinjaman", $data);

        for($i=1;$i<=$post['jml_cicilan'];$i++){
            $param['id_request_pinjaman'] = $idPinjaman;
            $param['date']                = date('Y-m-d H:i:s', strtotime('+'.$i. ' month', strtotime(date("Y-m-d H:i:s"))));
            $param['nominal']             = $simNilaiCicilan;
            $param['status']              = 'belum';
            $param['created_at']          = date('Y-m-d H:i:s');
            $this->generalModel->insert("request_pinjaman_cicilan", $param);
        }
        // SIMULASI CICILAN

        if($idPinjaman){
            $result['status']  = true;
            $result['message'] = 'Pengajuan Berhasil di Proses';
        }
        
        return JsonResponse:: withJson($response, $result, 200);

    }

    public function payment(Request $request, Response $response): Response
    {
        $result         = array('status' => false, 'message' => 'Pembayaran gagal dilakukan');
        
        $post              = $request->getParsedBody();

        $detailTrx = $this->generalModel->fetchBy($post['trx_id'], 'request_pinjaman_cicilan');

        if (!empty($detailTrx)) {
            $allowSubmit = true;
            $data   = [
                'status' => 'lunas',
                'date_payment' => $post['date'],
                'payment_method_id' => $post['payment_method_id']
            ];
    
            if (isset($_FILES['attachment']) && $_FILES['attachment']['size'] != 0) {
                $targetFolder   = "/payment/" . $this->user->role;
                $validateFile   = $this->file->validateFile('attachment', $targetFolder, true);
    
                if ($validateFile['status']) {
                    $allowed_extension = array('png','jpg');
                    if (in_array($validateFile['extension'], $allowed_extension)) {
                        $uploadedFiles  = $request->getUploadedFiles();
                        $uploadedFile   = $uploadedFiles['attachment'];
    
                        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                            $filename       = $this->file->moveUploadedFile($uploadedFile, 'attachment');
                            $url_upload     = $this->general->baseUrl($filename);
    
                            $data["attachment"]    = $url_upload;
                        }
                    } else {
                        $allowSubmit = false;
                        $result['error']        = "File yang diupload harus gambar (.png / .jpg)";
                    }
                }
            }
    
            if ($allowSubmit) {
                $data['updated_at'] = date('Y-m-d H:i:s');
                $prosesData = $this->generalModel->update($post["trx_id"], 'request_pinjaman_cicilan', $data);
                if($prosesData){
                    $result['status']  = true;
                    $result['message'] = 'Pembayaran berhasil dilakukan';
                    
                    $currUnpaid = $this->pinjaman->getListTrxUnpaid($detailTrx->id_request_pinjaman);
                    // flag the parent data if all tenor is paid
                    if (empty($currUnpaid)) {
                        $this->generalModel->update($detailTrx->id_request_pinjaman, 'request_pinjaman', ['instalment_status' => 'lunas']);

                        // insert to transaction when paid
                        $getTransaction = $this->pinjaman->fetchWhere(['id_request_pinjaman' => $detailTrx->id_request_pinjaman, 'siklus' => 'keluar', 'status' => 'success'], 'transaction', 'WHERE', 'FIRST');
                        if (!empty($getTransaction)) {
                            $dataTrx = [
                                'id_investor' => $getTransaction->id_investor,
                                'id_request_pinjaman' => $detailTrx->id_request_pinjaman,
                                'nominal' => $getTransaction->nominal,
                                'siklus' => 'masuk',
                                'description' => 'Pelunasan dana pinjaman',
                                'status' => 'success',
                                'payment_method_id' => $post['payment_method_id'],
                                'attachment' => $data["attachment"],
                                'date_payment' => $post['date'],
                                'created_at' => date('Y-m-d H:i:s')
                            ];
                            $processInsertTrx = $this->pinjaman->insert('transaction', $dataTrx);

                            if ($processInsertTrx) {
                                // add balance when loan is all paid
                                $saldoCheck = $this->pinjaman->fetchWhere(['id_investor' => $getTransaction->id_investor], 'saldo_investor', 'WHERE', 'FIRST');
                                if ($saldoCheck) {
                                    $updateSaldo['nominal'] = $saldoCheck->nominal +  $getTransaction->nominal;
                                    $updateSaldo['updated_at'] = date('Y-m-d H:i:s');
                                    $this->generalModel->update($saldoCheck->id, 'saldo_investor', $updateSaldo);
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return JsonResponse:: withJson($response, $result, 200);

    }
}