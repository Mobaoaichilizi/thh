<?php
// +----------------------------------------------------------------------
// | 处方
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class RecipeController extends AdminbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->recipe = D("Recipe");
		$this->pre = D("Pre");
		$this->setting = D("Setting");
		$this->member = D("Member");
		$this->patient = D("Patientmember");
		$this->admin = D("Admin");
		$this->user = D("User");
		$this->hrebs = D("Hrebs");
		$this->pre_hrebs = D("PreHrebs");
		$this->personaddress = D("Personaddress");
		$this->single_hrebs = D("Single_hrebs");

	}

	//列表显示
    public function index(){
		$doctor = I('request.doctor');
		$setting_id_class = I('request.setting_id_class');
		$status = I('request.status');
		$admin_id=session('admin_id');
		$admin_count = $this->admin->where("id=".$admin_id." and is_pharmacy = 1")->count();
    	if($doctor){
	    	$doctor_id = $this->member->where("name='".$doctor."'")->getfield('user_id');
	    	$where['doctor_id'] = array('like',"%$doctor_id%");
    	}
    	if($status!==''){
    		$where['status'] = array('eq',$status);
    	}
    	if($admin_count > 0){
			$where['admin_pre'] = array('eq',$admin_id);
		}
		if($setting_id_class){
			$where['setting_id_class'] = array('eq',$setting_id_class);
		}
		$count=$this->pre->where($where)->count();
		$page = $this->page($count,11);
		$pre = $this->pre
            ->where($where)
            ->order("id DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
            foreach ($pre as $key => &$value) {
			

        	$value['doctorname'] = $this->member->where('user_id='.$value['doctor_id'])->getfield('name');
        	$value['patientname'] = $this->patient->where('user_id='.$value['patient_id'])->getfield('name');
	    	$value['type'] = $this->setting->where('id='.$value['setting_id_class'])->getfield('title');
       
        }
        
		$setting_status = $this->setting->where('parentid=71')->select();
		$this->assign('setting_status',$setting_status);
		$this->assign('admin_id',$admin_id);
		$this->assign('admin_count',$admin_count);
		$this->assign("page", $page->show());
		$this->assign("list",$pre);
        $this->display('index');
    }
    public function editRecipe()
	{
		$admin_id=session('admin_id');

		$pre_price = $this->admin->field('technical_price,frying_price,paste_price')->where("id=".$admin_id)->find();
		$admin_count = $this->admin->where("id=".$admin_id." and is_pharmacy = 1")->count();
		if(IS_POST){
			$id = I('post.id',0,'intval');
			$is_show = I('post.is_show',0,'intval');
			$price = I('post.price');//审核价格
			$dosage_name = I('post.dosage_name');
			$hrebs_number = I('post.hrebs_number','1');
			// $dosage = I('post.dosage');
			$status = $this->pre->where('id='.$id)->getfield('status');
			$hrebs_id = $this->pre->where('id='.$id)->getfield('hrebs_id');
			// $hrebs_total = $this->hrebs->where('id='.$hrebs_id)->getfield('hrebs_total');
			$hrebs_total = $price*$hrebs_number;
	
			if($status < 75 ){
				if($is_show == 2){
					$status = 78;
				}else if($is_show == 1){
					if($price == 0 || $_POST['yun_free'] == 0){
						$status = 73;
					}else{
						$status = 74;
					}
					
				}else{
					$status = 72;
				}
			}
			if($dosage_name == 131){
				$jx = $pre_price['technical_price'];
			}else if($dosage_name == 132){
				$jx = $pre_price['frying_price']*$hrebs_number;
			}else if($dosage_name == 133){
				$jx = $pre_price['paste_price'];
			}
			$all_price = $hrebs_total + $_POST['yun_free'] + $jx;
			$res = array(
				'id' => $id,
				'yun_free' => $_POST['yun_free'],
				'total' => $price,
				'is_show' => $is_show,
				'status' => $status,
				'total_price' => $all_price,
				'dosage_name' => $dosage_name,
				'process_price' => $jx,
			);
			$result=$this->pre->save($res);
			if($result!==false){
				//
				unset($res);
				$res = array(
					'id' => $hrebs_id,
					'hrebs_number' => $hrebs_number,
					'hrebs_total' => $hrebs_total,
				);
				$this->hrebs->save($res);
				//
				$this->success("修改成功！", U("Pre/index"));
			}else{
				$this->error("修改失败！");
			}

		}else
		{
		$id = I('get.id',0,'intval');//处方的ID
		$results = $this->pre->field("setting_id_class,setting_id_type,hrebs_id,patient_id,doctor_id,img_thumb_common,img_thumb_prime,pre_description,pre_bz,status,admin_pre,address_id,create_time,is_show,yun_free,dosage_name,total_price")->where("id = '".$id."'")->find();
		
		$patient = $this->patient->field('name,sex,age')->where("user_id='".$results['patient_id']."'")->find();
		if($results['address_id'] != NULL){
			$address = $this->personaddress->where('id='.$results['address_id'])->find();
		}
		$jgf = $this->admin->where("id=".$admin_id)->find();
		$result['dosage_name'] = $results['dosage_name']?$results['dosage_name']:131;
		$result['id'] = $id;
		$result['yun_free'] = $results['yun_free'];
		$result['pname'] = $patient['name'];
		$result['sex'] = $patient['sex'];
		$result['age'] = $patient['age'];
		$result['create_time'] = $results['create_time'];
		$result['phone'] =  $this->user->where("id=".$results['patient_id'])->getfield('username');
		$result['doctor'] = $this->member->where("user_id = ".$results['doctor_id'])->getfield('name');
		$result['img_thumb_prime'] = explode(",",$results['img_thumb_prime']);
		$result['img_thumb_common'] = explode(",",$results['img_thumb_common']);
		$result['pre_description'] = $results['pre_description'];
		$result['is_show'] = $results['is_show'];
		$result['setting_id_class'] = $results['setting_id_class'];
		$result['admin_pre'] = $this->admin->where('id='.$results['admin_pre'])->getfield('admin_name');
		$result['status'] = $this->setting->where('id='.$results['status'])->getfield('title');
		if(!empty($results['hrebs_id']) || ($results['hrebs_id']!=0)){
			$hrebs = $this->hrebs->field('setting_id_type,pre_hrebs_id,setting_id_usage,hrebs_number,hrebs_total')->where("id = '".$results['hrebs_id']."'")->find();
			$result['setting_id_usage'] = $this->setting->where("id='".$hrebs['setting_id_usage']."'")->getfield("title");
			if(!empty($hrebs['pre_hrebs_id'])){
				$res = $this->pre_hrebs->join("lgq_single_hrebs on lgq_single_hrebs.id = lgq_pre_hrebs.hrebs_name_id")->field('lgq_pre_hrebs.id,lgq_pre_hrebs.hrebs_name_id,lgq_pre_hrebs.hrebs_dosage,lgq_single_hrebs.unit_price')->where("lgq_pre_hrebs.id in (".$hrebs['pre_hrebs_id'].")")->select();
				// dump($res);
				$result['hrebs_number'] = $hrebs['hrebs_number'];
				$total = 0;
				foreach ($res as $key => &$value) {
					$value['hrebs_name_id'] = $this->single_hrebs->where("id='".$value['hrebs_name_id']."'")->getfield('hrebs_name');
					$total += $value['unit_price']*$value['hrebs_dosage']; 

				}
				if($result['dosage_name'] == 131){
					$jg = $jgf['technical_price'];
				}else if($result['dosage_name'] == 132){
					$jg = $jgf['frying_price']*$result['hrebs_number'];
				}else if($result['dosage_name'] == 133){
					$jg = $jgf['paste_price'];
				}
			}
			
				
			
		}

		
		$this->assign("result",$result);
		$this->assign("address",$address);
		$this->assign("res",$res);
		$this->assign("total",$total);
		$this->assign("admin_count",$admin_count);
		$this->assign("jg",$jg);
		$this->assign("jgf",$jgf);
		$this->display('editRecipe');

		}
		
	}
	 public function printRecipe()
	{
		$admin_id=session('admin_id');

		$pre_price = $this->admin->field('technical_price,frying_price,paste_price')->where("id=".$admin_id)->find();
		$admin_count = $this->admin->where("id=".$admin_id." and is_pharmacy = 1")->count();
		
		$id = I('get.id',0,'intval');//处方的ID
		$results = $this->pre->field("setting_id_class,setting_id_type,hrebs_id,patient_id,doctor_id,img_thumb_common,img_thumb_prime,pre_description,pre_bz,status,admin_pre,address_id,create_time,is_show,yun_free,dosage_name,total_price")->where("id = '".$id."'")->find();
		
		$patient = $this->patient->field('name,sex,age')->where("user_id='".$results['patient_id']."'")->find();
		if($results['address_id'] != NULL){
			$address = $this->personaddress->where('id='.$results['address_id'])->find();
		}
		$jgf = $this->admin->where("id=".$admin_id)->find();
		$result['bz'] = $results['pre_bz'];
		$result['dosage_name'] = $results['dosage_name']?$results['dosage_name']:131;
		$result['id'] = $id;
		$result['yun_free'] = $results['yun_free'];
		$result['pname'] = $patient['name'];
		$result['sex'] = $patient['sex'];
		$result['age'] = $patient['age'];
		$result['create_time'] = $results['create_time'];
		$result['phone'] =  $this->user->where("id=".$results['patient_id'])->getfield('username');
		$result['doctor'] = $this->member->where("user_id = ".$results['doctor_id'])->getfield('name');
		$result['img_thumb_prime'] = explode(",",$results['img_thumb_prime']);
		$result['img_thumb_common'] = explode(",",$results['img_thumb_common']);
		$result['pre_description'] = $results['pre_description'];
		$result['is_show'] = $results['is_show'];
		$result['setting_id_class'] = $results['setting_id_class'];
		$result['admin_pre'] = $this->admin->where('id='.$results['admin_pre'])->getfield('admin_name');
		$result['status'] = $this->setting->where('id='.$results['status'])->getfield('title');
		if(!empty($results['hrebs_id']) || ($results['hrebs_id']!=0)){
			$hrebs = $this->hrebs->field('setting_id_type,pre_hrebs_id,setting_id_usage,hrebs_number,hrebs_total,hrebs_bz')->where("id = '".$results['hrebs_id']."'")->find();
			$result['setting_id_usage'] = $this->setting->where("id='".$hrebs['setting_id_usage']."'")->getfield("title");
			if(empty($result['bz'])){
				$result['bz'] = $results['pre_description'];
				if(empty($result['bz'])){
					$result['bz'] = $hrebs['hrebs_bz'];
				}else if(!empty($result['hrebs_bz'])){
					$result['bz'] = $results['pre_description'].",".$hrebs['hrebs_bz'];
				}
			}
			if(!empty($hrebs['pre_hrebs_id'])){
				$res = $this->pre_hrebs->join("lgq_single_hrebs on lgq_single_hrebs.id = lgq_pre_hrebs.hrebs_name_id")->field('lgq_pre_hrebs.id,lgq_pre_hrebs.hrebs_name_id,lgq_pre_hrebs.hrebs_dosage,lgq_single_hrebs.unit_price')->where("lgq_pre_hrebs.id in (".$hrebs['pre_hrebs_id'].")")->select();
				// dump($res);
				$result['hrebs_number'] = $hrebs['hrebs_number'];
				$total = 0;
				foreach ($res as $key => &$value) {
					$value['hrebs_name_id'] = $this->single_hrebs->where("id='".$value['hrebs_name_id']."'")->getfield('hrebs_name');
					$total += $value['unit_price']*$value['hrebs_dosage']; 

				}
				if($result['dosage_name'] == 131){
					$jg = $jgf['technical_price'];
				}else if($result['dosage_name'] == 132){
					$jg = $jgf['frying_price']*$result['hrebs_number'];
				}else if($result['dosage_name'] == 133){
					$jg = $jgf['paste_price'];
				}
			}
			
				
			
	

		
		$this->assign("result",$result);
		$this->assign("address",$address);
		$this->assign("res",$res);
		$this->assign("total",$total);
		$this->assign("admin_count",$admin_count);
		$this->assign("jg",$jg);
		$this->assign("jgf",$jgf);
		$this->display('printRecipe');

		}
		
	}
	public function addHrebs(){
 		
 		if(IS_POST){
 			$pre_id = I('post.pre_id',0,'intval');
 			// $count = $this->pharmacy_hrebs->where("pharmacy_id=".$pharmacy_id." and hrebs_name_id=".$_POST['hrebs_name_id'])->count();
 			
 			// if($count > 0){
 			// 	$this->error('你已添加过此药材！');
 			// }
 			$hrebs_id = $this->pre->where('id='.$pre_id)->getfield('hrebs_id');
 			if($hrebs_id == 0 || $hrebs_id == NULL){
 				if($this->pre_hrebs->create()!==false){
 					$result=$this->pre_hrebs->add();
	 				if($result!==false){
	 					$res = array(
	 						'pre_hrebs_id' => $result,
	 					);
	 					$results = $this->hrebs->add($res);
	 					if($results!==false){
	 						$pre = array(
	 							'id' => $_POST['pre_id'],
	 							'hrebs_id' => $results,
	 						);
	 						$this->pre->save($pre);
	 						$this->success("添加成功！", U("Recipe/editRecipe"));
	 					}
	 				}
	 			}
 			}else{
 				if($this->pre_hrebs->create()!==false){
 					$result=$this->pre_hrebs->add();
 					if($result!==false){
 						$pre_hrebs_id = $this->hrebs->where('id='.$hrebs_id)->getfield('pre_hrebs_id');
 						if($pre_hrebs_id!== ''){
 							$pre_hrebs_id.=",".$result;
 						}else{
 							$pre_hrebs_id.=$result;
 						}
	 					$res = array(
	 						'id' => $hrebs_id, 
	 						'pre_hrebs_id' => $pre_hrebs_id,
	 					);
	 					$results = $this->hrebs->save($res);
	 					$this->success("添加成功！", U("Recipe/editRecipe"));
	 				}

	 			}
	 		}
			
		}else{
			$pre_id = I('get.id',0,'intval');
			$admin_id=session('admin_id');
			$admin_count = $this->admin->where("id=".$admin_id." and is_pharmacy = 1")->count();

			$where = '';
			if($admin_count > 0){
				$where.=" and admin_id=".$admin_id;
			}else{
				$where.=" and admin_id=74";
			}
			$setting_id_class=$this->pre->where('id='.$pre_id)->getfield('setting_id_class');
			$img_thumb_common=$this->pre->where('id='.$pre_id)->getfield('img_thumb_common');
			$setting_id_type=$this->pre->where('id='.$pre_id)->getfield('setting_id_type');
			if($setting_id_class == 44){
				if($img_thumb_common!=NULL){
					$where.=" and setting_id_model=155";
				}else{
					$where.=" and setting_id_model=156";
				}
			}else{
				if($setting_id_type==155){
					$where.=" and setting_id_model=155";
				}else if($setting_id_type==156){
					$where.=" and setting_id_model=156";
				}else if($setting_id_type==51){
					$where.=" and setting_id_model=51";
				}
			}
			$where.=" and status=1";
	 		//abc
	 		$abca = $this->single_hrebs->where("firstletter in ('a','b','c')".$where)->order('firstletter asc')->select();
	 		$abc='';
	 		foreach ($abca as $key => $value) {
	 			$abc.="@".$value['id']."|".$value['hrebs_name'];
	 		}
	 		//def
	 		$defa = $this->single_hrebs->where("firstletter in ('d','e','f')".$where)->order('firstletter asc')->select();
	 		$def='';
	 		foreach ($defa as $key => $value) {
	 			$def.="@".$value['id']."|".$value['hrebs_name'];
	 		}
	 		//ghi
	 		$ghia = $this->single_hrebs->where("firstletter in ('g','h','i')".$where)->order('firstletter asc')->select();
	 		$ghi='';
	 		foreach ($ghia as $key => $value) {
	 			$ghi.="@".$value['id']."|".$value['hrebs_name'];
	 		}
	 		//jkl
	 		$jkla = $this->single_hrebs->where("firstletter in ('j','k','l')".$where)->order('firstletter asc')->select();
	 		$jkl='';
	 		foreach ($jkla as $key => $value) {
	 			$jkl.="@".$value['id']."|".$value['hrebs_name'];
	 		}
	 		//mno
	 		$mnoa = $this->single_hrebs->where("firstletter in ('m','n','o')".$where)->order('firstletter asc')->select();
	 		$mno='';
	 		foreach ($mnoa as $key => $value) {
	 			$mno.="@".$value['id']."|".$value['hrebs_name'];
	 		}
	 		//pqr
	 		$pqra = $this->single_hrebs->where("firstletter in ('p','q','r')".$where)->order('firstletter asc')->select();
	 		$pqr='';
	 		foreach ($pqra as $key => $value) {
	 			$pqr.="@".$value['id']."|".$value['hrebs_name'];
	 		}
	 		//stu
	 		$stua = $this->single_hrebs->where("firstletter in ('s','t','u')".$where)->order('firstletter asc')->select();
	 		$stu='';
	 		foreach ($stua as $key => $value) {
	 			$stu.="@".$value['id']."|".$value['hrebs_name'];
	 		}
	 		//vwx
	 		$vwxa = $this->single_hrebs->where("firstletter in ('v','w','x')".$where)->order('firstletter asc')->select();
	 		$vwx='';
	 		foreach ($vwxa as $key => $value) {
	 			$vwx.="@".$value['id']."|".$value['hrebs_name'];
	 		}
	 		//yz
	 		$yza = $this->single_hrebs->where("firstletter in ('y','z')".$where)->order('firstletter asc')->select();
	 		$yz='';
	 		foreach ($yza as $key => $value) {
	 			$yz.="@".$value['id']."|".$value['hrebs_name'];
	 		}
	 		$this->assign('abc',$abc);
	 		$this->assign('def',$def);
	 		$this->assign('ghi',$ghi);
	 		$this->assign('jkl',$jkl);
	 		$this->assign('mno',$mno);
	 		$this->assign('pqr',$pqr);
	 		$this->assign('stu',$stu);
	 		$this->assign('vwx',$vwx);
	 		$this->assign('yz',$yz);
	 		$this->assign('pre_id',$pre_id);
	 		
	 		$this->display("addHrebs");
	 	}
 	}

  public function pre_operate()
	{
		$id = I('post.id',0,'intval');
		$status = I('post.status',0,'intval');
		$admin_id=session('admin_id');
		$rate = $this->admin->where("id=".$admin_id)->getField('rate');
		$platform = $this->admin->where("id=".$admin_id)->getField('platform');
		$dataarray=array(
			'id' => $id,
			'status' => $status,
		);
		if($this->pre->save($dataarray)!==false) {
			if($status == "77"){
				//医生提成（药房回报率）
				$hrebs_total = $this->pre->where("id=".$id)->getField('total');//处方价格
				$total_price = $this->pre->where("id=".$id)->getField('total_price');//处方价格
				$doctor_id = $this->pre->where("id=".$id)->getField('doctor_id');
				$bala = $hrebs_total*$rate/100;
				setpoints($doctor_id,1,$bala,'元','207',0);
				$ba = $this->user->where('id='.$doctor_id)->getField('balance');
				financial_log($doctor_id,$bala,3,$ba,'处方分佣',8);
				//平台分佣
				$platform_money = $hrebs_total*$platform/100;
				$pharmacy_money = $total_price - $bala - $platform_money;
				$result = $this->admin->where("id=".$admin_id)->setInc('balance',$pharmacy_money);

			}
			$this->success("修改成功！");
		} else {
			$this->error("修改失败！");
		}
	}
	
	public function delHrebs(){
		$pre_id = I('post.pre_id');
		$id = I('post.id');
		$hrebs_id = $this->pre->where('id='.$pre_id)->getfield('hrebs_id');
		$pre_hrebs_id = $this->hrebs->where('id='.$hrebs_id)->getfield('pre_hrebs_id');
		$pre_hrebs_arr = explode(',', $pre_hrebs_id);
		foreach( $pre_hrebs_arr as $k=>&$v) {
		    if($id == $v) {
		    	unset($pre_hrebs_arr[$k]);
		    }
		}
		$pre_hrebs = implode(',', $pre_hrebs_arr);
		$res = array(
			'id'  => $hrebs_id,
			'pre_hrebs_id' => $pre_hrebs,
		);
		$result = $this->hrebs->save($res);
		if($result !== false){
			$this->success("删除成功！");
		}else{
			$this->error("删除失败！");
		}


	}
	public function btn_export(){
	 	$xlsName  = "Pre";
	 	$id = I('get.id');
        $xlsCell  = array(
            array('id','处方id'),
            array('setting_id_model','药材类型'),
            array('hrebs_name','药材名'),
            array('unit_price','单价'),
            array('hrebs_dosage','数量'),
            array('single_total','小计'),
            array('dosage_name','剂型'),
        );
        $xlsModel = $this->pre;
        $result  = $xlsModel->where('id='.$id)->getField('hrebs_id');
        $dosage_name  = $xlsModel->where('id='.$id)->getField('dosage_name');
        if($dosage_name == '131'){
        	$dosage_name = '原药';
        }else if($dosage_name == '132'){
        	$dosage_name = '汤剂代煎';
        }else if($dosage_name == '133'){
        	$dosage_name = '膏方制作';
        }
        $pre_hrebs = $this->hrebs->where('id='.$result)->getField('pre_hrebs_id');
        $xlsData = $this->pre_hrebs->join("lgq_single_hrebs on lgq_single_hrebs.id = lgq_pre_hrebs.hrebs_name_id")->field('lgq_single_hrebs.setting_id_model,lgq_pre_hrebs.hrebs_dosage,lgq_single_hrebs.unit_price,lgq_single_hrebs.hrebs_name')->where("lgq_pre_hrebs.id in (".$pre_hrebs.")")->select();
        foreach ($xlsData as $k => &$v) {
        	$v['id'] = $id;
        	if($v['setting_id_model'] == '155'){
        		$v['setting_id_model'] = "标准药材";
        	}else if($v['setting_id_model'] == '156'){
        		$v['setting_id_model'] = "精品药材";
        	}else if($v['setting_id_model'] == '51'){
        		$v['setting_id_model'] = "配方颗粒";
        	}
        	$v['single_total'] = $v['unit_price'] * $v['hrebs_dosage'];
        	
        	$v['dosage_name'] = $dosage_name;
        }
        exportExcel($xlsName,$xlsCell,$xlsData);
	}  

}