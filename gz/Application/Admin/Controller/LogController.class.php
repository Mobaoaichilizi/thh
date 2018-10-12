<?php
// +----------------------------------------------------------------------
// | 文章管理
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class LogController extends AdminbaseController {
	protected $admin_log;
	
	public function _initialize() {
		parent::_initialize();
		$this->admin_log = D("Log");
		$this->admin_user = D("admin");
	}
    public function index(){
		$count=$this->admin_log->count();
		$page = $this->page($count,11);
		$list = $this->admin_log
            ->order("createtime DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
		foreach($list as $row)
		{
			$row['admin_name']=$this->admin_user->where('id='.$row['user_id'])->getField('admin_login');
			$userst[]=$row;
		}
		$this->assign("page", $page->show());
		$this->assign("list",$userst);
        $this->display('index');
    }
	public function delLog()
	{
		$id = I('post.id',0,'intval');
		if ($this->admin_log->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}
	public function cancelLog()
	{
		if ($this->admin_log->where("1=1")->delete()!==false) {
			$this->success("日志清空成功!");
		} else {
			$this->error("日志清空失败!");
		}
	}
	
}
?>