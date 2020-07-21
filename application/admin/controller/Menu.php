<?php
namespace app\admin\controller;
use app\common\controller\AdminBasic;
use app\admin\model\Menu as MenuModel;
use think\Url;
use think\Request;
use think\Db;

class Menu extends AdminBasic
{
	public function _initialize() {
		parent::_initialize();
	}
    public function index(){
	    if($this->request->param('act')=='getData')
        {
            $res=MenuModel::getMenuTree(0);
            unset($data);
            $data['code']=0;
            $data['msg']='获取成功！';
            $data['data']=$res;
            outJson($data);
        }
		return $this->fetch();
    }
	//添加菜单
	public function addMenu(Request $request)
	{
		if($request->isPost())
		{
			$param = input('post.');
			$result=MenuModel::insertMenu($param);
			if($result['code']==1)
			{
				$this->success("添加成功！", Url::build("Menu/index"));
			}else
			{
				$this->error($result['msg']);
			}
		}else
		{
			$tree = new \think\Tree();
			$result = Db::name('Menu')->order(array("listorder" => "ASC"))->select();
			$str = "<option value='\$id'>\$spacer \$name</option>";
			$tree->init($result);
			$select_categorys = $tree->get_tree(0, $str);
			$this->assign("select_categorys", $select_categorys);
			return $this->fetch('addmenu');
		}
	}
	//编辑菜单
	public function editMenu(Request $request)
	{
		if($request->isPost())
		{
			$param = input('post.');
			$result=MenuModel::updateMenu($param);
			if($result['code']==1)
			{
				$this->success("编辑成功！", Url::build("Menu/index"));
			}else
			{
				$this->error($result['msg']);
			}
		}else
		{
			$id = input('get.id');
			$result=Db::name('Menu')->where("id=".$id)->find();
			$this->assign('result',$result);
			
			$tree = new \think\Tree();
			$parentid = $result['parentid'];
			$result = Db::name('Menu')->order(array("listorder" => "ASC"))->select();
			foreach ($result as $r) {
				$r['selected'] = $r['id'] == $parentid ? 'selected' : '';
				$array[] = $r;
			}
			$str = "<option value='\$id' \$selected>\$spacer \$name</option>";
			$tree->init($array);
			$select_categorys = $tree->get_tree(0, $str);
			
			
			
			$this->assign("select_categorys", $select_categorys);
			return $this->fetch('editmenu');
		}
	}
	//删除
	public function delMenu()
	{
		$id = input('post.id');
		if(Db::name('Menu')->where("parentid=".$id)->find())
		{
			$this->error("下级还有栏目！");
		}
		if (Db::name('Menu')->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}
	
}
