<?php
// +----------------------------------------------------------------------
// | 用户组管理
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class RoleController extends AdminbaseController {
	protected $role,$menu,$access,$user;
	
	public function _initialize() {
		parent::_initialize();
		$this->role = D("Role");
		$this->menu = D("Menu");
		$this->access = D("Access");
		$this->user = D("Admin");
	}
    public function index(){
		$name = I('request.name');
		if($name){
			$where['name'] = array('like',"%$name%");
		}
		$count=$this->role->where($where)->count();
		$page = $this->page($count,11);
		$rolelist = $this->role
            ->where($where)
            ->order("sort asc,id DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
		$this->assign("page", $page->show());
		$this->assign("rolelist",$rolelist);
        $this->display('index');
    }
	public function addRole()
	{
		if(IS_POST){
			if($this->role->create()!==false) {
				if ($result!==false) {
					$result=$this->role->add();
					admin_log('add','role',$_POST['name']);
					$this->success("添加成功！", U("Role/index"));
				}else
				{
					$this->error('添加失败!');
				}
			}
			
		}else
		{
			$this->display('addRole');
		}
	}
	public function editRole()
	{
		if(IS_POST)
		{
			$id = I('post.id',0,'intval');
			if($this->role->create()!==false) {
				if ($result!==false) {
					$result=$this->role->where("id=".$id)->save();
					admin_log('edit','role',$_POST['name']);
					$this->success("编辑成功！", U("Role/index"));
				}else
				{
					$this->error('编辑失败!');
				}
			}
		}else
		{
			$id = I('get.id',0,'intval');
			$result=$this->role->where("id=".$id)->find();
			$this->assign('result',$result);
			$this->display('editRole');
		}
	}
	public function delRole()
	{
		$id = I('post.id',0,'intval');
		$count=$this->user->where(array('role_id'=>$id))->count();
        if($count>0){
        	$this->error("该角色已经有用户！");
		}else{
		$name=$this->role->where("id=".$id)->getField("name");
		admin_log('del','role',$name);
		if ($this->role->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
		}
	}
	public function saveAccess()
	{
		if(IS_POST)
		{
			$role_id = I("post.role_id",0,'intval');
			if(!$role_id){
    			$this->error("需要授权的角色不存在！");
    		}
			if (is_array($_POST['menuid']) && count($_POST['menuid'])>0) {
    			
    			$this->access->where(array("role_id"=>$role_id))->delete();
    			foreach ($_POST['menuid'] as $menuid) {
    				$menu=$this->menu->where(array("id"=>$menuid))->field("app,model,action,id")->find();
    				if($menu){
    					$app=$menu['app'];
    					$model=$menu['model'];
    					$action=$menu['action'];
    					$name=strtolower("$app/$model/$action");
    					$this->access->add(array("role_id"=>$role_id,"node_name"=>$name,'node_id'=>$menu['id']));
    				}
    			}
    
    			$this->success("授权成功！", U("Role/index"));
    		}else{
    			//当没有数据时，清除当前角色授权
    			$this->access->where(array("role_id" => $role_id))->delete();
    			$this->error("没有接收到数据，执行清除授权成功！");
    		}
		}else
		{
			$role_id = I('get.id',0,'intval');
			
			$priv_data=$this->access->where(array("role_id"=>$role_id))->getField("node_id",true);//获取权限表数据
			
			$result = $this->menu->order(array("listorder" => "ASC"))->select();
			$tree = new \Think\Tree();
			$tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
			$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
			
			$newmenus=array();
			foreach ($result as $m){
				$newmenus[$m['id']]=$m;	 
			}
			foreach ($result as $n=> $r) {
				$result[$n]['checked'] = ($this->_is_checked($r, $role_id, $priv_data)) ? ' checked' : '';
				$result[$n]['level'] = $this->_get_level($r['id'], $newmenus);
			}
			$tree->init($result);
			$str = "<tr id='node-\$id'>
						<td><input type='checkbox' name='menuid[]' value='\$id' level='\$level' \$checked onclick='javascript:checknode(this);'></td>
						<td>\$spacer\$name</td>
					</tr>";
			$categorys = $tree->get_tree(0, $str);
			$this->assign("categorys", $categorys);
			$this->assign('role_id',$role_id);
			$this->display('saveAccess');
		}
		
	}
	 /**
     * 获取菜单深度
     * @param $id
     * @param $array
     * @param $i
     */
    protected function _get_level($id, $array = array(), $i = 0) {
        
		if ($array[$id]['parentid']==0 || empty($array[$array[$id]['parentid']]) || $array[$id]['parentid']==$id){
			return  $i;
		}else{
			$i++;
			return $this->_get_level($array[$id]['parentid'],$array,$i);
		}
        		
    }
	
	/**
     *  检查指定菜单是否有权限
     * @param array $menu menu表中数组
     * @param int $roleid 需要检查的角色ID
     */
    private function _is_checked($menu, $roleid, $priv_data) {
    	
    	$menuid=$menu['id'];
    	if($priv_data){
	    	if (in_array($menuid, $priv_data)) {
	    		return true;
	    	} else {
	    		return false;
	    	}
    	}else{
    		return false;
    	}
    	
    }
	
}
?>