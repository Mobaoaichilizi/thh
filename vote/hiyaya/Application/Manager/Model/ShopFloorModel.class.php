<?php
namespace Manager\Model;
use Think\Model;
class ShopFloorModel extends Model
{

	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
		array('floor_name','floor_name_unique','楼层名称已经存在！',0,'callback',1),
        array('floor','floor_unique','楼层已经存在！',0,'callback',1),
	);
	function floor_name_unique($arg1){
		$shop_id = session('shop_id');
		$count = M('ShopFloor')->where('shop_id='.$shop_id.' and floor_name='.$arg1)->count();
		if($count > 0)
		{
			return false;
		}
		else
        {
			return true;
		}
	}

	function floor_unique($arg)
    {
        $shop_id = session('shop_id');
        $count = M('ShopFloor')->where('shop_id='.$shop_id.' and floor='.$arg)->count();
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

