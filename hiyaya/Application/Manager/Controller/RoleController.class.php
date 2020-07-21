<?php
// +----------------------------------------------------------------------
// | 用户组管理
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Manager\Controller;
use Common\Controller\ManagerbaseController;
class RoleController extends ManagerbaseController {
	protected $role,$menu,$access,$user;
	
	public function _initialize() {
		parent::_initialize();
		$this->role = D("ShopRole");
		$this->menu = D("ShopMenu");
		$this->apimenu = D("ShopApimenu");
		$this->access = D("ShopAccess");
		$this->apiaccess = D("ShopApiaccess");
		$this->user = D("ShopUser");
		$this->shopid=session('shop_id');
		$this->chainid=session('chain_id');
	}
    public function index(){
		
        $this->display('index');
    }
	public function get_index()
	{
		$name = I('request.name');
		$limit = I('request.limit');
		$page = I('request.page');
		if($page==1)
		{
			$page=0;
		}else
		{
			$page=($page-1)*$limit;
		}
		$where['shop_id']=$this->shopid;
		if($name){
			$where['name'] = array('like',"%$name%");
		}
		$count=$this->role->where($where)->count();
		$rolelist = $this->role
            ->where($where)
            ->order("sort asc,id DESC")
            ->limit($page.','.$limit)
            ->select();
		foreach($rolelist as &$row)
		{
			$row['createtime']=date("Y-m-d H:i:s",$row['createtime']);
		}
		unset($data);
		$data['code']=0;
		$data['msg']=$page;
		$data['count']=$count;
		$data['data']=$rolelist;
		outJson($data);
	}
	public function addRole()
	{
		if(IS_POST){
			if($this->role->create()!==false) {
				$result=$this->role->add();
				if ($result!==false) {
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
				$result=$this->role->where("id=".$id)->save();
				if ($result!==false) {
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
		if ($this->role->delete($id)!==false) {
			$this->access->where("role_id=".$id." and shop_id=".$this->shopid)->delete();
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
			$chain_id = I("post.chain_id",0,'intval');
			$shop_id = I("post.shop_id",0,'intval');
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
    					$this->access->add(array("role_id"=>$role_id,"node_name"=>$name,'node_id'=>$menu['id'],'chain_id'=>$chain_id,'shop_id'=>$shop_id));
    				}
    			}
    
    			$this->success("授权成功！", U("Role/index"));
    		}else{
    			//当没有数据时，清除当前角色授权
    			$this->access->where(array("role_id" => $role_id,'shop_id'=>$this->shopid))->delete();
    			$this->error("没有接收到数据，执行清除授权成功！");
    		}
		}else
		{
			$role_id = I('get.id',0,'intval');
			
			$priv_data=$this->access->where(array("role_id"=>$role_id,'shop_id'=>$this->shopid))->getField("node_id",true);//获取权限表数据
			
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
						<td width='50'><input type='checkbox' name='menuid[]' value='\$id' level='\$level' \$checked onclick='javascript:checknode(this);'></td>
						<td>\$spacer\$name</td>
					</tr>";
			$categorys = $tree->get_tree(0, $str);
			$this->assign("categorys", $categorys);
			$this->assign('role_id',$role_id);
			$this->display('saveAccess');
		}
		
	}
	
	
	
	//APP菜单权限管理
	public function saveAppAccess()
	{
		if(IS_POST)
		{
			$role_id = I("post.role_id",0,'intval');
			$chain_id = I("post.chain_id",0,'intval');
			$shop_id = I("post.shop_id",0,'intval');
			if(!$role_id){
    			$this->error("需要授权的角色不存在！");
    		}
			if (is_array($_POST['menuid']) && count($_POST['menuid'])>0) {
    			
    			$this->apiaccess->where(array("role_id"=>$role_id))->delete();
    			foreach ($_POST['menuid'] as $menuid) {
    				$menu=$this->apimenu->where(array("id"=>$menuid))->field("app,model,action,id")->find();
    				if($menu){
    					$app=$menu['app'];
    					$model=$menu['model'];
    					$action=$menu['action'];
    					$name=strtolower("$app/$model/$action");
    					$this->apiaccess->add(array("role_id"=>$role_id,"node_name"=>$name,'node_id'=>$menu['id'],'chain_id'=>$chain_id,'shop_id'=>$shop_id));
    				}
    			}
    
    			$this->success("授权成功！", U("Role/index"));
    		}else{
    			//当没有数据时，清除当前角色授权
    			$this->apiaccess->where(array("role_id" => $role_id,'shop_id'=>$this->shopid))->delete();
    			$this->error("没有接收到数据，执行清除授权成功！");
    		}
		}else
		{
			$role_id = I('get.id',0,'intval');
			
			$priv_data=$this->apiaccess->where(array("role_id"=>$role_id,'shop_id'=>$this->shopid))->getField("node_id",true);//获取权限表数据
			
			$result = $this->apimenu->order(array("listorder" => "ASC"))->select();
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
			$this->display('saveAppAccess');
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