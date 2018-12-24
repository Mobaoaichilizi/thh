<?php
// +----------------------------------------------------------------------
// | 分组管理
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Manager\Controller;
use Common\Controller\ManagerbaseController;
class GroupController extends ManagerbaseController {
	
	public function _initialize() {
		parent::_initialize();
		$this->shopgroup = D("ShopGroup"); //分组表
		$this->shopmasseur = M("ShopMasseur"); //技师表
		$this->shopgroupmasseur = M("ShopGroupmasseur"); //分组技师表
		$this->masseurcategory = M("MasseurCategory"); //技师等级表
		$this->shopid=session('shop_id');
		$this->chainid=session('chain_id');
	}
    public function index(){
		
        $this->display('index');
    }
	public function get_index()
	{
		$name = I('request.name');
		$limit = I('request.limit');
		$page = I('request.page');
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
            ->where($where)
            ->order("sort asc,id DESC")
            ->limit($page.','.$limit)
            ->select();
		foreach($rolelist as &$row)
		{
			$row['createtime']=date("Y-m-d H:i:s",$row['createtime']);
		}
		unset($data);
		$data['code']=0;
		$data['msg']=$page;
		$data['count']=$count;
		$data['data']=$rolelist;
		outJson($data);
	}
	public function addGroup()
	{
		if(IS_POST){
			if($this->shopgroup->create()!==false) {
				$result=$this->shopgroup->add();
				if ($result!==false) {
					$this->success("添加成功！", U("Group/index"));
				}else
				{
					$this->error('添加失败!');
				}
			}	
		}else
		{
			$this->display('addGroup');
		}
	}
	public function editGroup()
	{
		if(IS_POST)
		{
			$id = I('post.id',0,'intval');
			if($this->shopgroup->create()!==false) {
				$result=$this->shopgroup->where("id=".$id)->save();
				if ($result!==false) {
					$this->success("编辑成功！", U("Group/index"));
				}else
				{
					$this->error('编辑失败!');
				}
			}
		}else
		{
			$id = I('get.id',0,'intval');
			$result=$this->shopgroup->where("id=".$id)->find();
			$this->assign('result',$result);
			$this->display('editGroup');
		}
	}
	public function delGroup()
	{
		$id = I('post.id',0,'intval');

		if ($this->shopgroup->delete($id)!==false) {
			$this->shopgroupmasseur->where("group_id=".$id." and shop_id=".$this->shopid)->delete();
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}

	}
	public function saveGroup()
	{
		$group_id = I('get.id',0,'intval');
		$un_group_list=$this->shopmasseur->where("state=1 and shop_id=".$this->shopid." and id not in (select masseur_id from dade_shop_groupmasseur where shop_id=".$this->shopid.")")->order("id desc")->select();
		foreach ($un_group_list as $k => &$v) {
			$v['category_name'] = $this->masseurcategory->where('id='.$v['category_id'])->getfield('category_name');
		}
		$this->assign('un_group_list',$un_group_list);
		$yes_group_list=$this->shopmasseur->where("state=1 and shop_id=".$this->shopid." and id in (select masseur_id from dade_shop_groupmasseur where group_id=".$group_id." and shop_id=".$this->shopid.")")->order("id desc")->select();
		foreach ($yes_group_list as $k1 => &$v1) {
			$v1['category_name'] = $this->masseurcategory->where('id='.$v1['category_id'])->getfield('category_name');
		}
		$this->assign('yes_group_list',$yes_group_list);
		$this->assign('group_id',$group_id);
		$this->display('saveGroup');
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
	public function delGroupmasseur()
	{
		$id = I('post.id',0,'intval');
		if ($this->shopgroupmasseur->where("masseur_id=".$id." and shop_id=".$this->shopid)->delete()!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}

	}
}
?>