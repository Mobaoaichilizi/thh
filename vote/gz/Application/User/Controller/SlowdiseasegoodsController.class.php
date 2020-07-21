<?php
// +----------------------------------------------------------------------
// | 慢性疾病商品列表管理文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
class SlowdiseasegoodsController extends UserbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->user =D("User");
		$this->member =D("Member");
		$this->goods =D("Goods");
		$this->department =D("Department");
		
	}
	//寻医诊疗商品列表
	public function lists()
	{
		
		$p=!empty($_POST['p']) ? $_POST['p'] : 1;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
		$settimg_id=!empty($_POST['settimg_id']) ? $_POST['settimg_id'] : 0;
		$typesort=!empty($_POST['typesort']) ? $_POST['typesort'] : 0;
		if(empty($settimg_id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请选择病症！";
			outJson($data);	
		}
		$p=($p-1)*$limit;
		
		if($settimg_id!==0)
		{
			$sqlwhere.=" and category_id=".$settimg_id;
		}
		if($typesort==1)
		{
			$orderwhere="sales desc,";
		}else if($typesort==2)
		{
			$orderwhere="price asc,";
		}
		$result=$this->goods->field("id,title,img_thumb,price,sales")->where("status=0 ".$sqlwhere)->order($orderwhere."createtime desc")->limit($p.",".$limit)->select();
		if($result)
		{
			unset($data);
			$data['code']=1;
			$data['message']="商品列表成功！";
			$data['info']=$result;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=1;
			$data['message']="暂无商品！";
			$data['info']=array();
			outJson($data);	
		}
	}
	
	

}
?>