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
}
