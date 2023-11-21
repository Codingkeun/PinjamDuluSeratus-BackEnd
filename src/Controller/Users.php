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
        $this->account      = new AccountModel($this->container->get('db'));
        $this->user         = $this->auth->validateToken();
    }

    public function info(Request $request, Response $response): Response
    {
        $result                 = array();

        $result['id']           = $this->user->id;
        $result['email']        = $this->user->email;
        $result['fullname']     = $this->user->fullname;
        $result['roles_id']     = $this->user->roles_id;        
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
}