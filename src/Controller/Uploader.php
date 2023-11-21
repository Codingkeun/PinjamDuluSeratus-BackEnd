<?php

declare(strict_types=1);

/*
 * Uploader
 * Author : Cecep Rokani
*/

namespace App\Controller;

use App\Helper\JsonResponse;
use Pimple\Psr11\Container;
use App\Model\GeneralModel;
use App\Model\FileModel;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class Uploader
{
    private $container;
    private $auth;

    public function __construct(Container $container)
    {
        $this->container    = $container;
        $this->general      = new GeneralModel($this->container->get('db'));
        $this->file         = new FileModel($this->container->get('db'));
    }

    public function imagesUpload(Request $request, Response $response): Response
    {
        $result = array();     
        
        if (isset($_FILES['upload']) && $_FILES['upload']['size'] != 0) {
            $targetFolder   = "";
            $validateFile   = $this->file->validateFile('upload', $targetFolder, true);

            if ($validateFile['status']) {
                $allowed_extension = array('png','jpg');
                if (in_array($validateFile['extension'], $allowed_extension)) {
                    $uploadedFiles  = $request->getUploadedFiles();
                    $uploadedFile   = $uploadedFiles['upload'];

                    if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                        $filename       = $this->file->moveUploadedFile($uploadedFile, 'editor-images-upload');
                        $url_upload     = $this->general->baseUrl($filename);

                        $result["fileName"]     = $filename;
                        $result["url"]         = $url_upload;
                        $result["uploaded"]     = 1;    
                    }

                } else {
                    $result['uploaded']     = 0;
                    $result['error']        = "File yang diupload harus gambar (.png / .jpg)";
                }
            }
        } else {
            $result['uploaded']     = 0;
            $result['error']        = "Tidak ada file";
        }
    
        
        return JsonResponse:: withJson($response, $result, 200);

    }
}