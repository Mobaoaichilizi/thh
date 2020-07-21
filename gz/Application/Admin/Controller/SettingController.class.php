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
class SettingController extends AdminbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->setting = D("setting");
	}

	//列表显示
    public function index(){
    	$setting = $this->setting->order(array("sort" => "asc","createtime" => "desc"))->select();
    	$tree = new \Think\Tree();
        $tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';
        foreach ($setting as $n=> $r) {
            $setting[$n]['str_manage'] = '<a onclick="btn_edit('.$r['id'].')" class="LGQ_ico"><i class="fa fa-pencil-square-o"></i> 编辑</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a onclick="btn_delete('.$r['id'].')" class="LGQ_ico"><i class="fa fa-trash-o"></i> 删除</a>';
            $setting[$n]['status'] = $r['status'] ? '显示' : '不显示';
            $setting[$n]['createtime'] = date('Y-m-d H:i:s',$r['createtime']);
        }

        $tree->init($setting);
        $str = "<tr id='node-\$id' \$parentid_node style='\$style'>
					<td>\$spacer\$title</td>
					<td>\$id</td>
				    <td>\$status</td>
					<td>\$createtime</td>
					<td>\$sort</td>
					<td align='center'>\$str_manage</td>
				</tr>";
        $settinglist = $tree->get_tree(0, $str);


    	$this->assign("settinglist",$settinglist);
    	$this->display();
    }
    //添加分类
    public function addSetting(){
    
    	if(IS_POST){
    		
			if($this->setting->create()!==false) {
				if ($setting!==false) {
					$setting=$this->setting->add();
					admin_log('add','settimg',$_POST['title']);
					$this->success("添加成功！", U("Setting/index"));
				}else
				{
					$this->error('添加失败!');
				}
			}
			
		}else
		{
			$tree = new \Think\Tree();
			$parentid = I("get.parentid",0,'intval');
			$result = $this->setting->order(array("sort" => "ASC"))->select();
			foreach ($result as $r) {
				$r['selected'] = $r['id'] == $parentid ? 'selected' : '';
				$array[] = $r;
			}
			$str = "<option value='\$id' \$selected>\$spacer\$title</option>";
			$tree->init($array);
			$select_categorys = $tree->get_tree(0, $str);
			$this->assign("select_categorys", $select_categorys);
			$this->display('addSetting');
		}
    }
    //修改分类
    public function editSetting()
	{
		if(IS_POST)
		{
			$id = I('post.id',0,'intval');
			if($this->setting->create()!==false) {
				if ($setting!==false) {
					$setting=$this->setting->where("id=".$id)->save();
					
					admin_log('edit','setting',$_POST['title']);
					$this->success("编辑成功！", U("setting/index"));
				}else
				{
					$this->error('编辑失败!');
				}
			}
		}else
		{

			$id = I('get.id',0,'intval');
			$setting=$this->setting->where("id=".$id)->find();

			$tree = new \Think\Tree();
			$parentid = $setting['parentid'];
			$result = $this->setting->order(array("sort" => "ASC"))->select();
			foreach ($result as $r) {
				$r['selected'] = $r['id'] == $parentid ? 'selected' : '';
				$array[] = $r;
			}
			$str = "<option value='\$id' \$selected>\$spacer \$title</option>";
			$tree->init($array);
			$select_categorys = $tree->get_tree(0, $str);
			
	
			$this->assign('setting',$setting);

			$this->assign("select_categorys", $select_categorys);
			$this->display('editSetting');
		}
	}
	//删除分类
	public function delSetting()
	{
		$id = I('post.id',0,'intval');
		if($this->setting->where("parentid=".$id)->find())
		{
			$this->error("下级还有栏目！");
		}
		$title=$this->setting->where("id=".$id)->getField("title");
		admin_log('del','setting',$title);
		if ($this->setting->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}



}