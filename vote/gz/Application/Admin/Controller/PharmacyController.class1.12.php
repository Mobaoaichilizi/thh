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
		$this->setting = D("Setting");
		$this->hrebs = D("SingleHrebs");
		$this->pharmacy_hrebs = D("PharmacyHrebs");

	}

	//列表显示
    public function index(){
    	$order_status = I('post.order_status');
    	$setting_id = I('post.setting_id');
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
			$all_price = $price + $_POST['yun_free'];
			$res = array(
				'id' => $id,
				'yun_free' => $_POST['yun_free'],
				'price' => $price,
				'is_show' => $is_show,
				'order_status' => $order_status,
				'all_price' => $all_price,
			);
			$result=$this->pharmacy->save($res);
			if($result){
				$this->success("修改成功！", U("Pharmacy/index"));
			}else{
				$this->error("修改失败！");
			}

			

		}else
		{
			
			$id = I('get.id',0,'intval');
			$result = $this->pharmacy->where("id=".$id)->find();
			$hrebs = $this->pharmacy_hrebs->join('lgq_single_hrebs on lgq_single_hrebs.id=lgq_pharmacy_hrebs.hrebs_name_id')->field('lgq_pharmacy_hrebs.id,hrebs_dosage,hrebs_name,unit_price,setting_id_model')->where('pharmacy_id='.$id)->select();
			$total = 0;
			foreach ($hrebs as $key => $value) {
				$total += $value['unit_price']*$value['hrebs_dosage']; 
			}
			$result['username']=$this->user->where("id=".$result['user_id'])->getField('username');
			$result['setting_name']=$this->setting->where("id=".$result['setting_id'])->getField('title');
			$result['order_status']=$this->setting->where("id=".$result['order_status'])->getField('title');
			$this->assign("result",$result);
			$this->assign("hrebs",$hrebs);
			$this->assign("total",$total);
			$this->display('editPharmacy');
		}
	}
    
 	public function addHrebs(){
 		$pharmacy_id = I('get.id',0,'intval');
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
			
	 		//abc
	 		$abca = $this->hrebs->where("firstletter in ('a','b','c')")->order('firstletter asc')->select();
	 		$abc='';
	 		foreach ($abca as $key => $value) {
	 			$abc.="@".$value['id']."|".$value['hrebs_name'];
	 		}
	 		//def
	 		$defa = $this->hrebs->where("firstletter in ('d','e','f')")->order('firstletter asc')->select();
	 		$def='';
	 		foreach ($defa as $key => $value) {
	 			$def.="@".$value['id']."|".$value['hrebs_name'];
	 		}
	 		//ghi
	 		$ghia = $this->hrebs->where("firstletter in ('g','h','i')")->order('firstletter asc')->select();
	 		$ghi='';
	 		foreach ($ghia as $key => $value) {
	 			$def.="@".$value['id']."|".$value['hrebs_name'];
	 		}
	 		//jkl
	 		$jkla = $this->hrebs->where("firstletter in ('j','k','l')")->order('firstletter asc')->select();
	 		$jkl='';
	 		foreach ($jkla as $key => $value) {
	 			$def.="@".$value['id']."|".$value['hrebs_name'];
	 		}
	 		//mno
	 		$mnoa = $this->hrebs->where("firstletter in ('m','n','o')")->order('firstletter asc')->select();
	 		$mno='';
	 		foreach ($mnoa as $key => $value) {
	 			$def.="@".$value['id']."|".$value['hrebs_name'];
	 		}
	 		//pqr
	 		$pqra = $this->hrebs->where("firstletter in ('p','q','r')")->order('firstletter asc')->select();
	 		$pqr='';
	 		foreach ($pqra as $key => $value) {
	 			$def.="@".$value['id']."|".$value['hrebs_name'];
	 		}
	 		//stu
	 		$stua = $this->hrebs->where("firstletter in ('s','t','u')")->order('firstletter asc')->select();
	 		$stu='';
	 		foreach ($stua as $key => $value) {
	 			$def.="@".$value['id']."|".$value['hrebs_name'];
	 		}
	 		//vwx
	 		$vwxa = $this->hrebs->where("firstletter in ('v','w','x')")->order('firstletter asc')->select();
	 		$vwx='';
	 		foreach ($vwxa as $key => $value) {
	 			$def.="@".$value['id']."|".$value['hrebs_name'];
	 		}
	 		//yz
	 		$yza = $this->hrebs->where("firstletter in ('y','z')")->order('firstletter asc')->select();
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
 	public function delHrebs(){
 		$id = I('post.id',0,'intval');

		if ($this->pharmacy_hrebs->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
 	}
	

}