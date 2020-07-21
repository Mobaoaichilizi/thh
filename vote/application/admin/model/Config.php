<?php
/**
 *
 * @author: love_fat<45199201@qq.com>
 * @day: 2019/08/15
 */
namespace app\admin\model;
use think\Model;

class Config extends Model
{
    /**
     * 编辑信息
     * @param $param
     * @return array
     */
    public static function updateConfig($param)
    {
        $model = new self;
        $result =  $model->save($param, ['id' => $param['id']]);
        if(false === $result){
            // 验证失败 输出错误信息
            return msg(0, '', $model->getError());
        }else{
            return msg(1,$param['id'],'获取成功！');
        }
    }

}

?>