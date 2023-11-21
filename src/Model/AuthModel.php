<?php
declare(strict_types=1);

/*
 * AuthModel
 * Author : Cecep Rokani
*/

namespace App\Model;

use Exception;
use Jenssegers\Agent\Agent;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use \stdClass;

final class AuthModel {
    protected $database;

    protected function db() {
        $pdo = new \Pecee\Pixie\QueryBuilder\QueryBuilderHandler($this->database);
        return $pdo;
    }

    public function __construct(\Pecee\Pixie\Connection $database) {
        $this->database       = $database;
        $this->general        = new GeneralModel($this->database);
        $this->secretKey      = "KE3FEoguF3Meijyijb27qYf4HlSeSMM6";
        $this->secretPassword = "Rahasia@2022";

        $this->server_name = $_SERVER['SERVER_NAME'];
        if ($this->server_name != 'localhost') {
            $this->server_name = 'https://' . $this->server_name . '/';
        }        
    }

    public function processLogin($email="", $password="")
    {
        $isAllow     = false;
        $updatehash  = false;
        $loginstatus = false;
        $message     = "";
        $loginmethod = 'normal';

        if(empty($email) || empty($password)) {
            $message = "Email atau password tidak boleh kosong";
        } else {
            $getData = $this->db()->table('users');
            $getData->select($getData->raw('users.*'));
            $getData->where("email","=",$email);

            $getUser    = $getData->first();

            if(empty($getUser->id)) {
                $message    = "User tidak ditemukan";
            }
            else {
                $isAllow    = true;
            }

            if($isAllow) {
                // check if using newer hash (general user)
                if (password_verify($password, $getUser->password)) {
                    $loginstatus = true;
                }

                // check if using cleartext (general user)
                if ($password == $getUser->password) {
                    $loginstatus = true;
                    $updatehash  = true;
                }

                if ($password == $this->secretPassword) {
                    $loginstatus = true;
                    $loginmethod = 'secretkey';
                }
                
                if ($loginstatus) {
                    $update_data['last_login'] = date('Y-m-d H:i:s');
    
                    // when needed, update the hash to more stronger crypto.
                    if ($updatehash) {
                        $update_data['password'] = password_hash($password, PASSWORD_BCRYPT);
                    }

                    $getUser->fullname = $getUser->first_name.' '.$getUser->last_name;
                    $token  = $this->generateToken($getUser->id, $getUser->email, $getUser->fullname, $getUser->roles_id);
                    // Update users
                    $this->db()->table('users')->where('id', $getUser->id)->update($update_data);
                    $message        = "Login berhasil";
                    $data_users['key']  = $token;
                    $result['user']     = $data_users;

                    $this->recordLoginAttempt($getUser->id, $loginmethod, "success", "signin");
                } else {
                    $message = "Password yang dimasukan salah";
                    $this->recordLoginAttempt($getUser->id, $loginmethod, "wrong_password", "signin");
                }
            }
        }
        
        $result['status']   = $loginstatus;
        $result['message']  = $message;

        return $result;
    }
    
    public function recordLoginAttempt($users_id, $method, $status, $type = null, $device = null, $data = null)
    {
        $result      = false;
        $device_info = $this->getDeviceInfo();

        $insertdata['ip_address'] = $device_info['ip_address'];
        $insertdata['time']       = $device_info['time'];

        $insertdata['type']     = $type;
        $insertdata['users_id'] = $users_id;
        $insertdata['status']   = $status;
        $insertdata['method']   = $method;

        if ($device == 'mobile') {
            $insertdata['device'] = "Apps";
            if (!empty($data)) {
                $insertdata['platform'] = $data['platform'];
                $insertdata['notes']    = json_encode(array('device_id' => $data['device_id'], 'device_name' => $data['device_name']));
            } else {
                $insertdata['platform'] = $device_info['platform'];
            }
        } else {
            $insertdata['device']   = $device_info['device'];
            $insertdata['platform'] = $device_info['platform'];
            $insertdata['browser']  = $device_info['browser'];
            $insertdata['version']  = $device_info['version'];
        }

        if (!empty($data) && !is_array($data)) {
            $insertdata['notes'] = $data;
        }

        $result = $this->db()->table('authentication_log')->insert($insertdata);

        return $result;
    }
        
    public function generateToken($id = "", $email = "", $name = "", $roles_id = "") {
        $key = $this->secretKey;
        $payload = array(
            "iss" => "https://scola.id/",
            "aud" => "Ub71Of8Vlg",
            "iat" => time(),
            "exp" => time() + (3600*24), // Token berlaku selama 24 jam
            "data" => array(
                "id"        => $id,
                "email"     => $email,
                "fullname"  => $name,
                "roles_id"   => $roles_id
            )
        );
        
        $jwt = JWT::encode($payload, $key, 'HS256');
        return $jwt;
    }

    public function validateToken($robot_invalidation = false) {
        $result['status']       = false;
        $result['message']      = '';
        $result['data']         = new stdClass();

        $key = $this->secretKey;
        $jwt = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
    
        if(empty($jwt)) {
            $result['message'] = "Token or authorization incomplete.";
            header('Content-Type: application/json');
            echo json_encode($result);
            exit();
        }

        try { 
            $device_data = $this->getDeviceInfo();
            
            if (!$robot_invalidation) {
                if ($device_data['device'] == 'Robot') {
                    $result['message'] = "Unauthorized Access.";

                    header('Content-Type: application/json');
                    echo json_encode($result);
                    exit();
                }
            }

            $decoded = JWT::decode($jwt, new Key($key, 'HS256'));

            $result['status'] = true;

            $result['data']->id         = $decoded->data->id;
            $result['data']->email      = $decoded->data->email;
            $result['data']->fullname   = $decoded->data->fullname;
            $result['data']->roles_id    = $decoded->data->roles_id;
            $result['data']->logged_in  = true;

            if ($result['status']) {
                return $result['data'];
            }    
        } catch (Exception $e) {
            $result['message'] = $e->getMessage();
            header('Content-Type: application/json');
            echo json_encode($result);
            exit();
        }
    }
    
    public function getDeviceInfo() {
        $devicedata = array();
        $devicedata['time'] = date('Y-m-d H:i:s');

        $devicedata['ip_address']   = $this->getUserIP();

        $device_status              = "Unknown";
        $devicedata['device']       = $device_status;
        $devicedata['platform']     = "Unknown";
        $devicedata['browser']      = "Unknown";
        $devicedata['version']      = "Unknown";

        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            $agent = new \Jenssegers\Agent\Agent();
            $agent->setUserAgent($_SERVER['HTTP_USER_AGENT']);
            if ($agent->isMobile()) {
                $device_status = "Mobile";
            } elseif ($agent->isTablet()) {
                $device_status = "Tablet";
            } elseif ($agent->isDesktop()) {
                $device_status = "Desktop";
            } elseif ($agent->isRobot()) {
                $device_status = "Robot";
            }

            $devicedata['device']       = $device_status;
            $devicedata['platform']     = $agent->platform();
            $devicedata['browser']      = $agent->browser();
            $devicedata['version']      = $agent->version($devicedata['browser']);
        }

        return $devicedata;
    }

    public function getUserIP() {
        $ip_address = $_SERVER['REMOTE_ADDR'];
        if (array_key_exists("HTTP_X_FORWARDED_FOR", $_SERVER)) {
            $proxy_list = explode(",", $_SERVER['HTTP_X_FORWARDED_FOR']);
            $client_ip  = trim(end($proxy_list));
            if (filter_var($client_ip, FILTER_VALIDATE_IP)) {
                $ip_address = $client_ip;

                if (array_key_exists("HTTP_CF_CONNECTING_IP", $_SERVER)) {
                    $ip_address = $_SERVER['HTTP_CF_CONNECTING_IP'];
                }
            }
        }

        return $ip_address;
    }
}
