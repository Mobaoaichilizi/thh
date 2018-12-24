<?php
namespace Manager\Model;
use Think\Model;
class ShopRewardModel extends Model
{
    public function _initialize()
    {
        parent::_initialize();

    }
	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
		array('reward_name','reward_name_unique','技师级别名称已经存在！',0,'callback',1),

	);
	function reward_name_unique($arg){
        $shop_id = session('shop_id');
        $arg=trim($arg);
		$count = M('ShopReward')->where('shop_id='.$shop_id.' and reward_name="'.$arg.'"')->count();
		if($count > 0)
		{
			return false;
		}
		else
        {
			return true;
		}
	}




	
}

