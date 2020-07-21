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
class SearchController extends UserbaseController {
	public function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->search =D("Search");
		$this->member =D("Member");
		$this->department =D("Department");
		$this->uid=D('User')->where("md5(md5(id))='".$uid."'")->getField('id');
		
	}
	//历史记录
	public function hot_history()
	{
		$result=array();
		$result_hot=array();
		$result=$this->search->field("id,title")->where("user_id=".$this->uid." and is_hot=0")->order('createtime asc')->limit("0,6")->select();
		// $result_hot=$this->search->field('id,title,count(title) as count')->group('title')->having('count(title)>1')->order('count desc')->select();
		$result_hot=$this->search->field('id,title')->where("is_hot=1")->order('sort asc,createtime desc')->limit(6)->select();
		unset($data);
		$data['code']=1;
		$data['message']="历史获取成功！";
		$data['info']=$result;
		$data['hot_key']=$result_hot;
		outJson($data);	
	}
	public function del_history()
	{
		$result=$this->search->where("user_id=".$this->uid)->delete();
		if($result)
		{
			unset($data);
			$data['code']=1;
			$data['message']="清除成功！";
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="清除失败！";
			outJson($data);	
		}
	}
	public function del_onehistory()
	{
		$id=I('post.id');
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请选择记录";
			outJson($data);	
		}
		$result=$this->search->where("user_id=".$this->uid." and id=".$id)->delete();
		if($result)
		{
			unset($data);
			$data['code']=1;
			$data['message']="清除成功！";
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="清除失败！";
			outJson($data);	
		}
	}
	public function search_doctor()
	{
		$keywords=I('post.keywords');
		$p=!empty($_POST['p']) ? $_POST['p'] : 1;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
		$department_id=!empty($_POST['department_id']) ? $_POST['department_id'] : 0;
		$longitude=!empty($_POST['longitude']) ? $_POST['longitude'] : 0;
		$latitude=!empty($_POST['latitude']) ? $_POST['latitude'] : 0;
		$typesort=!empty($_POST['typesort']) ? $_POST['typesort'] : 0;
		$default_id=!empty($_POST['default_id']) ? $_POST['default_id'] : 0;
		$p=($p-1)*$limit;
		
		if($department_id!==0)
		{
			$sqlwhere.=" and lgq_member.department_id=".$department_id;
		}
		if($typesort==1)
		{
			$orderwhere="juli asc,";
		}
		if($keywords!='')
		{
			$sqlwhere.=" and (lgq_member.name like '%".$keywords."%' or lgq_member.disease like '%".$keywords."%' or lgq_department.name like '%".$keywords."%')";
		}
		$res=array(
			'user_id' => $this->uid,
			'title' => $keywords,
			'createtime' => time(),
		);
		$this->search->add($res);
		$result=$this->member->join('lgq_user on lgq_member.user_id=lgq_user.id','left')->join('lgq_hospital on lgq_member.hospital_id=lgq_hospital.id','left')->join('lgq_department on lgq_member.department_id=lgq_department.id','left')->field("lgq_member.user_id as id,lgq_member.name as name,lgq_member.img_thumb as img_thumb,lgq_member.professional as professional,lgq_member.profile as profile,lgq_user.member_level as level,lgq_user.is_login as is_login,ROUND(6378.138 * 2 * ASIN(SQRT(POW(SIN((".$latitude." * PI() / 180 - lgq_hospital.lat * PI() / 180) / 2),2) + COS(".$latitude." * PI() / 180) * COS(lgq_hospital.lat * PI() / 180) * POW(SIN((".$longitude." * PI() / 180 - lgq_hospital.lng * PI() / 180) / 2),2))) * 1000) AS juli")->where("lgq_member.status=1 and lgq_user.role=1 ".$sqlwhere)->order($orderwhere."lgq_user.member_level desc,lgq_user.createtime desc")->limit($p.",".$limit)->select();
		if($result)
		{
			unset($data);
			$data['code']=1;
			$data['message']="获取医生列表成功！";
			$data['info']=$result;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=1;
			$data['message']="暂无医生！";
			$data['info']=array();
			outJson($data);	
		}
	}
}
?>