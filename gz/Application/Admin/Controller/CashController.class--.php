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
		
		$where['lgq_cash.pay_money'] = array('like',"%$personname%");
		
		$count=$this->cash->where($where)->count();
		$page = $this->page($count,11);
		$cash = $this->cash
            ->where($where)
            ->order("lgq_cash.id DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
        foreach ($cash as $k => $v) {
        	$v['username'] = $this->user->where('id='.$v['user_id'])->getField('username');
        	$bank = $this->bank->field('setting_id,banknumber,personname')->where('id='.$v['bank_id'])->find();
        	$v['banknumber'] = $bank['banknumber'];
        	$v['personname'] = $bank['personname'];
        	$v['bankname'] = $this->setting->where("id=".$bank['setting_id'])->getField("title");
        	$userst[] = $v;
        }
		$this->assign("page", $page->show());
		$this->assign("cashlist",$userst);
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
	// require_once VENDOR_PATH."PHPExcel/Classes/PHPExcel.php";
	 	$xlsName  = "Cash";
        $xlsCell  = array(
            array('id','账号序列'),
            array('ordernumber','订单号'),
            array('pay_money','提现金额')
        );
        $xlsModel = M('Cash');
        $xlsData  = $xlsModel->Field('id,ordernumber,pay_money')->select();
        exportExcel($xlsName,$xlsCell,$xlsData);
	}  
	

}