<?php
namespace Admin\Model;
use Think\Model;
class PricetimeModel extends Model
{
	
	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
		
	);
	
	protected $_auto = array(
	    array('createtime','mGetDate',self:: MODEL_INSERT,'callback'),
	);
	function mGetDate() {
		return time();
	}
	
	public function update($data) {
        /* 获取数据 */
        $data = $this->create($data);
        if ($data === false) {
            return false;
        }
        /*计算活动天数*/
        if (empty($data['id'])) {//新增数据
            $id = $this->add($data);
            if (!$id) {
                $this->error = '新增数据失败！';
                return false;
            }
        } else { //更新数据
            $status = $this->save($data);
            if (false === $status) {
                $this->error = '更新数据失败！';
                return false;
            }
        }
        return true;
    }
	
}

