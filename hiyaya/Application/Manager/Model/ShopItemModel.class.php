<?php
namespace Manager\Model;
use Think\Model;
class ShopItemModel extends Model
{

	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
		array('item_sn','item_sn_unique','项目编号已经存在！',0,'callback',1),
        array('item_name','item_name_unique','项目名称已经存在！',0,'callback',1),
	);
	function item_sn_unique($arg1){
		$shop_id = session('shop_id');
		$count = M('ShopItem')->where('shop_id='.$shop_id.' and item_sn="'.$arg1.'"')->count();
		if($count > 0)
		{
			return false;
		}
		else
        {
			return true;
		}
	}

	function item_name_unique($arg)
    {
        $shop_id = session('shop_id');
        $count = M('ShopItem')->where('shop_id='.$shop_id.' and item_name="'.$arg.'"')->count();
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

