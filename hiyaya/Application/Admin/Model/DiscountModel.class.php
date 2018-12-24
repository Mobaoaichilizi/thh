<?php
namespace Admin\Model;
use Think\Model;
class DiscountModel extends Model
{
	
	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
		
	);
	
	protected $_auto = array(
	    array('create_time','mGetDate',self:: MODEL_INSERT,'callback'),
		array('starttime','mGetStartDate',self:: MODEL_BOTH,'callback'),
		array('endtime','mGetEndDate',self:: MODEL_BOTH,'callback'),
	);
	function mGetDate() {
		return time();
	}
	function mGetStartDate() {
		$starttime=I('post.starttime');
		return strtotime($starttime);
	}
	function mGetEndDate() {
		$endtime=I('post.endtime');
		return strtotime($endtime);
	}
	
}

