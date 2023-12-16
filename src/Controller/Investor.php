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
        $result         = array('status' => false, 'message' => 'Data gagal disimpan');
        
        $post                 = $request->getParsedBody();  
        $idPinjaman           = $post["id"];

        $data['id_investor']  = $this->user->id;
        $data['id_request_pinjaman']  = $idPinjaman;
        $data['nominal']      = isset($post["nominal"]) ? $post["nominal"] :'';
        $data['siklus']       = 'masuk';
        $data['status']       = 'success';
        $data['description']  = 'peminjaman dana';
        $data['attachment']   = isset($files["attachment"]) ? $files["attachment"] :'';
        $data['created_at']   = date('Y-m-d H:i:s');

        // PENGECEKAN SALDO
        $saldoCheck = $this->investor->checkSaldo($this->user->id, 'saldo_investor');
        if(!empty($saldoCheck) && $saldoCheck->nominal >= $data['nominal']){
            $prosesData = $this->generalModel->insert("transaction", $data);
            if($prosesData){
                $updateData['status_approval'] = 'approve';
                $this->generalModel->update($idPinjaman, 'request_pinjaman', $updateData);

                $updateSaldo['nominal'] = $saldoCheck->nominal -  $data['nominal'];
                $this->generalModel->update($saldoCheck->id, 'saldo_investor', $updateSaldo);

                $result['status']  = true;
                $result['message'] = 'Pengajuan Berhasil di Setujui';
            }else{
                $result['status']  = true;
                $result['message'] = 'Pengajuan Gagal di Setujui';
            }
        }else{
            $result['status']  = false;
            $result['message'] = 'Saldo Tidak Cukup, silahkan Topup';
        }
        // PENGECEKAN SALDO

        
        
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
        $result         = array('status' => false, 'message' => 'Pembayaran gagal dilakukan');
        
        $post              = $request->getParsedBody();

        $saldoCheck = $this->investor->checkSaldo($this->user->id, 'saldo_investor');
        $allowSubmit = true;
        $data   = [
            'id_investor' => $this->user->id,
            'nominal' => $post['nominal'],
            'siklus' => 'masuk',
            'deskripsi' => 'Topup Saldo',
            'status' => 'success',
            'created_at' => date('Y-m-d H:i:s')
        ];

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
            $prosesData = $this->generalModel->insert('transaction', $data);
            if($prosesData){
                if (!empty($saldoCheck)) {
                    $dataSaldo   = [
                        'id_investor' => $this->user->id,
                        'nominal' => $saldoCheck->nominal + $post['nominal'],
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    $this->investor->updateSaldo($this->user->id, 'saldo_investor', $dataSaldo);
                }else{
                    $dataSaldo   = [
                        'id_investor' => $this->user->id,
                        'nominal' => $post['nominal'],
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    $this->generalModel->insert('saldo_investor', $dataSaldo);
                }
                $result['status']  = true;
                $result['message'] = 'Topup Saldo berhasil dilakukan';
            }else{
                $result['status']  = true;
                $result['message'] = 'Topup Saldo gagal dilakukan';
            }
        }
        
        return JsonResponse:: withJson($response, $result, 200);

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
}
