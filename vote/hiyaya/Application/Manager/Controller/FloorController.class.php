<?php
// +----------------------------------------------------------------------
// | 门店列表页
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Author: Alina <309824428@qq.com>
// +----------------------------------------------------------------------
namespace Manager\Controller;
use Common\Controller\ManagerbaseController;
class FloorController extends ManagerbaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->ShopFloor = M("ShopFloor");
        $this->chain_id=session('chain_id');
        $this->shop_id=session('shop_id');
        $this->assign('chain_id',session('chain_id'));
        $this->assign('shop_id',session('shop_id'));

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
            $where.="  floor_name like '%".$keywords."%'";
        }


        $pagenum =!empty($limit) ? $limit :3;
        $pagecur = !empty($pagecur)? $pagecur:1;
        $list    = $this->ShopFloor->where($where)->order('id')->page($pagecur.','.$pagenum)->select();
        foreach ($list as $key=> $val)
        {
            $list[$key]['chain_id']      =M('chain')->where('id='.$val['chain_id'])->getField('name');
            $list[$key]['shop_id']       =M('shop')->where('id='.$val['shop_id'])->getField('shop_name');

            //$list[$key]['createtime']=date("y-m-d H:i:s",$val['createtime']);
            if($val['state']==1)
            {
                $list[$key]['state']='启用';
            }
            else
            {
                $list[$key]['state']='锁定';
            }
        }
        $count   = $this->ShopFloor->where($where)->count();
        $page    = new \Think\Page($count,$pagenum);// 实例化分页类 传入总记录数和每页显示的记录数
        $data=array("code"=>0,"msg"=>"","count"=>$count,"data"=>$list);
        echo json_encode($data);
    }
	public function close()
	{
        $id = I('post.id',0,'intval');
        $data=array("state"=>0);
        $res=$this->ShopFloor->where('id='.$id)->save($data);
        if($res)
        {
            echo 'success';
        }
        else
        {
            echo 'error';
        }
	}
	public function del()
    {
        $id = I('post.id',0,'intval');
        $res=$this->ShopFloor->where("id=".$id)->find();
        if($res['state']==1){
            $this->error("启用中的楼层禁止删除！");
        }
        if ($this->ShopFloor->delete($id)!==false)
        {
            echo 'success';
        }
        else
        {

            $this->error("删除失败！");
        }
    }
    public function open()
    {

        $id = I('post.id',0,'intval');
        $data=array("state"=>1);
        $res=$this->ShopFloor->where('id='.$id)->save($data);
        if($res)
        {
            echo 'success';
        }
        else
        {
            echo 'error';
        }
    }
	public function add()
    {
       if($_POST['act']=='add')
       {

           $data=array(
               'chain_id'=>$this->chain_id,
               'shop_id'=>$this->shop_id,
               'floor'=>I('post.floor'),
               'floor_name'=>I('post.floor_name'),
               'sort'=>I('post.sort')
           );
           $state=I('post.state');
           $data['state']=!empty($state) ? $state:1;
           if($this->ShopFloor->create()!==false)
           {
               $result = $this->ShopFloor->add($data);
               if ($result!==false)
               {
                   $this->success("保存成功");
               }
               else
               {
                   $this->error("保存失败！");
               }
               return;
           }
           else
           {
               $this->error($this->ShopFloor->getError());exit;
           }

       }


        $this->display('add');
    }
    public function edit()
    {
        if($_POST['act']=='update')
        {
            $id=I('post.id');
            $data=array(
                'chain_id'=>$this->chain_id,
                'shop_id'=>$this->shop_id,
                'floor'=>I('post.floor'),
                'floor_name'=>I('post.floor_name'),
                'sort'=>I('post.sort')
            );
           // $state=I('post.state');
            //$data['state']=!empty($state) ? 1:'0';
            $where=" chain_id=".session('chain_id')." and shop_id=".session('shop_id')." AND id!=".$id;
            if (unique($where,'floor_name',$data['floor_name'],$this->ShopFloor)== false)
            {
                $this->error("楼层名称不能重复！");
                exit;
            }
            else if(unique($where,'floor',$data['floor'],$this->ShopFloor)== false)
            {
                $this->error("楼层不能重复！");
                exit;
            }
            else
            {
                $res = $this->ShopFloor->where('id=' . $id)->save($data);
                if ($res !== false)
                {
                    $this->success("保存成功");
                }
                else
                {
                    $this->error("保存失败！");
                }
                return;
            }

         }
        if($_GET['id'])
        {

            $info= $this->ShopFloor->where('id='.$_GET['id'])->find();
            $this->assign('info',$info);
        }

        $this->display('edit');
    }
}
?>