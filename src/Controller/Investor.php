<?php

declare(strict_types=1);

/*
 * Investor
 * Author: ari nurhuda 
 */

namespace App\Controller;

use App\Helper\JsonResponse;
use App\Helper\General;
use App\Model\AuthModel;
use App\Model\PinjamanModel;
use App\Model\InvestorModel;
use App\Model\GeneralModel;
use Pimple\Psr11\Container;
use App\Model\LogModel;
use App\Model\FileModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class Investor
{
    private $container;
    private $auth;
    
    public function __construct(Container $container)
    {
        $this->container     = $container;
        $this->auth          = new AuthModel($this->container->get('db'));
        $this->generalModel  = new GeneralModel($this->container->get('db'));
        $this->general       = new General($container);
        $this->investor      = new InvestorModel($this->container);
        $this->pinjaman      = new PinjamanModel($this->container);
        $this->log           = new LogModel($this->container->get('db'));
        $this->file          = new FileModel($this->container->get('db'));
        $this->user          = $this->auth->validateToken();
    }

    public function investInLoan(Request $request, Response $response): Response
    {
        $result         = array('status' => false, 'message' => 'Investasi gagal diproses');
        
        $post                = $request->getParsedBody();        
        $data['id_investor'] = $this->user->id;
        $data['loan_id']     = isset($post["loan_id"]) ? $post["loan_id"] : '';
        $data['amount']      = isset($post["amount"]) ? $post["amount"] : '';

        // Data terkait investor lainnya dapat ditambahkan sesuai kebutuhan

        $idInvestment = $this->generalModel->insert("investments", $data);

        if ($idInvestment) {
            $result['status']  = true;
            $result['message'] = 'Investasi Berhasil Diproses';
        }
        
        return JsonResponse::withJson($response, $result, 200);
    }

    public function approvePinjaman(Request $request, Response $response): Response
    {
        $result         = array('status' => false, 'message' => 'Pemberian pinjaman gagal dilakukan', 'must_topup' => false);
        
        $post                 = $request->getParsedBody();  
        $idPinjaman           = $post["id"];
        $detailInvestor       = $this->investor->fetchWhere(['id_user' => $this->user->id], 'investor', 'WHERE', 'FIRST');
        $detailTrx            = $this->investor->fetchById($idPinjaman, 'request_pinjaman');

        if (!empty($detailInvestor)) {
            $data['id_investor']  = $detailInvestor->id;
            $data['id_request_pinjaman']  = $idPinjaman;
            $data['nominal']      = $detailTrx->nominal;
            $data['siklus']       = 'keluar';
            $data['status']       = 'success';
            $data['description']  = 'peminjaman dana';
            $data['attachment']   = isset($files["attachment"]) ? $files["attachment"] :'';

            $currDate = date('Y-m-d H:i:s');
    
            // PENGECEKAN SALDO
            $saldoCheck = $this->investor->checkSaldo($detailInvestor->id, 'saldo_investor');
            if(!empty($saldoCheck) && $saldoCheck->nominal >= $data['nominal']){
                $data['created_at']   = $currDate;
                $prosesData = $this->generalModel->insert("transaction", $data);
                if($prosesData){
                    $updateData['status_approval'] = 'approve';
                    $updateData['updated_at'] = $currDate;
                    $this->generalModel->update($idPinjaman, 'request_pinjaman', $updateData);
    
                    $updateSaldo['nominal'] = $saldoCheck->nominal -  $data['nominal'];
                    $updateSaldo['updated_at'] = $currDate;
                    $this->generalModel->update($saldoCheck->id, 'saldo_investor', $updateSaldo);
    
                    $result['status']  = true;
                    $result['message'] = 'Pengajuan Berhasil di Setujui';
                } else {
                    $result['status']  = false;
                    $result['message'] = 'Pengajuan Gagal di Setujui';
                }
            } else {
                $result['status']  = false;
                $result['message'] = 'Saldo Tidak Cukup, silahkan Topup terlebih dahulu';
                $result['must_topup'] = true;
            }
            // PENGECEKAN SALDO
        }
        
        return JsonResponse:: withJson($response, $result, 200);
    }

    public function listPengajuanAktif(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $result = ['status' => false, 'message' => 'Data tidak ditemukan', 'data' => array()];
        $list   = $this->investor->listPengajuan($params);

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

    public function listPiutangAktif(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $result = ['status' => false, 'message' => 'Data tidak ditemukan', 'data' => array()];
        $params['id_investor'] = $this->user->id;
        $list   = $this->investor->listPiutang($params);

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
        $detail = (array) $this->investor->detailPinjaman($parameters['id']);

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
        $list   = $this->investor->listRiwayatPembayaran($params);

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

    public function topup(Request $request, Response $response): Response
    {
        $result         = array('status' => false, 'message' => 'Topup Saldo gagal dilakukan');
        
        $post              = $request->getParsedBody();

        $dataInvestor = $this->investor->fetchWhere(['id_user' => $this->user->id], 'investor', 'WHERE', 'FIRST');

        if (!empty($dataInvestor)) {
            $saldoCheck = $this->investor->checkSaldo($dataInvestor->id, 'saldo_investor');
            $data   = [
                'id_investor' => $dataInvestor->id,
                'nominal' => $post['nominal'],
                'payment_method_id' => $post['payment_method_id'],
                'siklus' => 'masuk',
                'description' => 'Topup Saldo',
                'status' => 'wait',
                'created_at' => date('Y-m-d H:i:s')
            ];
    
            $prosesData = $this->generalModel->insert('transaction', $data);
            if($prosesData){
                $result         = array('status' => true, 'message' => 'Topup Saldo berhasil dilakukan', 'data' => $prosesData);
            }
        }
        
        return JsonResponse:: withJson($response, $result, 200);
    }

    public function detailTransaction(Request $request, Response $response, $parameters): Response
    {
        $result         = array('status' => false, 'message' => 'Pembayaran gagal dilakukan');

        $detail = $this->investor->fetchById($parameters['id'], 'transaction', 'WHERE', 'FIRST');
        
        if (!empty($detail)) {
            $paymentMethod = $this->investor->fetchById($detail->payment_method_id, 'payment_method', 'WHERE', 'FIRST');
            $detail->bank_name = !empty($paymentMethod) ? $paymentMethod->bank_name : '';
            $detail->payment_method_account_number = !empty($paymentMethod) ? $paymentMethod->account_number : '';
            $detail->time_remaining_in_millisecond = $this->general->millisecsBetween($detail->created_at, date('Y-m-d H:i:s'));
            $result         = array('status' => true, 'message' => '', 'data' => $detail);
        }

        return JsonResponse:: withJson($response, $result, 200);
    }

    public function confirmTopup(Request $request, Response $response): Response
    {
        $result         = array('status' => false, 'message' => 'Konfirmasi pembayaran gagal dilakukan');
        
        $post              = $request->getParsedBody();

        $allowSubmit = true;
        $currDate = date('Y-m-d H:i:s');
        $data   = ['status' => 'success', 'date_payment' => $post['date'], 'updated_at' => $currDate];

        $detailTrx = $this->investor->fetchById($post['trx_id'], 'transaction');

        if (isset($_FILES['attachment']) && $_FILES['attachment']['size'] != 0) {
            $targetFolder   = "/topup";
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
            $saldoCheck = $this->investor->checkSaldo($detailTrx->id_investor, 'saldo_investor');
            $prosesData = $this->generalModel->update($post['trx_id'], 'transaction', $data);
            if($prosesData){
                if (!empty($saldoCheck)) {
                    $dataSaldo   = [
                        'id_investor' => $detailTrx->id_investor,
                        'nominal' => $saldoCheck->nominal + $detailTrx->nominal,
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    $this->investor->updateSaldo($detailTrx->id_investor, 'saldo_investor', $dataSaldo);
                }else{
                    $dataSaldo   = [
                        'id_investor' => $detailTrx->id_investor,
                        'nominal' => $detailTrx->nominal,
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    $this->generalModel->insert('saldo_investor', $dataSaldo);
                }
                $result['status']  = true;
                $result['message'] = 'Saldo berhasil ditambahkan';
            }
        }
        
        return JsonResponse:: withJson($response, $result, 200);

    }

    public function historyTopUp(Request $request, Response $response, $parameters): Response
    {
        $params = $request->getQueryParams();
        $result = ['status' => false, 'message' => 'Data tidak ditemukan'];
        $dataUser = $this->investor->fetchWhere(['id_user' => $this->user->id], 'investor', 'WHERE', 'FIRST');
        $list = [];

        if (!empty($dataUser)) {
            $params['id_investor'] = $dataUser->id;
            $list = $this->investor->listHistoryTopUp($params);
            $result = ['status' => true, 'message' => 'Data ditemukan', 'data' => $list];
        }
        $result['pagination'] = [
            'page' => (int) $params['page'],
            'prev' => $params['page'] > 1,
            'next' => ($list['total'] - ($params['page'] * $params['limit'])) > 0,
            'total' => $list['total']
        ];
        return JsonResponse::withJson($response, $result, 200);
    }

    public function saldo(Request $request, Response $response, $parameters): Response
    {
        $result = ['status' => false, 'message' => 'Data tidak ditemukan'];
        $saldo = $this->investor->checkSaldo($this->user->id, 'saldo_investor');

        if (!empty($detail)) {
            $result = ['status' => true, 'message' => 'Data ditemukan', 'data' => $saldo];
        }
        return JsonResponse::withJson($response, $result, 200);
    }

    public function detailPengajuanPinjaman(Request $request, Response $response, $parameters): Response
    {
        $result = ['status' => false, 'message' => 'Data tidak ditemukan'];
        $detail = (array) $this->pinjaman->detail($parameters['id']);

        if (!empty($detail)) {
            $result = ['status' => true, 'message' => 'Data ditemukan', 'data' => $detail];
        }
        return JsonResponse::withJson($response, $result, 200);
    }
}
