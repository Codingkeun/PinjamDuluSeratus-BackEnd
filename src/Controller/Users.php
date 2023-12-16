<?php

declare(strict_types=1);

/*
 * Users
 * Author : Cecep Rokani
*/

namespace App\Controller;

use App\Helper\JsonResponse;
use App\Helper\General;
use App\Model\AuthModel;
use Pimple\Psr11\Container;
use App\Model\AccountModel;
use App\Model\LogModel;
use App\Model\FileModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class Users
{
    private $container;
    private $auth;

    public function __construct(Container $container)
    {
        $this->container    = $container;
        $this->auth         = new AuthModel($this->container->get('db'));
        $this->general      = new General($container);
        $this->log          = new LogModel($this->container->get('db'));
        $this->file         = new FileModel($this->container->get('db'));
        $this->account      = new AccountModel($this->container);
        $this->user         = $this->auth->validateToken();
    }

    public function info(Request $request, Response $response): Response
    {
        $result                 = array();

        $result['id']           = $this->user->id;
        $result['email']        = $this->user->email;
        $result['fullname']     = $this->user->fullname;
        $result['role']         = $this->user->role;        
        $result['logged_in']    = $this->user->logged_in;
        return JsonResponse:: withJson($response, $result, 200);
    }

    public function menu(Request $request, Response $response): Response
    {
        $result     = array();

        $roles      = $this->user->role;
        $result     = $this->general->menuBar($roles);
        
        return JsonResponse:: withJson($response, $result, 200);
    }

    public function profile(Request $request, Response $response): Response {
        $detail = $this->account->fetchById($this->user->id, 'users');
        $result = ['status' => false, 'message' => 'Data tidak ditemukan!'];

        if (!empty($detail)) {
            $dataUser = $this->account->fetchWhere(['id_user' => $this->user->id], $this->user->role, 'WHERE', 'FIRST');
            if (!empty($dataUser) && $this->user->role == 'investor') {
                $balance = $this->account->fetchWhere(['id_investor' => $dataUser->id], 'saldo_investor', 'WHERE', 'FIRST');
                $dataUser->balance = !empty($balance) ? $balance->nominal : 0;
            }
            $result = ['status' => true, 'message' => 'Data ditemukan', 'data' => $dataUser];
        }
        
        return JsonResponse:: withJson($response, $result, 200);
    }
}