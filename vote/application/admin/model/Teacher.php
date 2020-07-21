<?php
/**
 *
 * @author: thh<752106024@qq.com>
 * @day: 2019/08/15
 */

namespace app\admin\model;

use think\Model;

class Teacher extends Model
{
    protected $insert = ['create_time'];

    protected function getCreateTimeAttr($value)
    {
        return date('Y-m-d H:i:s', $value);
    }

    protected function getLastLoginTimeAttr($value)
    {
        return date('Y-m-d H:i:s', $value);
    }

    protected function setCreateTimeAttr()
    {
        return time();
    }

    /**
     * 添加信息
     * @param $param
     * @return array
     */
    public static function insertTeacher($param)
    {
        $model = new self;
        $result = $model->validate('Teacher')->save($param);
        if (false === $result) {
            // 验证失败 输出错误信息
            return msg(0, '', $model->getError());
        } else {
            return msg(1, $result, '获取成功！');
        }
    }

    /**
     * 编辑信息
     * @param $param
     * @return array
     */
    public static function updateTeacher($param)
    {
        $model = new self;
        $result = $model->save($param, ['id' => $param['id']]);
        if (false === $result) {
            // 验证失败 输出错误信息
            return msg(0, '', $model->getError());
        } else {
            return msg(1, $param['id'], '获取成功！');
        }
    }


}

?>