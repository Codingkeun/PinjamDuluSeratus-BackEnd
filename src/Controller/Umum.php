<?php

declare(strict_types=1);

/*
 * Donasi
 * Author : Cecep Sutisna
*/

namespace App\Controller;

use App\Helper\JsonResponse;
use App\Helper\General;
use Pimple\Psr11\Container;
use App\Model\LogModel;
use App\Model\GeneralModel;
use App\Model\FileModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class Umum
{
    private $container;
    private $auth;

    public function __construct(Container $container)
    {
        $this->container    = $container;
        $this->general      = new General($container);
        $this->log          = new LogModel($this->container->get('db'));
        $this->generalModel = new generalModel($this->container->get('db'));
        $this->file         = new FileModel($this->container->get('db'));
    }

    public function donasi(Request $request, Response $response): Response
    {
        $result         = array('status' => false, 'message' => 'Data gagal disimpan');
        
        $post           = $request->getParsedBody();

        $data['nominal']        = isset($post["nominal"]) ? $post["nominal"] :'';
        $bank                   = isset($post["bank"]) ? $post["bank"] :'';
        $data['name']           = isset($post["name"]) ? $post["name"] :'';
        $data['email']          = isset($post["email"]) ? $post["email"] :'';
        $data['phone']          = isset($post["phone"]) ? $post["phone"] :'';
        $data['created_at']     = date('Y-m-d H:i:s');
        $data['expired_payment']   = date('Y-m-d H:i:s', strtotime('+24 hour', strtotime(date("Y-m-d H:i:s"))));
        $data['status']         = 'wait';
        $data['siklus']         = 'masuk';

        switch ($bank) {
            case 'BCA':
                $data['description'] = 'BCA | 2233456';
                break;
            case 'BNI':
                $data['description'] = 'BNI | 123123123111';
                break;
            case 'BRI':
                $data['description'] = 'BRI | 1549010101321931120';
                break;
            case 'Mandiri':
                $data['description'] = 'Mandiri | 456312';
                break;
            default:
                // Aksi default jika tidak ada kecocokan dengan nilai BANK
                break;
        }

        $prosesData = $this->generalModel->insert('saldo_sedekah', $data);
        if($prosesData){
            $result['status']  = true;
            $result['expired_payment'] = $data['expired_payment'];
            $result['message']         = 'Silahkan transfer melalui ' .$data['description']. ' dengan nilai Rp. ' . $data['nominal'];
        }
        
        return JsonResponse:: withJson($response, $result, 200);

    }

    public function tunggakan(Request $request, Response $response): Response
    {
        $get            = $request->getQueryParams();
        $keywords       = !empty($get['keywords']) ? $get['keywords'] : null;
        $page           = !empty($get['page']) ? $get['page'] : null;
        $key            = !empty($get['key']) ? $get['key'] : null;
        $totalData      = $this->mastermodel->countAllMasterBidang($keywords, $this->user->role, $this->user->id);
        $totalPerPage   = 10;
        $totalAllPage   = (int) (($totalData - 1) / $totalPerPage + 1);
        $getBidang      = $this->mastermodel->fetchAllMasterBidang($keywords, $totalPerPage, $page, $key, $this->user->role, $this->user->id);
        $data           = json_decode(json_encode($getBidang), TRUE);
        foreach ($data as $row) {
            $tempData['id']               = $row['id'];
            $tempData['user']             = $row['nickname'];
            $tempData['bidang']           = $row['bidang'];
            $tempData['singkatan']        = $row['singkatan'];
            $tempData['kabid_nama']       = $row['kabid_nama'];
            $tempData['kabid_nip']        = $row['kabid_nip'];
            $tempData['kabid_gol']        = $row['kabid_gol'];
            $list    []                   = $tempData;
        }

        if (!empty($list)) {
            $result['status'] = true;
            $result['data']   = $list;
        } else {
            $result['status']  = false;
            $result['data']    = [];
            $result['message'] = 'Data tidak ditemukan';
        }
        $result['total_page']  = $totalAllPage;
        return JsonResponse:: withJson($response, json_encode($result), 200);
    }
}