<?php
declare(strict_types=1);

/*
 * FileModel
 * Author : Cecep Rokani
*/

namespace App\Model;

use Psr\Http\Message\UploadedFileInterface;

final class FileModel {
    protected $database;

    protected function db() {
        $pdo = new \Pecee\Pixie\QueryBuilder\QueryBuilderHandler($this->database);
        return $pdo;
    }

    public function __construct(\Pecee\Pixie\Connection $database) {
        $this->database     = $database;
        $this->directory    = __DIR__ . '/../../public/assets';
    }

    public function validateFile($source_file, $target_folder, $randomize_filename = false)
    {
        $result['status']     = false;
        $result['file_path']  = "";
        $result['file_name']  = "";
        $result['extension']  = "";

        if (isset($_FILES[$source_file]) && $_FILES[$source_file]['size'] != 0) {
            $result['status']       = true;
            $result['extension']    = strtolower(pathinfo($_FILES[$source_file]["name"], PATHINFO_EXTENSION));

            $randomize_data       = "";

            if ($randomize_filename) {
                $randomize_data = "_" . bin2hex(random_bytes(32));
            }

            $file_name = str_replace(".", '', $_FILES[$source_file]['name']);
            $file_name = preg_replace('/[^A-Za-z0-9\-]/', '', $_FILES[$source_file]['name']) . $randomize_data . '.' . $result['extension'];
            $result['file_path']   = $target_folder . '/' . str_replace(" ", "-", $file_name);
            $result['file_name']   = $file_name;
        }

        return $result;
    }

    function moveUploadedFile(UploadedFileInterface $uploadedFile, $path="")
    {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        
        $dir = $this->directory;
        $permit = 0777;
        
        if(!is_dir($dir)){
            mkdir($dir);
            chmod($dir, $permit);        
        }

        // see http://php.net/manual/en/function.random-bytes.php
        $basename = bin2hex(random_bytes(8));
        $filename = $path.'-'.sprintf('%s.%0.8s', $basename, $extension);
    
        $uploadedFile->moveTo($this->directory . DIRECTORY_SEPARATOR . $filename);
    
        return 'assets/'.$filename;
    }    
}