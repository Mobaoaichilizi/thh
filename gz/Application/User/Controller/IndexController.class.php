<?php
// +----------------------------------------------------------------------
// | 主页文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
class IndexController extends UserbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->setting =D("Setting");
		$this->adv =D("Adv");
		$this->information =D("Information");
		$this->goods =D("Goods");
		$this->member =D("Member");
		
	}
	//首页banner
	public function banner()
	{

		$result=$this->adv->field("title,img_thumb")->where("adv_id=34")->order('sort asc')->select();
		
		if($result)
		{
			foreach($result as &$val)
			{
				$val['img_thumb']=$this->host.$val['img_thumb'];
			}
			unset($data);
			$data['code']=1;
			$data['message']="banner图获取成功！";
			$data['info']=$result;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=1;
			$data['message']="暂无图片！";
			$data['info']=array();
			outJson($data);	
		}
		
	}
	//首页头条新闻
	public function top_news()
	{
		$result=$this->information->field("id,title")->where("setting_id=20 and status=1")->order("update_time desc")->limit("0,5")->select();
		if($result)
		{
			unset($data);
			$data['code']=1;
			$data['message']="新闻获取成功！";
			$data['info']=$result;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=1;
			$data['message']="暂无新闻";
			$data['info']=array();
			outJson($data);	
		}
		
	}
	//分类展示
	public function index_cate()
	{
		$result=$this->setting->field("id,title,img_thumb")->where("parentid=57 and status=1")->order("sort asc")->select();
		if($result)
		{
			foreach($result as &$val)
			{
				$val['img_thumb']=$this->host.$val['img_thumb'];
			}
			unset($data);
			$data['code']=1;
			$data['message']="分类获取成功！";
			$data['info']=$result;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=1;
			$data['message']="暂无分类！";
			$data['info']=array();
			outJson($data);	
		}
	}
	//更多分类展示
	public function cate_more()
	{
		$result=$this->setting->field("id,title,img_thumb")->where("parentid=208 and status=1")->order("sort asc")->select();
		if($result)
		{
			foreach($result as &$val)
			{
				$val['img_thumb']=$this->host.$val['img_thumb'];
			}
			unset($data);
			$data['code']=1;
			$data['message']="分类获取成功！";
			$data['info']=$result;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=1;
			$data['message']="暂无分类！";
			$data['info']=array();
			outJson($data);	
		}
	}
	
	//名医工作室
	public function doctor_studio()
	{
		$result=$this->member->field("name,img_thumb,user_id,professional")->where("status=1 and is_position=1")->order("id desc")->limit("0,12")->select();
		foreach ($result as $key => &$value) {
			$value['doctor_id'] = md5(md5($value['user_id']));
		}
		if($result)
		{
			unset($data);
			$data['code']=1;
			$data['message']="名医工作室获取成功！";
			$data['info']=$result;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=1;
			$data['message']="暂无医生工作室";
			$data['info']=array();
			outJson($data);	
		}
	}
	
	//验方推荐
	public function recommend_goods()
	{
		$result=$this->goods->field("id,title,price,img_thumb")->where("status=0 and recommend=1")->order("createtime desc")->limit("0,12")->select();
		if($result)
		{
			foreach($result as &$val)
			{
				$val['img_thumb']=$this->host.$val['img_thumb'];
			}
			unset($data);
			$data['code']=1;
			$data['message']="验方推荐获取成功！";
			$data['info']=$result;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=1;
			$data['message']="暂无商品";
			$data['info']=array();
			outJson($data);	
		}
	}
	//新闻详情
	public function news_deal()
	{
		$id=I('post.id');
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="新闻不存在！";
			outJson($data);
		}
		$result=$this->information->field("title,content,update_time")->where("id=".$id)->find();
		$result['update_time']=date("Y-m-d H:i:s",$result['update_time']);
		$result['content']=html_entity_decode($result['content']);
		unset($data);
		$data['code']=1;
		$data['message']="获取成功！";
		$data['info']=$result;
		outJson($data);	
	}
	

}
?>