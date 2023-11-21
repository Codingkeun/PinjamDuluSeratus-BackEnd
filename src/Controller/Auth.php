<?php

declare(strict_types=1);

/*
 * Auth
 * Author : Cecep Rokani
*/

namespace App\Controller;

use App\Helper\JsonResponse;
use App\Model\AuthModel;
use Pimple\Psr11\Container;
use App\Model\GeneralModel;
use App\Model\LogModel;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class Auth
{
    private $container;
    private $auth;

    public function __construct(Container $container)
    {
        $this->container    = $container;
        $this->authModel    = new AuthModel($this->container->get('db'));
        $this->general      = new GeneralModel($this->container->get('db'));
        $this->log          = new LogModel($this->container->get('db'));
    }

    public function signin(Request $request, Response $response): Response
    {
        $result         = array();
        $post           = $request->getParsedBody();        
        $email          = isset($post["email"]) ? $post["email"] :'';
        $password       = isset($post["password"]) ? $post["password"] :'';
        $data           = $this->authModel->processLogin($email, $password);

        $result['status']  = $data['status'];
        $result['message'] = $data['message'];
        $result['user']    = (isset($data['user']) ? $data['user'] : array());
        
        return JsonResponse:: withJson($response, $result, 200);

    }
}