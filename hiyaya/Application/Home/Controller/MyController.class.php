<?php
namespace Home\Controller;
use Common\Controller\HomebaseController;
class MyController extends HomebaseController {
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
  //个人信息
	public function user_info()
	{	
		
		$member_info=$this->member->where("id=".$this->user_id)->find();
		$this->assign('member_info',$member_info);
		
		$shop_info=$this->shop->where("id=".$member_info['shop_id'])->find();
		$this->assign('shop_info',$shop_info);
		
		
		
		
		$this->display();
	}
	//编辑个人信息
	public function user_edit()
	{
		$member_info=$this->member->where("id=".$this->user_id)->find();
		$this->assign('member_info',$member_info);
		$this->assign('i',$this->wx_i);
		$this->display();
	}
	//编辑提交个人信息
	public function post_useredit()
	{
		$member_name=I('post.member_name'); //姓名
		$birthday=I('post.birthday'); //生日
		$sex=I('post.sex'); //生日
		
		if($member_name=='')
		{
			$this->error("请输入您的姓名！");
		}
		if($birthday=='')
		{
			$this->error("请输入您的生日！");
		}
		unset($data);
		$data=array(
			'id' => $this->user_id,
			'member_name' => $member_name,
			'sex' => $sex,
			'birthday' => $birthday,
		);
		$rest=$this->member->save($data);
		if($rest!==false)
		{
			$this->success("编辑成功！",U("Index/index",array('i' => $this->wx_i)));
		}else
		{
			$this->error("编辑失败！");
		}
	}
	//卡的列表
	public function card_list()
	{
		
		$member_info=$this->member->where("id=".$this->user_id)->find();
		$this->assign('member_info',$member_info);
		
		$shop_info=$this->shop->where("id=".$member_info['shop_id'])->find();
		$this->assign('shop_info',$shop_info);
		
		
		$numcard_info=$this->shopnumcard->where("shop_id=".$member_info['shop_id']." and member_id=".$this->user_id." and card_num > 0")->order("id desc")->select();
		foreach($numcard_info as &$val)
		{
			$val['project_info']=$this->shopproject->where("id=".$val['project_id'])->find();
		}
		$this->assign('numcard_info',$numcard_info);
		$deadlinecard_info=$this->shopdeadlinecard->where("shop_id=".$member_info['shop_id']." and member_id=".$this->user_id." and day_ceiling > 0 and start_time < ".time()." and end_time > ".time())->order("id desc")->select();
		foreach($deadlinecard_info as &$val)
		{
			$val['project_info']=$this->shopproject->where("id=".$val['project_id'])->find();
		}
		$this->assign('deadlinecard_info',$deadlinecard_info);
		$coursecard_info=$this->shopcoursecard->where("shop_id=".$member_info['shop_id']." and member_id=".$this->user_id)->order("id asc")->select();
		foreach($coursecard_info as &$val)
		{
			$val['courseproject_info']=$this->shopcourseproject->where("card_id=".$val['id'])->select();
			$val['courseproject_count']=$this->shopcourseproject->where("card_id=".$val['id'])->count();
			$long_time=0;
			foreach($val['courseproject_info'] as &$rowt)
			{
				$rowt['project_name']=$this->shopproject->where("id=".$rowt['project_id'])->getField("item_name");
				$rowt['project_time']=$this->shopproject->where("id=".$rowt['project_id'])->getField("item_duration");
				$long_time+=$rowt['project_time'];
			}
			$val['long_time']=$long_time;
		}
		$this->assign('coursecard_info',$coursecard_info);
		$this->display();
	}
	
	//消费记录
	public function record()
	{
		$member_info=$this->member->where("id=".$this->user_id)->find();
		$this->assign('member_info',$member_info);
		$financial_list=$this->shopfinancial->where("shop_id=".$member_info['shop_id']." and member_id=".$this->user_id." and transaction_money <= 0")->order("id desc")->select();
		foreach($financial_list as &$val)
		{
			$val['project_name']=$this->shopproject->where("id=".$val['project_id'])->getField("item_name");
		}
		$this->assign('financial_list',$financial_list);
		$this->display();
	}

}