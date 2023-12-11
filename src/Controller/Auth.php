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
use App\Model\FileModel;

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
        $this->file         = new FileModel($this->container->get('db'));
        $this->log          = new LogModel($this->container->get('db'));
    }

    public function signin(Request $request, Response $response): Response
    {
        $result         = array();
        $post           = $request->getParsedBody();        
        $email          = isset($post["email"]) ? $post["email"] :'';
        $password       = isset($post["password"]) ? $post["password"] :'';
        $role           = isset($post["role"]) ? $post["role"] :'';
        $data           = $this->authModel->processLogin($email, $password, $role);

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
        $akun['foto_selfie']    = isset($files["foto_selfie"]) ? $files["foto_selfie"] :'';
        $akun['foto_profile']   = isset($files["foto_profile"]) ? $files["foto_profile"] :'';

        $allowSubmit = true;

        if (isset($_FILES['foto_ktm']) && $_FILES['foto_ktm']['size'] != 0) {
            $targetFolder   = "/" . $users['role'];
            $validateFile   = $this->file->validateFile('foto_ktm', $targetFolder, true);

            if ($validateFile['status']) {
                $allowed_extension = array('png','jpg');
                if (in_array($validateFile['extension'], $allowed_extension)) {
                    $uploadedFiles  = $request->getUploadedFiles();
                    $uploadedFile   = $uploadedFiles['foto_ktm'];

                    if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                        $filename       = $this->file->moveUploadedFile($uploadedFile, 'foto_ktm');
                        $url_upload     = $this->general->baseUrl($filename);

                        $akun["foto_ktm"]    = $url_upload;
                    }
                } else {
                    $allowSubmit = false;
                    $result['error']        = "File yang diupload harus gambar (.png / .jpg)";
                }
            }
        } if (isset($_FILES['foto_selfie']) && $_FILES['foto_selfie']['size'] != 0) {
            $targetFolder   = "/" . $users['role'];
            $validateFile   = $this->file->validateFile('foto_selfie', $targetFolder, true);

            if ($validateFile['status']) {
                $allowed_extension = array('png','jpg');
                if (in_array($validateFile['extension'], $allowed_extension)) {
                    $uploadedFiles  = $request->getUploadedFiles();
                    $uploadedFile   = $uploadedFiles['foto_selfie'];

                    if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                        $filename       = $this->file->moveUploadedFile($uploadedFile, 'foto_selfie');
                        $url_upload     = $this->general->baseUrl($filename);

                        $akun["foto_selfie"]    = $url_upload;
                    }
                } else {
                    $allowSubmit = false;
                    $result['error']        = "File yang diupload harus gambar (.png / .jpg)";
                }
            }
        } if (isset($_FILES['foto_profile']) && $_FILES['foto_profile']['size'] != 0) {
            $targetFolder   = "/" . $users['role'];
            $validateFile   = $this->file->validateFile('foto_profile', $targetFolder, true);

            if ($validateFile['status']) {
                $allowed_extension = array('png','jpg');
                if (in_array($validateFile['extension'], $allowed_extension)) {
                    $uploadedFiles  = $request->getUploadedFiles();
                    $uploadedFile   = $uploadedFiles['foto_profile'];

                    if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                        $filename       = $this->file->moveUploadedFile($uploadedFile, 'foto_profile');
                        $url_upload     = $this->general->baseUrl($filename);

                        $akun["foto_profile"]    = $url_upload;
                    }
                } else {
                    $allowSubmit = false;
                    $result['error']        = "File yang diupload harus gambar (.png / .jpg)";
                }
            }
        }

        if ($allowSubmit) {
            $prosesData = $this->generalModel->insert($post["role"], $akun);
            if($prosesData){
                $result['status']  = true;
                $result['message'] = 'Data berhasil disimpan';
            }
        }
        
        return JsonResponse:: withJson($response, $result, 200);

    }
}