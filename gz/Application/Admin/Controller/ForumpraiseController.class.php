<?php
// +----------------------------------------------------------------------
// | 论坛点赞管理
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class ForumpraiseController extends AdminbaseController {
	protected $forum;
	
	public function _initialize() {
		parent::_initialize();
		$this->forum = D("Forum");
		$this->user = D("User");
		$this->forumoperation = D("ForumOperation");
	}
    public function index(){
		$title = I('request.title');
		$where['lgq_forum_operation.type'] = array('eq',1);
		if($title){
			$where['title'] = array('like',"%$title%");
		}
		$count=$this->forumoperation->join('lgq_forum on lgq_forum.id=lgq_forum_operation.forum_id')->where($where)->count();
		$page = $this->page($count,11);
		$list = $this->forumoperation
			->join('lgq_forum on lgq_forum.id=lgq_forum_operation.forum_id')
			->field('lgq_forum.title,lgq_forum_operation.*')
            ->where($where)
            ->order("createtime DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
		foreach($list as $row)
		{
			$row['user_name']=$this->user->where('id='.$row['user_id'])->getField('username');
			$row['forum_name']=$this->forum->where('id='.$row['forum_id'])->getField('title');
			$listt[]=$row;
		}
		$this->assign("page", $page->show());
		$this->assign("list",$listt);
        $this->display('index');
    }
	
}
?>