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
class MemberController extends AdminbaseController {
	protected $member;
	
	public function _initialize() {
		parent::_initialize();
		$this->member = D("Member");
	}
    public function index(){
		$username = I('request.username');
		if($username){
			$where['username'] = array('like',"%$username%");
		}
		$count=$this->member->where($where)->count();
		$page = $this->page($count,11);
		$users = $this->member
            ->where($where)
            ->order("createtime DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
		$this->assign("page", $page->show());
		$this->assign("lists",$users);
        $this->display('index');
    }
	
	public function disableMember()
	{
		$id = I('post.id',0,'intval');
		$res=array(
			'status' => 0,
		);
		if ($this->member->where("id=".$id)->save($res)!==false) {
			$this->success("禁用成功！");
		} else {
			$this->error("禁用失败！");
		}
	}
	public function enableMember()
	{
		$id = I('post.id',0,'intval');
		$res=array(
			'status' => 1,
		);
		if ($this->member->where("id=".$id)->save($res)!==false) {
			$this->success("启用成功！");
		} else {
			$this->error("启用失败！");
		}
	}
}
?>