<?php
declare(strict_types=1);

/*
 * AccountModel
 * Author : Cecep Rokani
*/

namespace App\Model;

use Pimple\Psr11\Container;

final class AccountModel extends BaseModel {
    public function __construct(Container $container) {
        $this->container    = $container;

        parent::__construct();
    }

    public function detail($id="") {
        $getData = $this->db()->table('users');
        $getData->select($getData->raw('users.id, users.username, users.fullname, users.last_login'));
        $getData->where("id","=",$id);

        $getUser    = $getData->first();

        return $getUser;
    }

    public function list($role="", $school_id="", $keywords="",$page=null, $limit=null) {
        $getData = $this->db()->table('users');
        $getData->select(array("users.*"));
        $getData->whereNull("users.softdelete");
        $getData->whereNull("school.softdelete");
        $getData->select(
            $getData->raw("
                users.*,
                school.name as school_name
            ")
        );
        if(!empty($keywords)) {
            $getData->where(function($relation) use ($keywords) {
                $relation->where($relation->raw('lower(users.fullname)'), 'LIKE', '%'.$keywords.'%');
            });
        }

        if (!empty($page)) {
            $limit = (int) $limit;
            $getData->limit($limit)->offset(($limit * $page) - $limit);
        }
        
        $getData->innerJoin("school","school.id","=","users.school_id");
        if(!empty($role)) {
            $getData->where("role", $role);        
        }

        if($role == 'student') {
            if(!empty($school_id)) {
                $getData->where("school_id", $school_id);        
            }
        }

        $result = array();
        foreach($getData->get() as $row) {
            unset($row->password);
            $row->relation = 0;

            if($role == 'student') {
                $getRationalization = $this->db()->table("rationalization")
                ->select(
                    $this->db()->raw("
                        users.fullname as consultant_name,
                        rationalization.university_one,
                        rationalization.major_one,
                        rationalization.university_two,
                        rationalization.major_two,
                        rationalization.graduation_status,
                        rationalization.pass_at_university,
                        rationalization.pass_at_major,
                        rationalization.graduation_year
                    ")
                )
                ->leftJoin("users","users.id","=","rationalization.consultant_id")
                ->where("rationalization.users_id", $row->id)
                ->whereNull("rationalization.softdelete")
                ->first();

                $university_one = "-";
                if(!empty($getRationalization->university_one)) {
                    $getuniversity = $this->db()->table("university")
                    ->select(array("id","name"))
                    ->where("id", $getRationalization->university_one)
                    ->first();

                    if(!empty($getuniversity->name)) {
                        $university_one = $getuniversity->name;
                    }
                }
                    
                $major_one = "";
                if(!empty($getRationalization->major_one)) {
                    $getMajor = $this->db()->table("major")
                    ->select(array("id","name"))
                    ->where("id", $getRationalization->major_one)
                    ->first();

                    if(!empty($getMajor->name)) {
                        $major_one = $getMajor->name;
                    }
                }

                $university_two = "-";
                if(!empty($getRationalization->university_two)) {
                    $getuniversity = $this->db()->table("university")
                    ->select(array("id","name"))
                    ->where("id", $getRationalization->university_two)
                    ->first();

                    if(!empty($getuniversity->name)) {
                        $university_two = $getuniversity->name;
                    }
                }
                    
                $major_two = "";
                if(!empty($getRationalization->major_two)) {
                    $getMajor = $this->db()->table("major")
                    ->select(array("id","name"))
                    ->where("id", $getRationalization->major_two)
                    ->first();

                    if(!empty($getMajor->name)) {
                        $major_two = $getMajor->name;
                    }
                }

                $consultant_name = "-";
                if(!empty($getRationalization->consultant_name)) {
                    $consultant_name = $getRationalization->consultant_name;
                }
                $temp_rationalization['graduation_status']  = $getRationalization->graduation_status;

                $pass_at_university = "";
                $pass_at_major      = "";

                if($getRationalization->university_one == $getRationalization->pass_at_university) {
                    $pass_at_university = $university_one;
                }

                if($getRationalization->major_one == $getRationalization->pass_at_major) {
                    $pass_at_major = $major_one;
                }
                
                if($getRationalization->university_two == $getRationalization->pass_at_university) {
                    $pass_at_university = $university_two;
                }

                if($getRationalization->major_two == $getRationalization->pass_at_major) {
                    $pass_at_major = $major_two;
                }

                $temp_rationalization['pass_at_university'] = $pass_at_university;
                $temp_rationalization['pass_at_major']      = $pass_at_major;
                $temp_rationalization['graduation_year']    = $getRationalization->graduation_year;
                $temp_rationalization['consultant_name']    = $consultant_name;
                $temp_rationalization['university_one']     = $university_one;
                $temp_rationalization['major_one']          = $major_one;
                $temp_rationalization['university_two']     = $university_two;
                $temp_rationalization['major_two']          = $major_two;

                $rationalization    = array();
                $rationalization    = $temp_rationalization;

                $row->rationalization = $rationalization;
            }
            $result[] = $row;
        }

        return $result;
    }

    public function list_rationalization($id="", $school_id="") {

        $getData = $this->db()->table('users');
        $getData->whereNull("users.softdelete");
        $getData->whereNull("school.softdelete");
        $getData->select(
            $getData->raw("
                users.*,
                school.name as school_name
            ")
        );
        
        $getData->innerJoin("school","school.id","=","users.school_id");

        if(empty($id)) {
            $getRationalization = $this->db()->table("rationalization")
            ->whereNull("softdelete")
            ->get();
            
            $users_id = array();
            foreach ($getRationalization as $value) {
                $users_id[] = $value->users_id;
            }

            if(!empty($getData)) {
                $getData->whereNotIn("users.id", $users_id);
            }
        }

        $result = array();
        foreach($getData->get() as $row) {
            $row->relation = 0;
            $result[] = $row;
        }

        return $result;
    }

    public function list_achievement($school_id="", $keywords="",$page=null, $limit=null) {
        $getData = $this->db()->table('users');
        $getData->innerJoin("school","school.id","=","users.school_id");
        $getData->innerJoin("rationalization","users.id","=","rationalization.users_id");
        $getData->innerJoin("rationalization_achievement","rationalization.id","=","rationalization_achievement.rationalization_id");
        $getData->innerJoin("achievement","achievement.id","=","rationalization_achievement.achievement_id");

        $getData->whereNull("users.softdelete");
        $getData->whereNull("rationalization.softdelete");
        $getData->whereNull("rationalization_achievement.softdelete");
        $getData->whereNull("school.softdelete");
        $getData->select(
            $getData->raw("
                rationalization_achievement.id,
                users.fullname,
                rationalization_achievement.grade,
                school.name as school_name,
                achievement.name as achievement_name,
                rationalization_achievement.attachment
            ")
        );
        if(!empty($keywords)) {
            $getData->where(function($relation) use ($keywords) {
                $relation->where($relation->raw('lower(users.fullname)'), 'LIKE', '%'.$keywords.'%');
                $relation->orWhere($relation->raw('lower(achievement.name)'), 'LIKE', '%'.$keywords.'%');
            });
        }

        if (!empty($page)) {
            $limit = (int) $limit;
            $getData->limit($limit)->offset(($limit * $page) - $limit);
        }
        
        if(!empty($school_id)) {
            $getData->where("school_id", $school_id);        
        }

        $getData->where("users.role", "student");        
        $result = array();
        foreach($getData->get() as $row) {
            unset($row->password);
            $result[] = $row;
        }

        return $result;
    }
}