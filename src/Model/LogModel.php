<?php
declare(strict_types=1);

/*
 * LogModel
 * Author : Cecep Rokani
*/

namespace App\Model;

final class LogModel {
    protected $database;
    private $authModel;

    protected function db() {
        $pdo = new \Pecee\Pixie\QueryBuilder\QueryBuilderHandler($this->database);
        return $pdo;
    }

    public function __construct(\Pecee\Pixie\Connection $database) {
        $this->database = $database;
        $this->authModel    = new AuthModel($this->database);
    }

    public function create($users_id, $level = 'info', $type = null, $method, $status, $notes, $data) {
        $device_info                        = $this->authModel->getDeviceInfo();
        $insertdata['level']                = $level;
        $insertdata['users_id']             = $users_id;
        $insertdata['ip_address']           = $device_info['ip_address'];
        $insertdata['device']               = $device_info['device'];
        $insertdata['platform']             = $device_info['platform'];
        $insertdata['browser']              = $device_info['browser'];
        $insertdata['version']              = $device_info['version'];
        $insertdata['time']                 = $device_info['time'];
        $insertdata['type']                 = $type;
        $insertdata['method']               = $method;
        $insertdata['status']               = $status;
        $insertdata['notes']                = $notes;
        $insertdata['data']                 = json_encode($data);

        return $this->db()->table('activity_log')->insert($insertdata);
    }
}
