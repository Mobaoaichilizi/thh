<?php
namespace Manager\Model;
use Think\Model;
class ShopRoomModel extends Model
{

	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
		array('room_name','room_name_unique','包间名称已经存在！',0,'callback',1),
        array('tel','tel_unique','技师电话已经存在！',0,'callback',1),
	);
	function room_name_unique($arg1){
		$shop_id = session('shop_id');
		$count = M('ShopRoom')->where('shop_id='.$shop_id.' and room_name="'.$arg1.'"')->count();
		if($count > 0)
		{
			return false;
		}
		else
        {
			return true;
		}
	}

	function tel_unique($arg)
    {
        $shop_id = session('shop_id');
        $count = M('ShopRoom')->where('shop_id='.$shop_id.' and tel="'.$arg.'"')->count();
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

