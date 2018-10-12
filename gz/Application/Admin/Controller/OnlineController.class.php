<?php
// +----------------------------------------------------------------------
// | 在线会诊管理
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class OnlineController extends AdminbaseController {

	public function _initialize() {
		parent::_initialize();
		$this->onlinetreat = D("Onlinetreat");
		$this->onlinetreatuser = D("Onlinetreatuser");
		$this->user = D("User");
		$this->member = D("Member");
	}
    public function index(){
		$status = I('request.status');
		
		$where['status'] = array('like',"%$status%");
		
		$count=$this->onlinetreat->where($where)->count();
		$page = $this->page($count,11);
		$list = $this->onlinetreat
            ->where($where)
            ->order("create_time DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
		foreach($list as $row)
		{
			$info['member']=$this->onlinetreatuser->where('online_id='.$row['id'])->getField('user_id');
			$info['join_member']=$this->onlinetreatuser->where('online_id='.$row['id'])->getField('join_user');
			
			if(!empty($info['member'])){
				$row['member'] = $this->member->where("user_id=".$info['member'])->getfield("name");
				if(empty($info['join_member'])){
					$row['join_member'] = "无";
				}else{
					$res = $this->member->where("md5(md5(user_id)) in ('".$info['join_member']."')")->getfield("name",true);
					$row['join_member'] = implode(",", $res);
				}
				$listt[]=$row;

			}
			
		}
		$this->assign("page", $page->show());
		$this->assign("list",$listt);
        $this->display('index');
    }
	
	
	
}
?>