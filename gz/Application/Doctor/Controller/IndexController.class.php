<?php
// +----------------------------------------------------------------------
// | 主页文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class IndexController extends DoctorbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->setting =D("Setting");
		$this->adv =D("Adv");
		$this->information =D("Information");
		
	}
	//首页
	public function index()
	{

		$result=$this->adv->field("title,img_thumb")->where("adv_id=33")->order('sort asc')->select();
		$result1=$this->information->field("id,title")->where("setting_id=19 and status=1")->order("update_time desc")->limit("0,5")->select();
		// $result2=$this->setting->field("id,title,img_thumb,description")->where("parentid=110")->order("sort asc")->select();
		$ress['banner'] = $result;
		$ress['information'] = $result1;
		// $ress['type'] = $result2;
		if($ress)
		{
			unset($data);
			$data['code']=1;
			$data['message']="获取成功！";
			$data['info']=$ress;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=2;
			$data['message']="暂无信息！";
			outJson($data);	
		}
		
	}
	public function info_details(){
		$uid = $this->uid;
		$id = I('post.id');//头条ID
		if(empty($id)){
			unset($data);
			$data['code'] = 0;
			$data['message'] ="此头条不存在！";
			outJson($data);
		}
		$info = $this->information->field('id,title,img_thumb,description,content,update_time')->where("id=".$id." and status=1")->find();
		$info['update_time'] = date("Y-m-d H:i:s",$info['update_time']);
		$info['content']=html_entity_decode($info['content']);
		if($info){
			unset($data);
			$data['code'] = 1;
			$data['message'] ="success";
			$data['info'] = $info;
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 0;
			$data['message'] ="fail";
			outJson($data);
		}
	}
	//分类展示
	public function index_cate()
	{
		$result=$this->setting->field("id,title,img_thumb,description")->where("parentid=110")->order("sort asc")->select();
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

}
?>