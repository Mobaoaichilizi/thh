<?php
// +----------------------------------------------------------------------
// | 论坛信息
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class ForumController extends AdminbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->forum = D("forum");
		$this->setting = D("Setting");

	}

	//列表显示
    public function index(){
    	$name = I('request.name');
    	$setting_id = I('request.setting_id');
    	$status = I('request.status');
    	$type = I('request.type');
		if($name){
			$where['title'] = array('like',"%$name%");
		}
		if($setting_id){
			$where['setting_id'] = array('eq',$setting_id);
		}
		if($status !== ''){
			$where['status'] = array('eq',$status);
		}
		if($type){
			$where['type'] = array('eq',$type);
		}
		$count=$this->forum->where($where)->count();
		$page = $this->page($count,11);
		$forum = $this->forum->join("lgq_user on lgq_user.id = lgq_forum.user_id")->field("lgq_user.username,lgq_forum.*")
            ->where($where)
            ->order("lgq_forum.id DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();

		$result = $this->setting->where('parentid=21')->order(array("sort" => "ASC"))->select();
		
		$this->assign("page", $page->show());
		$this->assign("forumlist",$forum);
		$this->assign("categories",$result);
        $this->display('index');
    }
   	//查看详细信息
   	public function lookForum(){
   		if(IS_POST){
   			$id = I('post.id',0,'intval');
   			$res = array(
   				"id" => $id,
   				"status" => $_POST['status'],
   			);
			if($this->forum->create()!==false) {
				if ($forum!==false) {
					$forum=$this->forum->where("id=".$id)->save($res);
					$this->success("编辑成功！", U("Forum/index"));
				}else
				{
					$this->error('编辑失败!');
				}
			}
   		}else{
   			$id = I('get.id',0,'intval');
			$forum = $this->forum->join("lgq_setting on lgq_setting.id=lgq_forum.setting_id")->join("lgq_user on lgq_user.id=lgq_forum.user_id")->field("lgq_forum.*,lgq_user.username,lgq_setting.title as stitle")->where("lgq_forum.id=".$id)->find();
			if(!empty($forum['item_thumb'])){
				$forum['item_thumb'] = explode(',', $forum['item_thumb']);
			}
			

			$this->assign('forum',$forum);
			$this->display('lookForum');
   		}
   	}
	//删除论坛消息
	public function delForum()
	{
		$id = I('post.id',0,'intval');
		$content=$this->forum->where("id=".$id)->getField("content");
		admin_log('del','forum',$content);
		if ($this->forum->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}



}