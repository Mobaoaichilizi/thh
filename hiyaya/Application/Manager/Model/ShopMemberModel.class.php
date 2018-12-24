<?php
namespace Manager\Model;
use Think\Model;
class ShopMemberModel extends Model
{
	

	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
		array('member_no','member_no_unique','会员编号已经存在！',0,'callback',1),
		array('member_card','member_card_unique','此卡号已经绑定！',0,'callback',1),
		
	);
	function member_no_unique($arg1){
		$shop_id = session('shop_id');
		$count = M('ShopMember')->where('shop_id='.$shop_id." and member_no='".$arg1."'")->count();
		if($count > 0){
			return false;
		}else{
			return true;
		}
	}

	function member_card_unique($arg1){
		$shop_id = session('shop_id');
		$chain_id=session('chain_id');
		$count = M('ShopMember')->where('chain_id='.$chain_id." and member_card='".$arg1."'")->count();
		if($count > 0){
			return false;
		}else{
			return true;
		}
	}
	
}

