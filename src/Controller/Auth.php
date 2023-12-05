<?php

declare(strict_types=1);

/*
 * Auth
 * Author : Cecep Rokani
*/

namespace App\Controller;

use App\Helper\JsonResponse;
use App\Helper\General;
use App\Model\AuthModel;
use App\Model\GeneralModel;
use Pimple\Psr11\Container;
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
        $this->generalModel = new generalModel($this->container->get('db'));
        $this->general      = new General($container);
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

    public function signup(Request $request, Response $response): Response
    {
        $result         = array('status' => false, 'message' => 'Data gagal disimpan');
        
        $post              = $request->getParsedBody();        
        $users['email']    = isset($post["email"]) ? $post["email"] :'';
        $users['password'] = isset($post["password"]) ? $post["password"] :'';
        $users['role']     = isset($post["role"]) ? $post["role"] :'';
        $idUser = $this->generalModel->insert("users", $users);

        $akun['id_user']        = $idUser;
        $akun['name']           = isset($post["name"]) ? $post["name"] :'';
        $akun['phone']          = isset($post["phone"]) ? $post["phone"] :'';
        $akun['npm']            = isset($post["npm"]) ? $post["npm"] :'';
        $akun['faculty']        = isset($post["faculty"]) ? $post["faculty"] :'';
        $akun['major']          = isset($post["major"]) ? $post["major"] :'';
        $akun['class']          = isset($post["class"]) ? $post["class"] :'';
        $akun['foto_ktm']       = isset($files["foto_ktm"]) ? $files["foto_ktm"] :'';
        $akun['foto_selfie']    = isset($files["foto_selfie"]) ? $files["foto_selfie"] :'';
        $akun['foto_profile']   = isset($files["foto_profile"]) ? $files["foto_profile"] :'';

        $prosesData = $this->generalModel->insert($post["role"], $akun);
        if($prosesData){
            $result['status']  = true;
            $result['message'] = 'Data berhasil disimpan';
        }
        
        return JsonResponse:: withJson($response, $result, 200);

    }
}