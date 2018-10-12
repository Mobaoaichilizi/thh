<?php
// +----------------------------------------------------------------------
// | 药房
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class PharmacyController extends AdminbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->pharmacy = D("Pharmacy");
		$this->user = D("User");
		$this->patient = D("Patientmember");
		$this->setting = D("Setting");
		$this->hrebs = D("SingleHrebs");
		$this->admin = D("Admin");
		$this->pharmacy_hrebs = D("PharmacyHrebs");

	}

	//列表显示
    public function index(){
    	$order_status = I('request.order_status');
    	$setting_id = I('request.setting_id');
    	if($order_status){
    		$where['order_status'] = array('eq',$order_status);
    	}
    	if($setting_id){
    		$where['setting_id'] = array('eq',$setting_id);
    	}
		$count=$this->pharmacy->where($where)->count();
		$page = $this->page($count,11);
		$pharmacy = $this->pharmacy
			->where($where)
            ->order("id DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
		foreach($pharmacy as &$row)
		{
			$row['username']=$this->user->where("id=".$row['user_id'])->getField('username');
			$row['setting_name']=$this->setting->where("id=".$row['setting_id'])->getField('title');
			$row['order_status']=$this->setting->where("id=".$row['order_status'])->getField('title');
		}
		$categories = $this->setting->where('parentid = 66')->select();
		$status = $this->setting->where('parentid = 71')->select();
		$this->assign("page", $page->show());
		$this->assign("pharmacylist",$pharmacy);
		$this->assign('categories',$categories);
		$this->assign('status',$status);
        $this->display('index');
    }
	public function editPharmacy()
	{
		if(IS_POST){
			$id = I('post.id',0,'intval');
			$is_show = I('post.is_show',0,'intval');
			$price = I('post.price');
			// $yun_free = I('post.yun_free');
			$order_statuss = I('post.order_statuss');
			$setting_id_dostype = I('post.setting_id_dostype');
			$hrebs_number = I('post.hrebs_number');
			$admin_id=session('admin_id');
			$pre_price = $this->admin->field('technical_price,frying_price,paste_price')->where("is_default=1")->find();
			
			$yun_free = $this->admin->where("is_default=1")->getField('yun_free');
			$hrebs_total = $price*$hrebs_number;
			
			switch($order_statuss){
 
				case '待审核':
				 $order_status = 72;
				 
				break;
				 
				case '核价中':
				 $order_status = 73;
				 
				break;
				 
				case '待支付':
				 $order_status = 74;
				break;
				case '待发货':
				 $order_status = 75;
				break;
				case '待收货':
				 $order_status = 76;
				break;
				case '已完成':
				 $order_status = 77;
				break;
				case '审核不通过':
				 $order_status = 78;
				break;
				default:
				 
				}
			if($order_statuss == '待审核' || $order_statuss == '核价中' ){
				if($is_show == 2){
					$order_status = 78;
				}else if($is_show == 1){
					if($price == 0){
						$order_status = 73;
					}else{
						$order_status = 74;
					}
					
				}else{
					$order_status = 72;
				}
			}
			if($setting_id_dostype == 131){
				$jx = $pre_price['technical_price'];
			}else if($setting_id_dostype == 132){
				$jx = $pre_price['frying_price']*$hrebs_number;
			}else if($setting_id_dostype == 133){
				$jx = $pre_price['paste_price']*$hrebs_number;
			}
			$all_price = $hrebs_total + $yun_free + $jx;
			$res = array(
				'id' => $id,
				'price' => $hrebs_total,
				'is_show' => $is_show,
				'order_status' => $order_status,
				'all_price' => $all_price,
				'process_price' => $jx,
				'setting_id_dostype' => $setting_id_dostype,
				'hrebs_number' => $hrebs_number,
			);
			$result=$this->pharmacy->save($res);
			if($result!==false){
				$this->success("修改成功！", U("Pharmacy/index"));
			}else{
				$this->error("修改失败！");
			}

			

		}else
		{
			
			$id = I('get.id',0,'intval');
			$result = $this->pharmacy->where("id=".$id)->find();
			$hrebs = $this->pharmacy_hrebs->join('lgq_single_hrebs on lgq_single_hrebs.id=lgq_pharmacy_hrebs.hrebs_name_id')->field('lgq_pharmacy_hrebs.id,hrebs_dosage,hrebs_name,unit_price,setting_id_model')->where('pharmacy_id='.$id)->select();
			$admin_id=session('admin_id');
			$jgf = $this->admin->where("is_default=1")->find();
			$total = 0;
			foreach ($hrebs as $key => $value) {
				$total += $value['unit_price']*$value['hrebs_dosage']; 
			}
			$result['username']=$this->user->where("id=".$result['user_id'])->getField('username');
			$result['setting_name']=$this->setting->where("id=".$result['setting_id'])->getField('title');
			$result['order_status']=$this->setting->where("id=".$result['order_status'])->getField('title');
			if($result['img_thumb']!=''){
				$images = explode(',', $result['img_thumb']);
			}
			$this->assign("result",$result);
			$this->assign("hrebs",$hrebs);
			$this->assign("total",$total);
			$this->assign("images",$images);
			$this->assign("jgf",$jgf);
			$this->display('editPharmacy');
		}
	}
    public function printPharmacy()
	{
		$id = I('get.id',0,'intval');
		$result = $this->pharmacy->where("id=".$id)->find();
		$patient = $this->patient->where("user_id=".$result['user_id'])->find();
		$patient['phone'] = $this->user->where('id='.$patient['user_id'])->getField('username');
		$hrebs = $this->pharmacy_hrebs->join('lgq_single_hrebs on lgq_single_hrebs.id=lgq_pharmacy_hrebs.hrebs_name_id')->field('lgq_pharmacy_hrebs.id,hrebs_dosage,hrebs_name,unit_price,setting_id_model')->where('pharmacy_id='.$id)->select();
		$admin_id=session('admin_id');
		// $jgf = $this->admin->where("id=".$admin_id)->find();
		$jgf = $this->admin->where("is_default=1")->find();
		$total = 0;
		foreach ($hrebs as $key => $value) {
			$total += $value['unit_price']*$value['hrebs_dosage']; 
		}
		$result['username']=$this->user->where("id=".$result['user_id'])->getField('username');
		$result['setting_name']=$this->setting->where("id=".$result['setting_id'])->getField('title');
		$result['order_status']=$this->setting->where("id=".$result['order_status'])->getField('title');
		if($result['img_thumb']!=''){
			$images = explode(',', $result['img_thumb']);
		}
		$this->assign("result",$result);
		$this->assign("patient",$patient);
		$this->assign("hrebs",$hrebs);
		$this->assign("total",$total);
		$this->assign("images",$images);
		$this->assign("jgf",$jgf);
		$this->display('printPharmacy');
		
	}
 	public function addHrebs(){
 		$pharmacy_id = I('get.id',0,'intval');
 		$setting_id_dostype = I('get.setting_id_dostype','131');
 		$hrebs_number = I('get.hrebs_number',1,'intval');
 		$pre_price = $this->admin->where("is_default=1")->find();
 		if($setting_id_dostype == 131){
			$process_price = $pre_price['technical_price'];
		}else if($setting_id_dostype == 132){
			$process_price = $pre_price['frying_price']*$hrebs_number;
		}else if($setting_id_dostype == 133){
			$process_price = $pre_price['paste_price']*$hrebs_number;
		}
 		$res = array(
 			'id'=>$pharmacy_id,
 			'setting_id_dostype'=> $setting_id_dostype,
 			'hrebs_number' => $hrebs_number,
 			'process_price' => $process_price,
 		);
 		$this->pharmacy->save($res);
 		if(IS_POST){
 			// $count = $this->pharmacy_hrebs->where("pharmacy_id=".$pharmacy_id." and hrebs_name_id=".$_POST['hrebs_name_id'])->count();
 			
 			// if($count > 0){
 			// 	$this->error('你已添加过此药材！');
 			// }
 			
			if($this->pharmacy_hrebs->create()!==false) {
				$result=$this->pharmacy_hrebs->add();
				if ($result!==false) {
					$this->success("添加成功！", U("Pharmacy/editPharmacy"));
				}
			}else{
				
				$this->error('添加失败!');
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
			
			$where.=" and setting_id_model=155";
			$where.=" and status=1";	
	 		//abc
	 		$abca = $this->hrebs->where("firstletter in ('a','b','c')".$where)->order('firstletter asc')->select();
	 		$abc='';
	 		foreach ($abca as $key => $value) {
	 			$abc.="@".$value['id']."|".$value['hrebs_name'];
	 		}
	 		//def
	 		$defa = $this->hrebs->where("firstletter in ('d','e','f')".$where)->order('firstletter asc')->select();
	 		$def='';
	 		foreach ($defa as $key => $value) {
	 			$def.="@".$value['id']."|".$value['hrebs_name'];
	 		}
	 		//ghi
	 		$ghia = $this->hrebs->where("firstletter in ('g','h','i')".$where)->order('firstletter asc')->select();
	 		$ghi='';
	 		foreach ($ghia as $key => $value) {
	 			$ghi.="@".$value['id']."|".$value['hrebs_name'];
	 		}
	 		//jkl
	 		$jkla = $this->hrebs->where("firstletter in ('j','k','l')".$where)->order('firstletter asc')->select();
	 		$jkl='';
	 		foreach ($jkla as $key => $value) {
	 			$jkl.="@".$value['id']."|".$value['hrebs_name'];
	 		}
	 		//mno
	 		$mnoa = $this->hrebs->where("firstletter in ('m','n','o')".$where)->order('firstletter asc')->select();
	 		$mno='';
	 		foreach ($mnoa as $key => $value) {
	 			$mno.="@".$value['id']."|".$value['hrebs_name'];
	 		}
	 		//pqr
	 		$pqra = $this->hrebs->where("firstletter in ('p','q','r')".$where)->order('firstletter asc')->select();
	 		$pqr='';
	 		foreach ($pqra as $key => $value) {
	 			$pqr.="@".$value['id']."|".$value['hrebs_name'];
	 		}
	 		//stu
	 		$stua = $this->hrebs->where("firstletter in ('s','t','u')".$where)->order('firstletter asc')->select();
	 		$stu='';
	 		foreach ($stua as $key => $value) {
	 			$stu.="@".$value['id']."|".$value['hrebs_name'];
	 		}
	 		//vwx
	 		$vwxa = $this->hrebs->where("firstletter in ('v','w','x')".$where)->order('firstletter asc')->select();
	 		$vwx='';
	 		foreach ($vwxa as $key => $value) {
	 			$vwx.="@".$value['id']."|".$value['hrebs_name'];
	 		}
	 		//yz
	 		$yza = $this->hrebs->where("firstletter in ('y','z')".$where)->order('firstletter asc')->select();
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
	 		$this->assign('pharmacy_id',$pharmacy_id);
	 		$this->display("addHrebs");
	 	}
 	}
 	public function getQuilty(){
 		$admin_id=session('admin_id');
			$admin_count = $this->admin->where("id=".$admin_id." and is_pharmacy = 1")->count();
			$where = '';
			if($admin_count > 0){
				$where.=" and admin_id=".$admin_id;
			}else{
				$where.=" and admin_id=74";
			}
			
			$setting_id_model = I('post.setting_id_model','155');
			$where.=" and setting_id_model=".$setting_id_model;
			$where.=" and status=1";	
	 		//abc
	 		$abca = $this->hrebs->where("firstletter in ('a','b','c')".$where)->order('firstletter asc')->select();
	 		$abc='';
	 		foreach ($abca as $key => $value) {
	 			$abc.="@".$value['id']."|".$value['hrebs_name'];
	 		}
	 		//def
	 		$defa = $this->hrebs->where("firstletter in ('d','e','f')".$where)->order('firstletter asc')->select();
	 		$def='';
	 		foreach ($defa as $key => $value) {
	 			$def.="@".$value['id']."|".$value['hrebs_name'];
	 		}
	 		//ghi
	 		$ghia = $this->hrebs->where("firstletter in ('g','h','i')".$where)->order('firstletter asc')->select();
	 		$ghi='';
	 		foreach ($ghia as $key => $value) {
	 			$ghi.="@".$value['id']."|".$value['hrebs_name'];
	 		}
	 		//jkl
	 		$jkla = $this->hrebs->where("firstletter in ('j','k','l')".$where)->order('firstletter asc')->select();
	 		$jkl='';
	 		foreach ($jkla as $key => $value) {
	 			$jkl.="@".$value['id']."|".$value['hrebs_name'];
	 		}
	 		//mno
	 		$mnoa = $this->hrebs->where("firstletter in ('m','n','o')".$where)->order('firstletter asc')->select();
	 		$mno='';
	 		foreach ($mnoa as $key => $value) {
	 			$mno.="@".$value['id']."|".$value['hrebs_name'];
	 		}
	 		//pqr
	 		$pqra = $this->hrebs->where("firstletter in ('p','q','r')".$where)->order('firstletter asc')->select();
	 		$pqr='';
	 		foreach ($pqra as $key => $value) {
	 			$pqr.="@".$value['id']."|".$value['hrebs_name'];
	 		}
	 		//stu
	 		$stua = $this->hrebs->where("firstletter in ('s','t','u')".$where)->order('firstletter asc')->select();
	 		$stu='';
	 		foreach ($stua as $key => $value) {
	 			$stu.="@".$value['id']."|".$value['hrebs_name'];
	 		}
	 		//vwx
	 		$vwxa = $this->hrebs->where("firstletter in ('v','w','x')".$where)->order('firstletter asc')->select();
	 		$vwx='';
	 		foreach ($vwxa as $key => $value) {
	 			$vwx.="@".$value['id']."|".$value['hrebs_name'];
	 		}
	 		//yz
	 		$yza = $this->hrebs->where("firstletter in ('y','z')".$where)->order('firstletter asc')->select();
	 		$yz='';
	 		foreach ($yza as $key => $value) {
	 			$yz.="@".$value['id']."|".$value['hrebs_name'];
	 		}

			unset($data);
			$data['code']=0;
			$data['abc']=$abc;
			$data['def']=$def;
			$data['ghi']=$ghi;
			$data['jkl']=$jkl;
			$data['mno']=$mno;
			$data['pqr']=$pqr;
			$data['stu']=$stu;
			$data['vwx']=$vwx;
			$data['yz']=$yz;
			 exit(json_encode($data));	
	 	
 	}
 	public function delHrebs(){
 		$id = I('post.id',0,'intval');

		if ($this->pharmacy_hrebs->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
 	}

 	public function pharmacy_operate()
	{
		$id = I('post.id',0,'intval');
		$status = I('post.status',0,'intval');
		$dataarray=array(
			'id' => $id,
			'order_status' => $status,
		);
		if ($this->pharmacy->save($dataarray)!==false) {
			$this->success("修改成功！");
		} else {
			$this->error("修改失败！");
		}
	}
	
	public function btn_export(){
	 	$xlsName  = "Pharmacy";
	 	$id = I('get.id');
        $xlsCell  = array(
            array('id','药方id'),
            array('setting_id','类型'),
            array('setting_id_model','药材类型'),
            array('hrebs_name','药材名'),
            array('unit_price','单价'),
            array('hrebs_dosage','数量'),
            array('single_total','小计'),
            // array('dosage_name','剂型'),
        );
        $xlsModel = $this->pharmacy;
        $result = $xlsModel->where('id='.$id)->find();
        $setting_id  = $this->setting->where('id='.$result['setting_id'])->getField('title');
        $xlsData = $this->pharmacy_hrebs->join("lgq_single_hrebs on lgq_single_hrebs.id = lgq_pharmacy_hrebs.hrebs_name_id")->field('lgq_single_hrebs.setting_id_model,lgq_pharmacy_hrebs.hrebs_dosage,lgq_single_hrebs.unit_price,lgq_single_hrebs.hrebs_name')->where('pharmacy_id='.$id)->select();
        // if($dosage_name == '131'){
        // 	$dosage_name = '原药';
        // }else if($dosage_name == '132'){
        // 	$dosage_name = '汤剂代煎';
        // }else if($dosage_name == '133'){
        // 	$dosage_name = '膏方制作';
        // }
       
        foreach ($xlsData as $k => &$v) {
        	$v['id'] = $id;
        	$v['setting_id'] = $setting_id;
        	if($v['setting_id_model'] == '155'){
        		$v['setting_id_model'] = "标准药材";
        	}else if($v['setting_id_model'] == '156'){
        		$v['setting_id_model'] = "精品药材";
        	}else if($v['setting_id_model'] == '51'){
        		$v['setting_id_model'] = "配方颗粒";
        	}
        	$v['single_total'] = $v['unit_price'] * $v['hrebs_dosage'];
        	
        }
        exportExcel($xlsName,$xlsCell,$xlsData);
	}  

}