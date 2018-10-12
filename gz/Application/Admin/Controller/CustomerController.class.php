<?php
// +----------------------------------------------------------------------
// | 会员登录
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class CustomerController extends AdminbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->customer = D("member");
		$this->user     = D("user");
		$this->hospital = D('Hospital');
		$this->department = D('Department');
		$this->setting = D('Setting');

	}

	//列表显示
    public function index(){
    	$name = I('request.name');
    	$member_level = I('request.member_level');
    	$status = I('request.status');
		if($name){
			$where['lgq_member.name'] = array('like',"%$name%");
			$where['lgq_user.role'] = array('eq',1);
		}
		if($member_level!==''){
			$where['lgq_user.member_level'] = array('eq',$member_level);
		}
		if($status!==''){
			$where['lgq_member.status'] = array('eq',$status);
		}
		$count=$this->customer->join("lgq_user on lgq_user.id = lgq_member.user_id")->where($where)->count();
		$page = $this->page($count,11);
		$customer = $this->customer->join("lgq_user on lgq_user.id = lgq_member.user_id")->join("left join lgq_department on lgq_department.id = lgq_member.department_id")->join("left join lgq_hospital on lgq_hospital.id = lgq_member.hospital_id")->field("lgq_department.name as departmentname,lgq_hospital.name as hospitalname,lgq_user.username,lgq_user.member_level,lgq_user.createtime,lgq_member.*")
            ->where($where)
            ->order("lgq_member.id DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
       
		$this->assign("page", $page->show());
		$this->assign("customerlist",$customer);
        $this->display('index');
    }
    
public function btn_export(){
	 	$xlsName  = "Doctor";
        $xlsCell  = array(
            array('id','序列'),
            array('name','姓名'),
            array('sex','性别'),
            array('username','电话'),
            array('card','身份证号码'),
            array('department','科室'),
            array('hospital','医院'),
            array('member_level','会员'),
            array('status','状态'),
            array('createtime','注册时间')
        );
        $xlsModel = $this->customer;
        $xlsData  = $xlsModel->Field('id,user_id,name,sex,status,card,department_id,hospital_id')->select();
        foreach ($xlsData as $k => &$v) {
        	$v['username'] = $this->user->where('id='.$v['user_id'])->getField('username');
        	$v['member_level'] = $this->user->where('id='.$v['user_id'])->getField('member_level');
        	if(!empty($v['hospital_id'])){
        		$v['hospital'] = $this->hospital->where('id='.$v['hospital_id'])->getField('name');
        	}
        	if(!empty($v['department_id'])){
        		$v['department'] = $this->department->where('id='.$v['department_id'])->getField('name');
        	}
        	
        
        	if($v['status'] == 0){
        		$v['status'] = "未审核";
        	}else if($v['status'] == 1){
        		$v['status'] = "审核通过";
        	}else if($v['status'] == 2){
        		$v['status'] = "审核中";
        	}else if($v['status'] == 3){
        		$v['status'] = "审核未通过";
        	}
        	if($v['sex'] == 1){
        		$v['sex'] = "女";
        	}else if($v['sex'] == 2){
        		$v['sex'] = "男";
        	}else if($v['sex'] == 0){
        		$v['sex'] = "未知";
        	}
        	if($v['member_level'] == 1){
        		$v['member_level'] = "VIP";
        	}else if($v['member_level'] == 2){
        		$v['member_level'] = "SVIP";
        	}else if($v['member_level'] == 0){
        		$v['member_level'] = "非会员";
        	}
        	$v['createtime'] = $this->user->where('id='.$v['user_id'])->getField('createtime');
        	$v['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
        }
        exportExcel($xlsName,$xlsCell,$xlsData);
	}  
	
 
		
	// }
    //查看详细信息
    public function lookCustomer()
	{
		if(IS_POST)
		{
			$id = I('post.id',0,'intval');
			$user_id = I('post.user_id',0,'intval');
			$balance = I('post.balance');
			$operation = I('post.operation');
			$professional = I('post.professional');
			$hospital_id = I('post.hospital_id');
			$department_id = I('post.department_id');
			$res = array(
						"id" => $id,
						"status"=> $_POST['status'],
						'professional' => $professional,
						'hospital_id' => $hospital_id,
						'department_id' => $department_id,
					);
			$ress = array(
						"id" => $_POST['user_id'],
						"member_level"=>$_POST['member_level'],
			);
			if($this->customer->create()!==false) {
				if ($customer!==false) {
					$customer=$this->customer->save($res);
					$level = $this->user->save($ress);
					$result=$this->user->where("id=".$user_id)->find();
					if($balance!='')
					{
						if($operation=="+")
						{
							$this->user->where("id=".$user_id)->setInc('balance',$balance);
							$data=array(
					           'user_id'    => $user_id, 
					           'role'   => '1',
					           'score'  => $balance,
					           'content' => '元',
					           'setting_id'  => '167',
					           'optime' => time(),
						    );
						    M('ScoreList')->add($data);   
							
							$ba = $this->user->where('id='.$user_id)->getField('balance');
							financial_log($user_id,$balance,3,$ba,'系统后台操作',8);
						}else if($operation=="-")
						{
							if($result['balance']-$balance < 0)
							{
								$this->error('充值以后余额不能为负数!');
							}else
							{
								$this->user->where("id=".$user_id)->setDec('balance',$balance);
								$ba = $this->user->where('id='.$user_id)->getField('balance');
								financial_log($user_id,$balance,3,$ba,'系统后台操作',8);
							}
						}
					}
					$this->success("编辑成功！", U("customer/index"));
				}else
				{
					$this->error('编辑失败!');
				}
			}
		}else
		{
			$id = I('get.id',0,'intval');
			$customer = $this->customer->join("lgq_user on lgq_user.id = lgq_member.user_id")->join("left join lgq_department on lgq_department.id = lgq_member.department_id")->join("left join lgq_hospital on lgq_hospital.id = lgq_member.hospital_id")->field("lgq_department.name as departmentname,lgq_hospital.name as hospitalname,lgq_user.username,lgq_user.member_level,role,lgq_member.*")->where("lgq_member.id=".$id)->find();
			if(!empty($customer['othercert'])){
				$customer['othercert'] = explode(",", $customer['othercert']);
			}
			$this->assign('customer',$customer);
			$this->display('lookCustomer');
		}


		
		
		
	}
public function audit()
	{

		$status = I('post.status');
		$id = I('post.id');
		$res = array(
			"id"     => $id,
			"stauts" => $stauts,
		);
		$result = $this->customer->save($res);
		$this->display('index');
	}	

public function free_clinic(){
	if($_POST){
		$begin_time = strtotime(I('post.begin_time'));
		$end_time = strtotime(I('post.end_time'));
		$ids = I('post.menuid');
		$ids = implode(',',$ids);
		if($end_time < $begin_time){
			$this->error('结束时间必须大于开始时间！');
		}else if($end_time == $begin_time){
			$end_time = $begin_time + 86400-1;
		}
		if($ids){
			$res = array(
				'begin_time' => $begin_time,
				'end_time'   => $end_time,
 			);
			$this->customer->where("id in (".$ids.")")->save($res);
			$this->success("保存成功");
		}else{
			$this->success("保存成功");
		}
	}
	else{

		$result = $this->customer->where('status=1')->order(array("id" => "desc"))->select();
		$time = time();
		foreach ($result as $key => &$value) {
			if(($value['begin_time'] == 0 && $value['end_time'] == 0) || $time < $value['begin_time']){
				$value['clinic_status'] =0;
			}else if($time >= $value['begin_time'] && $time <= $value['end_time']){
				$value['clinic_status'] =1;
			}else if($time > $value['end_time']){
				$value['clinic_status'] =2;
			}
		}
		$this->assign('result',$result);
		$this->display('freeClinic');
	}
}  
	
public function editCustomer()
	{
		if(IS_POST)
		{
			$id = I('post.id',0,'intval');
			$user_id = I('post.user_id',0,'intval');
			$balance = I('post.balance');
			$operation = I('post.operation');
			$professional = I('post.professional');
			$name = I('post.username');
			$age = I('post.age');
			$sex = I('post.sex');
			$card = I('post.card');
			$hospital_id = I('post.hospital_id');
			$department_id = I('post.department_id');
			$is_position = I('post.is_position');
			$img_thumb = I('post.img_thumb');
			$certificate = I('post.certificate');
			$card_correct = I('post.card_correct');
			$card_opposite = I('post.card_opposite');
			$qualification = I('post.qualification');
			$highest_professional = I('post.highest_professional');
			$res = array(
						"id" => $id,
						"status"=> $_POST['status'],
						'professional' => $professional,
						'hospital_id' => $hospital_id,
						'department_id' => $department_id,
						'name' => $name,
						'age' => $age,
						'card' => $card,
						'sex' => $sex,
						'is_position' => $is_position,
						'img_thumb'=>$img_thumb,
						'certificate'=>$certificate,
						'card_correct'=>$card_correct,
						'card_opposite'=>$card_opposite,
						'qualification'=>$qualification,
						'highest_professional'=>$highest_professional,
					);
			$ress = array(
						"id" => $_POST['user_id'],
						"member_level"=>$_POST['member_level'],
			);
			if($this->customer->create()!==false) {
				if ($customer!==false) {
					$customer=$this->customer->save($res);
					$level = $this->user->save($ress);
					$result=$this->user->where("id=".$user_id)->find();
					if($balance!='')
					{
						if($operation=="+")
						{
							$this->user->where("id=".$user_id)->setInc('balance',$balance);
							$data=array(
					           'user_id'    => $user_id, 
					           'role'   => '1',
					           'score'  => $balance,
					           'content' => '元',
					           'setting_id'  => '167',
					           'optime' => time(),
						    );
						    M('ScoreList')->add($data);   
							
							$ba = $this->user->where('id='.$user_id)->getField('balance');
							financial_log($user_id,$balance,3,$ba,'系统后台操作',8);
						}else if($operation=="-")
						{
							if($result['balance']-$balance < 0)
							{
								$this->error('充值以后余额不能为负数!');
							}else
							{
								$this->user->where("id=".$user_id)->setDec('balance',$balance);
								$ba = $this->user->where('id='.$user_id)->getField('balance');
								financial_log($user_id,$balance,3,$ba,'系统后台操作',8);
							}
						}
					}
					$this->success("编辑成功！", U("customer/index"));
				}else
				{
					$this->error('编辑失败!');
				}
			}
		}else
		{
			$id = I('get.id',0,'intval');
			$customer = $this->customer->join("lgq_user on lgq_user.id = lgq_member.user_id")->field("lgq_user.username,lgq_user.member_level,role,lgq_member.*")->where("lgq_member.id=".$id)->find();
			if(!empty($customer['othercert'])){
				$customer['othercert'] = explode(",", $customer['othercert']);
			}
			$hospital = $this->hospital->field('id,name')->order("sort asc")->select();
			$department = $this->department->field('id,name')->order("sort asc")->select();
			$this->assign('customer',$customer);
			$this->assign('hospital',$hospital);
			$this->assign('department',$department);
			$this->display('editCustomer');
		}


		
		
		
	}
	public function editworkCustomer()
	{
		if(IS_POST)
		{

			$id = I('post.id',0,'intval');
			$user_id = I('post.user_id',0,'intval');
			$profile = I('post.profile');
			$disease = I('post.disease');
			$othercert = I('post.othercert');
			$address = I('post.address');
			$graphic_price = I('post.graphic_price');
			$telephone_price = I('post.telephone_price');
			$reserve_price = I('post.reserve_price');
			$res = array(
						"id" => $id,
						'profile' => $profile,
						'disease' => $disease,
						'othercert' => $othercert,
						'address' => $address,
						'graphic_price' => $graphic_price,
						'telephone_price' => $telephone_price,
						'reserve_price' => $reserve_price,
					);
			
			if($this->customer->create()!==false) {
				if ($customer!==false) {
					$customer=$this->customer->save($res);
					
					$this->success("编辑成功！", U("customer/index"));
				}else
				{
					$this->error('编辑失败!');
				}
			}
		}else
		{
			$id = I('get.id',0,'intval');
			$customer = $this->customer->where("id=".$id)->find();
			// if(!empty($customer['othercert'])){
			// 	$customer['othercert'] = explode(",", $customer['othercert']);
			// }
			
			$this->assign('customer',$customer);
			$this->display('editworkCustomer');
		}


		
		
		
	}

	public function saveAccess()
	{
		if(IS_POST)
		{
			$doctor_id = I("post.doctor_id",0,'intval');
			$menuid =$_POST['menid'];
			$settimg_id = implode(",",$menuid);
		
			if(!$doctor_id){
    			$this->error("需要授权的医生不存在！");
    		}
    		$res = array(
    			"settimg_id" => $settimg_id,
    		);
    		$result = $this->customer->where("user_id=".$doctor_id)->save($res);
			if ($result!=false) {
    
    			$this->success("设置成功！", U("Customer/editCustomer"));
    		}
    		
		}else
		{
			$doctor_id = I('get.id',0,'intval');
			
			$selected=$this->customer->where(array("user_id"=>$doctor_id))->getField("settimg_id");//获取权限表数据
			$selected = explode(',', $selected);
			$result = $this->setting->where('id=82 or id=88')->order(array("sort" => "ASC"))->select();
			
			foreach ($result as &$value) {
				$value['child'] = $this->setting->where("parentid =".$value['id'])->order(array("sort" => "ASC"))->select();
			}
			
			$this->assign("categorys", $categorys);
			$this->assign("result", $result);
			$this->assign('doctor_id',$doctor_id);
			$this->assign('selected',$selected);
			$this->display('saveAccess');
		}
		
	}


	private function _is_checked($menu, $roleid, $priv_data) {
    	
    	$menuid=$menu['id'];
    	if($priv_data){
	    	if (in_array($menuid, $priv_data)) {
	    		return true;
	    	} else {
	    		return false;
	    	}
    	}else{
    		return false;
    	}
    	
    }
    protected function _get_level($id, $array = array(), $i = 0) {
        
		if ($array[$id]['parentid']==0 || empty($array[$array[$id]['parentid']]) || $array[$id]['parentid']==$id){
			return  $i;
		}else{
			$i++;
			return $this->_get_level($array[$id]['parentid'],$array,$i);
		}
        		
    }
	
}