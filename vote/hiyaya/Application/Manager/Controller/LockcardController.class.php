<?php
// +----------------------------------------------------------------------
// | 锁牌列表页
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Author: TH2 <2791919673@qq.com>
// +----------------------------------------------------------------------
namespace Manager\Controller;
use Common\Controller\ManagerbaseController;
class LockcardController extends ManagerbaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->lockcard = M("ShopLockcard");
        $this->chain_id=session('chain_id');
        $this->shop_id=session('shop_id');
    }
    public function index()
    {
        $this->display('index');
    }
    public function json()
    {

        $limit   =I('get.limit');
        $pagecur=I('get.page');
        $where=" chain_id=".session('chain_id')." and shop_id=".session('shop_id');
        if(I('get.keywords'))
        {
            $keywords= I('get.keywords');
            $where.="  and card_number = '".$keywords."'";
        }

        $pagenum =!empty($limit) ? $limit :3;
        $pagecur = !empty($pagecur)? $pagecur:1;
        $list    = $this->lockcard->where($where)->order('sort asc')->page($pagecur.','.$pagenum)->select();
        foreach ($list as $key => &$value) {
            if($value['status'] == '1'){
                $value['status'] = '正常';
            }else if($value['status'] == '2'){
                $value['status'] = '异常';
            }
            
        }
        $count   = $this->lockcard->where($where)->count();
        $page    = new \Think\Page($count,$pagenum);// 实例化分页类 传入总记录数和每页显示的记录数
        $data=array("code"=>0,"msg"=>"","count"=>$count,"data"=>$list);
        echo json_encode($data);
    }
	// public function isclose()
	// {
 //        $id = I('post.id',0,'intval');
 //        $is_lock =I('post.is_lock',0,'intval');
 //        $data=array("is_lock"=>$is_lock);
 //        $res=$this->lockcard->where('id='.$id)->save($data);
 //        if($res)
 //        {
 //            echo 'success';
 //        }
 //        else
 //        {
 //            echo 'error';
 //        }
	// }
	public function add()
    {

        if(IS_POST){
            if($this->lockcard->create()!==false) {
                $result=$this->lockcard->add();
                if ($result!==false) {
                    $this->success("添加成功！", U("Lockcard/index"));
                }else
                {
                    $this->error('添加失败!');
                }
            }else{
                $this->error($this->lockcard->getError());
            }
            
        }else
        {
           
            $this->display('add');
        }
        
    }
    public function edit()
    {
       
        if(IS_POST)
        {
            $id = I('post.id',0,'intval');
            if($this->lockcard->create()!==false) {
                $result=$this->lockcard->where("id=".$id)->save();
                if ($result!==false) {
                    $this->success("编辑成功！", U("Lockcard/index"));
                }else
                {
                    $this->error('编辑失败!');
                }
            }
        }else
        {
            $id = I('get.id',0,'intval');
            $result=$this->lockcard->where("id=".$id)->find();
            $this->assign('result',$result);
            $this->display('edit');
        }
    }

    public function del()
    {
        $id = I('post.id',0,'intval');
        
        if ($this->ShopMember->delete($id)!==false) {
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
    }


}
?>