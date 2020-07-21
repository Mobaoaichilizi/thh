<?php
/**
 *
 * @author: love_fat<45199201@qq.com>
 * @day: 2019/08/15
 */
namespace app\admin\model;
use think\Model;

class Role extends Model
{
    protected $insert = ['create_time'];


    protected function setCreateTimeAttr()
    {
        return time();
    }

    protected function getCreateTimeAttr($value)
    {
        return date('Y-m-d H:i:s', $value);
    }
    /**
     * 添加信息
     * @param $param
     * @return array
     */
    public static function insertRole($param)
    {
        $model = new self;
        $result =  $model->validate('Role')->save($param);
        if(false === $result){
            // 验证失败 输出错误信息
            return msg(0, '', $model->getError());
        }else{
            return msg(1,$result,'获取成功！');
        }
    }

    /**
     * 编辑信息
     * @param $param
     * @return array
     */
    public static function updateRole($param)
    {
        $model = new self;
        $result =  $model->validate('Role')->save($param, ['id' => $param['id']]);
        if(false === $result){
            // 验证失败 输出错误信息
            return msg(0, '', $model->getError());
        }else{
            return msg(1,$param['id'],'获取成功！');
        }
    }

}

?>