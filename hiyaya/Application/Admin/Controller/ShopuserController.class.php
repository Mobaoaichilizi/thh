<?php
// +----------------------------------------------------------------------
// | 店铺人员管理
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class ShopuserController extends AdminbaseController {
	protected $shopuser,$shop;
	
	public function _initialize() {
		parent::_initialize();
		$this->shopuser = D("ShopUser");
		$this->shop = M("Shop");
		$this->chain = M("Chain");
	}
    public function index()
	{
        $this->display('index');
    }
	public function get_index()
	{
		$username = I('request.username');
		$limit = I('request.limit');
		$page = I('request.page');
		if($page==1)
		{
			$page=0;
		}else
		{
			$page=($page-1)*$limit;
		}
		if($username){
			$where['username'] = array('like',"%$username%");
		}
		$count=$this->shopuser->where($where)->count();
		$users = $this->shopuser
            ->where($where)
            ->order("id DESC")
             ->limit($page.','.$limit)
            ->select();
		foreach($users as &$row)
		{
			$row['shop_name']=$this->shop->where('id='.$row['shop_id'])->getField('shop_name');
			$row['chain_name']=$this->chain->where('id='.$row['chain_id'])->getField('name');
			$row['createtime']=date("Y-m-d H:i:s",$row['createtime']);
			$row['last_login_time']=date("Y-m-d H:i:s",$row['last_login_time']);
			$row['state'] = $row['state']==1 ? '启用' : '禁用';
			$row['role_id'] = $row['role_id']==0 ? '本店超管' : '普通管理员';
		}
		unset($data);
		$data['code']=0;
		$data['msg']=$page;
		$data['count']=$count;
		$data['data']=$users;
		outJson($data);
	}
	public function addShopuser()
	{
		if(IS_POST){
			$username = I('post.username');
			$isadmin=$this->shopuser->where("username='".$username."'")->find();
			if($isadmin)
			{
				$this->error('用户已存在!');
			}
			if($this->shopuser->create()!==false) {
				$this->shopuser->password=sp_password(I('post.password'));
				$this->shopuser->chain_id=$this->shop->where("id=".I('post.shop_id'))->getField('chain_id');
				$result=$this->shopuser->add();
				$this->shopuser->where("id=".$result)->save(array('token' => md5($result.time().'lovefat')));
				if ($result!==false) {
					$this->success("添加成功！", U("Shopuser/index"));
				}else
				{
					$this->error('添加失败!');
				}
			}
			
		}else
		{
			$shoplist=$this->shop->where('state=1')->order('id asc')->select();
			$this->assign('shoplist',$shoplist);
			$this->display('addShopuser');
		}
	}
	public function editShopuser()
	{
		if(IS_POST)
		{
			$id = I('post.id',0,'intval');
			if($this->shopuser->create()!==false) {
				$this->shopuser->chain_id=$this->shop->where("id=".I('post.shop_id'))->getField('chain_id');
				$result=$this->shopuser->where("id=".$id)->save();
				if($result!==false) {
					$this->success("编辑成功！", U("Shopuser/index"));
				}else
				{
					$this->error('编辑失败!');
				}
			}
		}else
		{
			$id = I('get.id',0,'intval');
			$result=$this->shopuser->where("id=".$id)->find();
			$this->assign('result',$result);
			$shoplist=$this->shop->where('state=1')->order('id asc')->select();
			$this->assign('shoplist',$shoplist);
			$this->display('editShopuser');
		}
	}
	public function delShopuser()
	{
		$id = I('post.id',0,'intval');
		if ($this->shopuser->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}
}
?>