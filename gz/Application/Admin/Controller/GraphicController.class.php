<?php
// +----------------------------------------------------------------------
// | 图文咨询
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class GraphicController extends AdminbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->graphic = D("graphic");
		$this->user = D("user");
		$this->member = D("member");
		$this->patientmember = D("patientmember");

	}

	//列表显示
    public function index(){
    	$status = I('request.status');
		if($status){
			


			$where['status'] = array('eq',$status);
		}
		$count=$this->graphic->where($where)->count();
		$page = $this->page($count,11);
		$graphic = $this->graphic
            ->where($where)
            ->order("lgq_graphic.id DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
        foreach ($graphic as  $value) {
			$value['img_thumb'] = explode(",", $value['img_thumb']);
			// foreach ($value['img_thumb'] as $k => &$v) {
			// 	$v=$http_img_url.$v;
			// }
        	$value['username'] = $this->member->join('lgq_user on lgq_user.id = lgq_member.user_id')->where('lgq_user.id='.$value['member_id'])->getfield('username');
        	$value['patientusername'] = $this->patientmember->join('lgq_user on lgq_user.id = lgq_patientmember.user_id')->where('lgq_user.id='.$value['patientmember_id'])->getfield('username');
        	$userst[]=$value;
        }   
       
		$this->assign("page", $page->show());
		$this->assign("list",$userst);
        $this->display('index');
    }
    
    //查看详细信息
    public function lookgraphic()
	{
	
		$id = I('get.id',0,'intval');
		$graphic = $this->graphic->where('id='.$id)->order("id DESC")->find();
			
    	$graphic1 = $this->member->join('lgq_user on lgq_user.id = lgq_member.user_id')->where('lgq_user.id='.$graphic['member_id'])->field('username,name')->find();

    	$graphic2 = $this->patientmember->join('lgq_user on lgq_user.id = lgq_patientmember.user_id')->where('lgq_user.id='.$graphic['patientmember_id'])->field('username,name')->find();

    	$graphic['doctorusername'] = $graphic1['username'];
    	$graphic['doctorname'] = $graphic1['name'];
    	$graphic['patientusername'] = $graphic2['username'];
    	$graphic['patientname'] = $graphic2['name'];
		$this->assign('graphic',$graphic);
		$this->display('lookGraphic');
		
		
	}
	

}