<?php
// +----------------------------------------------------------------------
// | 用户管理文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2020 All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;
use app\common\controller\AdminBasic;
use app\admin\model\Role as RoleModel;
use think\Url;
use think\Db;
use think\Tree;

class Role extends AdminBasic
{
    public function index($page = 1, $limit = null){
        if($this->request->param('act')=='getData')
        {
            $param = input('get.');
            $map = [];
            if(!empty($param['account'])) {
                $map['name'] = ['like', "%" . $param['account'] . "%"];
            }
            $count = RoleModel::where($map)->count();
            $res = RoleModel::where($map)->page($page, $limit)->order("id desc")->select();
            unset($data);
            $data['code']=0;
            $data['msg']='获取成功！';
            $data['count'] = $count;
            $data['data']=$res;
            outJson($data);
        }
        return $this->fetch();
    }
    /**
     * 添加
     */
    public function add()
    {
        if($this->request->isPost())
        {
            $param = input('post.');
            $result=RoleModel::insertRole($param);
            if($result['code']==1)
            {
                $this->success("添加成功！", Url::build("Role/index"));
            }else
            {
                $this->error($result['msg']);
            }
        }else
        {
            return $this->fetch('add');
        }
    }
    /**
     * 编辑
     */
    public function edit()
    {
        if($this->request->isPost())
        {
            $param = input('post.');
            $result=RoleModel::updateRole($param);
            if($result['code']==1)
            {
                $this->success("修改成功！", Url::build("Role/index"));
            }else
            {
                $this->error($result['msg']);
            }
        }else
        {
            $id = input('get.id');
            $result=Db::name('Role')->where("id=".$id)->find();
            $this->assign('result',$result);
            return $this->fetch('edit');
        }
    }
    /**
     * 删除
     */
    public function del()
    {
        $id = input('post.id');

        if (Db::name('Role')->delete($id)!==false) {
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
    }

    /**
     * 角色授权
     */
    public function saveAccess()
    {
        if($this->request->isPost())
        {
            $role_id = input('post.role_id');
            if(!$role_id){
                $this->error("需要授权的角色不存在！");
            }
            $menu_id = input('post.menuid/a');
            if (is_array($menu_id) && count($menu_id)>0) {

                Db::name('access')->where(array("role_id"=>$role_id))->delete();
                foreach ($menu_id as $menuid) {
                    $menu=Db::name('Menu')->where(array("id"=>$menuid))->field("app,model,action,id")->find();
                    if($menu){
                        $app=$menu['app'];
                        $model=$menu['model'];
                        $action=$menu['action'];
                        $name=strtolower("$app/$model/$action");
                        Db::name('access')->insert(array("role_id"=>$role_id,"node_name"=>$name,'node_id'=>$menu['id']));
                    }
                }

                $this->success("授权成功！", Url::build("Role/index"));
            }else{
                //当没有数据时，清除当前角色授权
                Db::name('access')->where(array("role_id" => $role_id))->delete();
                $this->error("没有接收到数据，执行清除授权成功！");
            }
        }else
        {
            $role_id = input('get.id');

            $priv_data=Db::name('access')->where(array("role_id"=>$role_id))->column("node_id");//获取权限表数据

            $result = Db::name('Menu')->order(array("listorder" => "ASC"))->select();
            $tree = new Tree();
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
						<td><input type='checkbox' name='menuid[]' lay-skin='primary' value='\$id' level='\$level' \$checked lay-filter='checknode'></td>
						<td>\$spacer\$name</td>
					</tr>";
            $categorys = $tree->get_tree(0, $str);
            $this->assign("categorys", $categorys);
            $this->assign('role_id',$role_id);
            return $this->fetch('saveAccess');
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
