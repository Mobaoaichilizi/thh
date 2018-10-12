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
class AdviceController extends AdminbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->advice = D("advice");

	}

	//列表显示
    public function index(){
    	$name = I('request.name');
		if($name){
			$where['lgq_user.name'] = array('like',"%$name%");
		}
		$count=$this->advice->where($where)->count();
		$page = $this->page($count,11);
		$advice = $this->advice->join("lgq_user on lgq_user.id = lgq_advice.user_id")->field("lgq_user.username,role,lgq_advice.*")
            ->where($where)
            ->order("lgq_advice.id DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();

	       foreach ($advice as $key => &$value) {
	       	if(!empty($value['img_thumb'])){
	       		$value['img_thumb'] = explode(',', $value['img_thumb']);
	       	}
	       }
		$this->assign("page", $page->show());
		$this->assign("advicelist",$advice);
        $this->display('index');
    }
   
	//删除分类
	public function delAdvice()
	{
		$id = I('post.id',0,'intval');
		$content=$this->advice->where("id=".$id)->getField("content");
		admin_log('del','advice',$content);
		if ($this->advice->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}



}