<?php
// +----------------------------------------------------------------------
// | 分组管理
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Api\Controller;
use Common\Controller\ApibaseController;
class GroupController extends ApibaseController {
	
	public function _initialize() {
		parent::_initialize();
		$this->shopgroup = D("ShopGroup"); //分组表
		$this->shopmasseur = M("ShopMasseur"); //技师表
		$this->shopgroupmasseur = M("ShopGroupmasseur"); //分组技师表
		$this->shopid=$this->shop_id;
		$this->chainid=$this->chain_id;
	}
	public function get_index()
	{
		$name = I('post.name');
		$limit = I('post.pagesize',10,'intval');
		$page = I('post.pagecur',1,'intval');
		if($page==1)
		{
			$page=0;
		}else
		{
			$page=($page-1)*$limit;
		}
		$where['shop_id']=$this->shopid;
		if($name){
			$where['name'] = array('like',"%$name%");
		}
		$count=$this->shopgroup->where($where)->count();
		$rolelist = $this->shopgroup
            ->field("id,name,remark,sort")->where($where)
            ->order("sort asc,id DESC")
            ->limit($page.','.$limit)
            ->select();
		unset($data);
		$data['code']=0;
		$data['msg']=$page;
		$data['count']=$count;
		$data['data']=$rolelist;
		outJson($data);
	}
	//添加提交排班
	public function addGroup()
	{
		$name=I('post.name');
		$remark=I('post.remark');
		$sort=I('post.sort');
		if(empty($name))
		{
			unset($data);
			$data['code']=1;
			$data['msg']="请输入分组名称";
			outJson($data);
		}
		if(empty($sort))
		{
			unset($data);
			$data['code']=1;
			$data['msg']="请输入排序";
			outJson($data);
		}
		unset($data_array);
		$data_array=array(
			'name' => $name,
			'remark' => $remark,
			'sort' => $sort,
			'chain_id' => $this->chainid,
			'shop_id' => $this->shopid,
			'createtime' => time(),
		);
		$result=$this->shopgroup->add($data_array);
		if ($result!==false) {	
			unset($data);
			$data['code']=0;
			$data['msg']="添加成功！";
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=1;
			$data['msg']="添加失败";
			outJson($data);
		}
	}
	//编辑排班
	public function showGroup()
	{
		$id=I('post.id');
		if(empty($id))
		{
			unset($data);
			$data['code']=1;
			$data['msg']="您查找的排版不存在";
			outJson($data);
		}
		$result=$this->shopgroup->field("id,name,remark,sort")->where("id=".$id)->find();
		if($result)
		{
			unset($data);
			$data['code']=0;
			$data['msg']="添加成功！";
			$data['data']=$result;
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=1;
			$data['msg']="您查找的排版不存在";
			outJson($data);
		}
	}
	//提交编辑排班
	public function editGroup()
	{
		$id=I('post.id');
		$name=I('post.name');
		$remark=I('post.remark');
		$sort=I('post.sort');
		if(empty($id))
		{
			unset($data);
			$data['code']=1;
			$data['msg']="您查找的排版不存在";
			outJson($data);
		}
		if(empty($name))
		{
			unset($data);
			$data['code']=1;
			$data['msg']="请输入分组名称";
			outJson($data);
		}
		if(empty($sort))
		{
			unset($data);
			$data['code']=1;
			$data['msg']="请输入排序";
			outJson($data);
		}
		unset($data_array);
		$data_array=array(
			'name' => $name,
			'remark' => $remark,
			'sort' => $sort,
		);
		$result=$this->shopgroup->where("id=".$id)->save($data_array);
		if ($result!==false) {	
			unset($data);
			$data['code']=0;
			$data['msg']="编辑成功！";
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=1;
			$data['msg']="编辑失败";
			outJson($data);
		}
	}
	//删除排班
	public function delGroup()
	{
		$id = I('post.id',0,'intval');
		$count=$this->shopgroupmasseur->where("group_id=".$id." and shop_id=".$this->shopid)->count();
		if($count > 0)
		{
			unset($data);
			$data['code']=1;
			$data['msg']="不能删除！";
			outJson($data);
		}
		if ($this->shopgroup->delete($id)!==false) {
			unset($data);
			$data['code']=0;
			$data['msg']="删除成功！";
			outJson($data);
		} else {
			unset($data);
			$data['code']=1;
			$data['msg']="删除失败！";
			outJson($data);
		}

	}
	
	
	//技师分组
	public function  saveGroup()
	{
		$un_group_list=$this->shopmasseur->where("state=1 and shop_id=".$this->shopid." and id not in (select masseur_id from dade_shop_groupmasseur where shop_id=".$this->shopid.")")->order("id desc")->select();
		$group_list=$this->shopgroup->field("id,name")->where("shop_id=".$this->shopid)->order("sort asc,id desc")->select();
		foreach($group_list as &$val)
		{
			$val['count']=$this->shopgroupmasseur->where("shop_id=".$this->shopid." and group_id=".$val['id'])->count();
			$val['masseur_list']=$this->shopmasseur->field("id,masseur_sn,masseur_name,cover")->where("state=1 and shop_id=".$this->shopid." and id in (select masseur_id from dade_shop_groupmasseur where group_id=".$val['id']." and shop_id=".$this->shopid.")")->order("id desc")->select();
		}
		unset($data);
		$data['code']=0;
		$data['msg']="获取成功！";
		$data['info']=$un_group_list;
		$data['data']=$group_list;
		outJson($data);
	}
	
	public function postGroup()
	{
		$chain_id = I("post.chain_id",0,'intval');
		$shop_id = I("post.shop_id",0,'intval');
		$group_id = I("post.group_id",0,'intval');
		if (is_array($_POST['masseur_id']) && count($_POST['masseur_id'])>0) {	
			foreach ($_POST['masseur_id'] as $val) {	
				unset($data);
				$data=array(
					'chain_id' => $chain_id,
					'shop_id' => $shop_id,
					'group_id' => $group_id,
					'masseur_id' => $val,
				);
				$this->shopgroupmasseur->add($data);
			}
			$this->success("授权成功！", U("Group/saveGroup"));
		}else{
			$this->error("请选择技师！");
		}
	}
	//删除分组人员
	public function delGroupmasseur()
	{
		$id = I('post.id',0,'intval');
		if ($this->shopgroupmasseur->where("masseur_id=".$id." and shop_id=".$this->shopid)->delete()!==false) {
			unset($data);
			$data['code']=0;
			$data['msg']="删除成功！";
			outJson($data);
		} else {
			unset($data);
			$data['code']=1;
			$data['msg']="删除失败！";
			outJson($data);
		}

	}
}
?>