<?php
namespace Manager\Model;
use Think\Model;
class ShopMasseurModel extends Model
{

	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
		array('masseur_sn','masseur_sn_unique','技师工号已经存在！',0,'callback',1),
        array('tel','tel_unique','技师电话已经存在！',0,'callback',1),
	);
	function masseur_sn_unique($arg1){
		$shop_id = session('shop_id');
		$count = M('ShopMasseur')->where('shop_id='.$shop_id.' and masseur_sn='.$arg1)->count();
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
        $count = M('ShopMasseur')->where('shop_id='.$shop_id.' and tel='.$arg)->count();
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

