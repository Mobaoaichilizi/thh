<?php
namespace Manager\Model;
use Think\Model;
class ShopCikaModel extends Model
{

	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
		array('package_name','category_name_unique','套餐名称已经存在！',0,'callback',1),

	);
	function category_name_unique($arg){
        $arg=trim($arg);
        $shop_id = session('shop_id');
		$count = M('ShopCika')->where('shop_id='.$shop_id.' and package_name="'.$arg.'"')->count();
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

