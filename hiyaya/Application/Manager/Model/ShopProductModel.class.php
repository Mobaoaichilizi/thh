<?php
namespace Manager\Model;
use Think\Model;
class ShopProductModel extends Model
{

	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
		array('product_sn','product_sn_unique','产品编号已经存在！',0,'callback',1),
        array('product_name','product_name_unique','产品名称已经存在！',0,'callback',1),
	);
	function product_sn_unique($arg1){
		$shop_id = session('shop_id');
		$count = M('ShopProduct')->where('shop_id='.$shop_id.' and product_sn="'.$arg1.'"')->count();
		if($count > 0)
		{
			return false;
		}
		else
        {
			return true;
		}
	}

	function product_name_unique($arg)
    {
        $shop_id = session('shop_id');
        $count = M('ShopProduct')->where('shop_id='.$shop_id.' and product_name="'.$arg.'"')->count();
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

