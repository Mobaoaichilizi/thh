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
		$status = I('post.status');
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
			$order_statuss = I('post.order_statuss');
			switch($order_statuss){
 
				case '待审核':
				 $status = 72;
				 
				break;
				 
				case '核价中':
				 $status = 73;
				 
				break;
				 
				case '待支付':
				 $status = 74;
				break;
				case '待发货':
				 $status = 75;
				break;
				case '待收货':
				 $status = 76;
				break;
				case '已完成':
				 $status = 77;
				break;
				case '审核不通过':
				 $status = 78;
				break;
				default:
				 
				}
			if($order_statuss == '待审核' || $order_statuss == '核价中' ){
				if($is_show == 2){
					$status = 78;
				}else if($is_show == 1){
					if($price == 0){
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
				$jx = $pre_price['frying_price'];
			}else if($dosage_name == 133){
				$jx = $pre_price['paste_price'];
			}
			$all_price = $price + $_POST['yun_free'] + $jx;
			$res = array(
				'id' => $id,
				'yun_free' => $_POST['yun_free'],
				'total' => $price,
				'is_show' => $is_show,
				'status' => $status,
				'total_price' => $all_price,
				'dosage_name' => $dosage_name,
			);
			$result=$this->pre->save($res);
			if($result!==false){
				$this->success("修改成功！", U("Pre/index"));
			}else{
				$this->error("修改失败！");
			}

		}else
		{
		$id = I('get.id',0,'intval');//处方的ID
		$results = $this->pre->field("setting_id_class,setting_id_type,hrebs_id,patient_id,doctor_id,img_thumb_common,img_thumb_prime,pre_description,pre_bz,status,admin_pre,address_id,create_time,is_show,yun_free")->where("id = '".$id."'")->find();
		
		$patient = $this->patient->field('name,sex,age')->where("user_id='".$results['patient_id']."'")->find();
		if($results['address_id'] != NULL){
			$address = $this->personaddress->where('id='.$results['address_id'])->find();
		}
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
			$hrebs = $this->hrebs->field('setting_id_type,pre_hrebs_id,setting_id_usage,hrebs_number')->where("id = '".$results['hrebs_id']."'")->find();
			$result['setting_id_usage'] = $this->setting->where("id='".$hrebs['setting_id_usage']."'")->getfield("title");
			if(!empty($hrebs['pre_hrebs_id'])){
				$res = $this->pre_hrebs->join("lgq_single_hrebs on lgq_single_hrebs.id = lgq_pre_hrebs.hrebs_name_id")->field('lgq_pre_hrebs.id,lgq_pre_hrebs.hrebs_name_id,lgq_pre_hrebs.hrebs_dosage,lgq_single_hrebs.unit_price')->where("lgq_pre_hrebs.id in (".$hrebs['pre_hrebs_id'].")")->select();
				$result['hrebs_number'] = $hrebs['hrebs_number'];
				$total = 0;
				foreach ($res as $key => &$value) {
					$value['hrebs_name_id'] = $this->single_hrebs->where("id='".$value['hrebs_name_id']."'")->getfield('hrebs_name');
					$total += $value['unit_price']*$value['hrebs_dosage']; 

				}
			}
			
				
			
		}

		
		
		$this->assign("result",$result);
		$this->assign("address",$address);
		$this->assign("res",$res);
		$this->assign("total",$total);
		$this->assign("admin_count",$admin_count);
		$this->display('editRecipe');

		}
		
	}
	public function addHrebs(){
 		$pre_id = I('get.id',0,'intval');
 		if(IS_POST){
 			// $count = $this->pharmacy_hrebs->where("pharmacy_id=".$pharmacy_id." and hrebs_name_id=".$_POST['hrebs_name_id'])->count();
 			
 			// if($count > 0){
 			// 	$this->error('你已添加过此药材！');
 			// }
 			$hrebs_id = $this->pre->where('id='.$pre_id)->getfield('hrebs_id');
			
			print_r($hrebs_id);
			exit;
 			if($hrebs_id == 0){
 				// if($this->pre_hrebs->create()!==false){
 				// 	$result=$this->pre_hrebs->add();
	 			// 	if($result!==false){
	 			// 		$res = array(
	 			// 			'pre_hrebs_id' => $result,
	 			// 		);
	 			// 		$results = $this->hrebs->add($res);
	 			// 		if($results!==false){
	 			// 			$pre = array(
	 			// 				'id' => $_POST['pre_id'],
	 			// 				'hrebs_id' => $results,
	 			// 			);
	 			// 			$this->pre->save($pre);
	 			// 			$this->success("添加成功！", U("Recipe/editRecipe"));
	 			// 		}
	 			// 	}
	 			// }
 			}else{
 				if($this->pre_hrebs->create()!==false){
 					$result=$this->pre_hrebs->add();
 					if($result!==false){
 						$pre_hrebs_id = $this->hrebs->where('id='.$hrebs_id)->getfield('pre_hrebs_id');
 						$pre_hrebs_id.=",".$result;
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
				if($img_thumb_common!==NULL){
					$where.=" and setting_id_model=155";
				}else{
					$where.=" and setting_id_model=156";
				}
			}else{
				if($setting_id_type==155){
					$where.=" and setting_id_model=155";
				}else if($setting_id_type==156){
					$where.=" and setting_id_model=156";
				}else{
					$where.=" and setting_id_model=51";
				}
			}
			 
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
	 			$def.="@".$value['id']."|".$value['hrebs_name'];
	 		}
	 		//jkl
	 		$jkla = $this->single_hrebs->where("firstletter in ('j','k','l')".$where)->order('firstletter asc')->select();
	 		$jkl='';
	 		foreach ($jkla as $key => $value) {
	 			$def.="@".$value['id']."|".$value['hrebs_name'];
	 		}
	 		//mno
	 		$mnoa = $this->single_hrebs->where("firstletter in ('m','n','o')".$where)->order('firstletter asc')->select();
	 		$mno='';
	 		foreach ($mnoa as $key => $value) {
	 			$def.="@".$value['id']."|".$value['hrebs_name'];
	 		}
	 		//pqr
	 		$pqra = $this->single_hrebs->where("firstletter in ('p','q','r')".$where)->order('firstletter asc')->select();
	 		$pqr='';
	 		foreach ($pqra as $key => $value) {
	 			$def.="@".$value['id']."|".$value['hrebs_name'];
	 		}
	 		//stu
	 		$stua = $this->single_hrebs->where("firstletter in ('s','t','u')".$where)->order('firstletter asc')->select();
	 		$stu='';
	 		foreach ($stua as $key => $value) {
	 			$def.="@".$value['id']."|".$value['hrebs_name'];
	 		}
	 		//vwx
	 		$vwxa = $this->single_hrebs->where("firstletter in ('v','w','x')".$where)->order('firstletter asc')->select();
	 		$vwx='';
	 		foreach ($vwxa as $key => $value) {
	 			$def.="@".$value['id']."|".$value['hrebs_name'];
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
		$dataarray=array(
			'id' => $id,
			'status' => $status,
		);
		if ($this->pre->save($dataarray)!==false) {
			$this->success("修改成功！");
		} else {
			$this->error("修改失败！");
		}
	}
	

}