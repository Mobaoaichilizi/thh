<?php
// +----------------------------------------------------------------------
// | 积分明细页面
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class ScoreController extends AdminbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->score = D("ScoreList");
		$this->setting = D("Setting");
		$this->user = D('User');
	}
    public function index(){
		$count=$this->score->count();
		$page = $this->page($count,11);
		$where['setting_id'] = array('not in','166,207');
		$list = $this->score
			->where($where)
            ->order("optime desc,id DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
		foreach($list as $row)
		{
			$row['title']=$this->setting->where('id='.$row['setting_id'])->getField('title');
			$row['username'] = $this->user->where("id=".$row['user_id'])->getField("username");
			$prolist[]=$row;
		}
		$this->assign("page", $page->show());
		$this->assign("list",$prolist);
        $this->display('index');
    }
	
	
}
?>