<?php
/**
 *
 * @author: thh<752106024@qq.com>
 * @day: 2020/06/05
 */
namespace app\home\model;
use think\Model;
use think\Request;

class LoginLog extends Model
{

    public static function insertLog($userName,$status,$remark)
    {
        $model = new self;
        $param = [
            'user_name' => $userName,
            'ip' => Request::instance()->ip(),
            'status' => $status,
            'remark' => $remark,
            'create_time' => time(),
            'app_name' => 'Home'
        ];
        $model->save($param);
    }
    

}

?>