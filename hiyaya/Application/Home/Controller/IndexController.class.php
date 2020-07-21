<?php
namespace Home\Controller;
use Common\Controller\HomebaseController;
class IndexController extends HomebaseController {
	public function _initialize() {
		parent::_initialize();
		$this->member=M('ShopMember');
		$this->shop=M('Shop');
		
		$this->shopnumcard=M("ShopNumcard"); //次卡
		$this->shopdeadlinecard=M("ShopDeadlinecard"); //期限卡
		$this->shopcoursecard=M("ShopCoursecard"); //疗程卡
		$this->shopcourseproject=M("ShopCourseproject"); //疗程卡对应的项目
		
		$this->shopproject=M("ShopItem"); //项目表
		$this->shopfinancial=M("ShopFinancial"); //消费记录
		
		$this->wx_i=I('get.i');
		$this->openid=$_SESSION['wx_user'];
		$this->user_id=$_SESSION['user_id'];
	}
  //首页
	public function index()
	{	
		$member_info=$this->member->where("id=".$this->user_id)->find();
		$this->assign('member_info',$member_info);
		
		$shop_info=$this->shop->where("id=".$member_info['shop_id'])->find();
		$this->assign('shop_info',$shop_info);

		$numcard_info_count=$this->shopnumcard->where("shop_id=".$member_info['shop_id']." and member_id=".$this->user_id." and card_num > 0")->count();
		$deadlinecard_info_count=$this->shopdeadlinecard->where("shop_id=".$member_info['shop_id']." and member_id=".$this->user_id." and day_ceiling > 0 and start_time < ".time()." and end_time > ".time())->count();
		$coursecard_info_count=$this->shopcoursecard->where("shop_id=".$member_info['shop_id']." and member_id=".$this->user_id)->count();
		
		$this->assign("total_count",$numcard_info_count+$deadlinecard_info_count+$coursecard_info_count);
		
		$this->display();
	}

}