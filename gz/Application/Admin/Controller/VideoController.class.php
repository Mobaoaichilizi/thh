<?php
// +----------------------------------------------------------------------
// | 视频诊疗
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class VideoController extends AdminbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->video = D("Videodia");
		$this->user = D("User");
		$this->member = D("Member");
		$this->patientmember = D("Patientmember");

	}

	//列表显示
    public function index(){
    	$status = I('request.status');
		if($status){
			if($status == "未支付"){
				$status = 0;
			}else if($status == "待诊疗"){
				$status = 1;
			}else if($status == "已完成"){
				$status = 2;
			}
			$where['status'] = array('like',"%$status%");
		}
		$count=$this->video->where($where)->count();
		$page = $this->page($count,11);
		$video = $this->video
            ->where($where)
            ->order("id DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
        foreach ($video as $key => $value) {
			

        	$value['username'] = $this->member->join('lgq_user on lgq_user.id = lgq_member.user_id')->where('lgq_user.id='.$value['member_id'])->getfield('username');
        	$value['patientusername'] = $this->patientmember->join('lgq_user on lgq_user.id = lgq_patientmember.user_id')->where('lgq_user.id='.$value['patientmember_id'])->getfield('username');
        	$userst[]=$value;
        }
        // dump($userst);
		$this->assign("page", $page->show());
		$this->assign("list",$userst);
        $this->display('index');
    }
    
    //查看详细信息
    public function lookVideo()
	{
	
		$id = I('get.id',0,'intval');
		
		$video = $this->video->where('id='.$id)->order("id DESC")->find();
			
    	$video1 = $this->member->join('lgq_user on lgq_user.id = lgq_member.user_id')->where('lgq_user.id='.$video['member_id'])->field('username,name')->find();

    	$video2 = $this->patientmember->join('lgq_user on lgq_user.id = lgq_patientmember.user_id')->where('lgq_user.id='.$video['patientmember_id'])->field('username,name')->find();

    	$video['doctorusername'] = $video1['username'];
    	$video['doctorname'] = $video1['name'];
    	$video['patientusername'] = $video2['username'];
    	$video['patientname'] = $video2['name'];
        	
		$this->assign('video',$video);
		$this->display('lookVideo');
		
		
	}
	

}