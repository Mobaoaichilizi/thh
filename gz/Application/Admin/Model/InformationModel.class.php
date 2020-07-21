<?php
namespace Admin\Model;
use Think\Model;
class InformationModel extends Model
{
	
	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
		
	);
	
	protected $_auto = array(
	    array('update_time','mGetDate',self:: MODEL_BOTH,'callback'),
	);
	function mGetDate() {
		return time();
	}
	
}

