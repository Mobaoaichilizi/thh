<?php
// +----------------------------------------------------------------------
// | 提现
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class CashController extends AdminbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->cash = D("Cash");
		$this->bank = D("Bank");
		$this->setting = D("Setting");
		$this->user = D("User");

	}

	//列表显示
    public function index(){
        $personname = I('request.personname');
        $bank_id = I('request.bank_id');
    	$status = I('request.status');
		if($personname){
		  $where['lgq_cash.pay_money'] = array('like',"%$personname%");
        }
        if($status !== ''){
            $where['lgq_cash.status'] = array('eq',$status);
        }
        if($bank_id !== ''){
            $bank_ids = $this->bank->where('setting_id='.$bank_id)->getfield('id',true);
            $bank_id = implode(',', $bank_ids);
            $where['lgq_cash.bank_id'] = array('in',$bank_id);
        }
		
		$count=$this->cash->where($where)->count();
		$page = $this->page($count,11);
		$cash = $this->cash
            ->where($where)
            ->order("lgq_cash.id DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
        foreach ($cash as $k => $v) {
        	$v['username'] = $this->user->where('id='.$v['user_id'])->getField('username');
        	$bank = $this->bank->field('id,setting_id,banknumber,personname')->where('id='.$v['bank_id'])->find();
           
        	$v['banknumber'] = $bank['banknumber'];
            $v['personname'] = $bank['personname'];
           
        	// $v['bankname'] = $this->setting->where("id=".$bank['setting_id'])->getField("title");
        	$userst[] = $v;
        }
        $banklist = $this->setting->field('title,id')->where('parentid = 29')->select();
		$this->assign("page", $page->show());
        $this->assign("cashlist",$userst);
		$this->assign("banklist",$banklist);
        $this->display('index');
    }
    public function manage_operate()
	{
		$id = I('post.id',0,'intval');
		$status = I('post.status',0,'intval');
		$dataarray=array(
			'id' => $id,
			'status' => $status,
		);
		if ($this->cash->save($dataarray)!==false) {
			$this->success("修改成功！");
		} else {
			$this->error("修改失败！");
		}
	}
public function btn_export(){
	 	$xlsName  = "Cash";
        $xlsCell  = array(
            array('id','账号序列'),
            array('ordernumber','订单号'),
            array('username','用户'),
            array('pay_money','提现金额'),
            array('bankname','开户行'),
            array('personname','开户人姓名'),
            array('banknumber','银行卡号'),
            array('status','状态'),
            array('create_time','申请提现时间')
        );
        $xlsModel = M('Cash');
        $xlsData  = $xlsModel->Field('id,user_id,bank_id,create_time,status,ordernumber,pay_money,status')->select();
        foreach ($xlsData as $k => &$v) {
        	$v['username'] = $this->user->where('id='.$v['user_id'])->getField('username');
        	$bank = $this->bank->field('setting_id,banknumber,personname')->where('id='.$v['bank_id'])->find();
        	$v['banknumber'] = "'".$bank['banknumber'];
        	$v['personname'] = $bank['personname'];
        	$v['bankname'] = $this->setting->where("id=".$bank['setting_id'])->getField("title");
        	$v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
        	if($v['status'] == 0){
        		$v['status'] = "提现失败";
        	}else if($v['status'] == 1){
        		$v['status'] = "提现成功";
        	}else if($v['status'] == 2){
        		$v['status'] = "待打款";
        	}
        }
        exportExcel($xlsName,$xlsCell,$xlsData);
	}  
	

}