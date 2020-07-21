<?php
// +----------------------------------------------------------------------
// | 前台菜单管理
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class ChainmenuController extends AdminbaseController {
	protected $chainmenu;
	
	public function _initialize() {
		parent::_initialize();
		$this->chainmenu = D("ChainMenu");
	}
    public function index(){
		$result = $this->chainmenu->order(array("listorder" => "ASC"))->select();
        $tree = new \Think\Tree();
        $tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';
        
        $newmenus=array();
        foreach ($result as $m){
        	$newmenus[$m['id']]=$m;
        	 
        }
        foreach ($result as $n=> $r) {
            $result[$n]['str_manage'] = '<a onclick="btn_edit('.$r['id'].')" class="layui-btn layui-btn-normal layui-btn-xs"><i class="layui-icon layui-icon-edit"></i>编辑</a><a onclick="btn_delete('.$r['id'].')" class="layui-btn layui-btn-danger layui-btn-xs"><i class="layui-icon layui-icon-delete"></i>删除</a>';
            $result[$n]['status'] = $r['status'] ? '显示' : '不显示';
            $result[$n]['app']=$r['app']."/".$r['model']."/".$r['action'];
        }
        $tree->init($result);
        $str = "<tr id='node-\$id' \$parentid_node style='\$style'>
					<td>\$spacer\$name</td>
					<td>\$id</td>
        			<td>\$app</td>
				    <td>\$status</td>
					<td>\$listorder</td>
					<td align='center'>\$str_manage</td>
				</tr>";
        $categorys = $tree->get_tree(0, $str);
        $this->assign("categorys", $categorys);
        $this->display('index');
    }
	public function addChainmenu()
	{
		if(IS_POST){
			if($this->chainmenu->create()!==false) {
				if ($result!==false) {
					$result=$this->chainmenu->add();
					$this->success("添加成功！", U("Chainmenu/index"));
				}else
				{
					$this->error('添加失败!');
				}
			}
			
		}else
		{
			$tree = new \Think\Tree();
			$parentid = I("get.parentid",0,'intval');
			$result = $this->chainmenu->order(array("listorder" => "ASC"))->select();
			foreach ($result as $r) {
				$r['selected'] = $r['id'] == $parentid ? 'selected' : '';
				$array[] = $r;
			}
			$str = "<option value='\$id' \$selected>\$spacer \$name</option>";
			$tree->init($array);
			$select_categorys = $tree->get_tree(0, $str);
			$this->assign("select_categorys", $select_categorys);
			$this->display('addChainmenu');
		}
	}
	public function editChainmenu()
	{
		if(IS_POST)
		{
			$id = I('post.id',0,'intval');
			if($this->chainmenu->create()!==false) {
				if ($result!==false) {
					$result=$this->chainmenu->where("id=".$id)->save();
					$this->success("编辑成功！", U("Chainmenu/index"));
				}else
				{
					$this->error('编辑失败!');
				}
			}
		}else
		{
			$id = I('get.id',0,'intval');
			$result=$this->chainmenu->where("id=".$id)->find();
			$this->assign('result',$result);
			
			$tree = new \Think\Tree();
			$parentid = $result['parentid'];
			$result = $this->chainmenu->order(array("listorder" => "ASC"))->select();
			foreach ($result as $r) {
				$r['selected'] = $r['id'] == $parentid ? 'selected' : '';
				$array[] = $r;
			}
			$str = "<option value='\$id' \$selected>\$spacer \$name</option>";
			$tree->init($array);
			$select_categorys = $tree->get_tree(0, $str);
			$this->assign("select_categorys", $select_categorys);
			$this->display('editChainmenu');
		}
	}
	public function delChainmenu()
	{
		$id = I('post.id',0,'intval');
		if($this->chainmenu->where("parentid=".$id)->find())
		{
			$this->error("下级还有栏目！");
		}
		if ($this->chainmenu->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}
}
?>