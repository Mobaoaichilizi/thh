<?php
// +----------------------------------------------------------------------
// | 我要体检商品列表管理文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
class MovementgoodsController extends UserbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->user =D("User");
		$this->member =D("Member");
		$this->goods =D("Goods");
		
	}
	//商品列表
	public function lists()
	{
		
		$p=!empty($_POST['p']) ? $_POST['p'] : 1;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
		$typesort=!empty($_POST['typesort']) ? $_POST['typesort'] : 0;
		
		$p=($p-1)*$limit;
		
		if($typesort==1)
		{
			$orderwhere="sales desc,";
		}else if($typesort==2)
		{
			$orderwhere="price asc,";
		}
		$result=$this->goods->field("id,title,img_thumb,price,sales")->where("status=0 and category_id=15")->order($orderwhere."createtime desc")->limit($p.",".$limit)->select();
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
	
	public function goods_details(){
		$id = I('post.id');
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="商品不存在";
			outJson($data);	
		}
		$result=$this->goods->field("id,title,img_thumb,img_item,description")->where("id=".$id)->find();
		if($result)
		{
			$result['img_item']=explode(',',$result['img_item']);
			unset($data);
			$data['code']=1;
			$data['message']="获取成功！";
			$data['info']=$result;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="信息不存在";
			outJson($data);	
		}
	}

}
?>