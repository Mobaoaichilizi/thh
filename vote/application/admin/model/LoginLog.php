<?php
/**
 *
 * @author: love_fat<45199201@qq.com>
 * @day: 2019/08/15
 */
namespace app\admin\model;
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
            'app_name' => 'Admin'
        ];
        $model->save($param);
    }
    

}

?>