<?php
// +----------------------------------------------------------------------
// | 门店列表页
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Author: Alina <309824428@qq.com>
// +----------------------------------------------------------------------
namespace Manager\Controller;
use Common\Controller\ManagerbaseController;
class RoomController extends ManagerbaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->ShopRoom = D("ShopRoom");
        $this->ShopBed = M("ShopBed");
        $this->chain_id=session('chain_id');
        $this->shop_id=session('shop_id');
        $this->assign('chain_id',session('chain_id'));
        $this->assign('shop_id',session('shop_id'));
        $category=M('RoomCategory')->where('chain_id='.session('chain_id').' and shop_id='.session('shop_id'))->select();
        $floor=M('ShopFloor')->where('chain_id='.session('chain_id').' and shop_id='.session('shop_id'))->select();
        $this->assign('category',$category);
        $this->assign('floors',$floor);
        $this->where=" chain_id=".session('chain_id')." and shop_id=".session('shop_id');
    }
    public function index()
    {
        $this->display('index');
    }
    public function json()
    {

        $limit   =I('get.limit');
        $pagecur=I('get.page');

        if(I('get.keywords'))
        {
            $keywords= trim(I('get.keywords'));
            $this->where.=" and  room_name like '%".$keywords."%'";
        }
        if(I('get.category_id'))
        {
            $category_id= I('get.category_id');
            $this->where.=" AND category_id=".$category_id;
        }

        $pagenum =!empty($limit) ? $limit :3;
        $pagecur = !empty($pagecur)? $pagecur:1;
        $list    = $this->ShopRoom->where($this->where)->order('id')->page($pagecur.','.$pagenum)->select();
        foreach ($list as $key=> $val)
        {
            $list[$key]['chain_id']      =M('chain')->where('id='.$val['chain_id'])->getField('name');
            $list[$key]['shop_id']       =M('shop')->where('id='.$val['shop_id'])->getField('shop_name');
            $list[$key]['category_id']   =M('RoomCategory')->where('id='.$val['category_id'])->getField('category_name');
            //$list[$key]['createtime']=date("y-m-d H:i:s",$val['createtime']);
            if($val['state']==1)
            {
                $list[$key]['state']='空净';
            }
            else if($val['state']==0)
            {
                $list[$key]['state']='锁定';
            }
            else if($val['state']==2)
            {
                $list[$key]['state']='占用';
            }
            else if($val['state']==3)
            {
                $list[$key]['state']='待扫';
            }
            else if($val['state']==4)
            {
                $list[$key]['state']='维修';
            }
            else if($val['state']==5)
            {
                $list[$key]['state']='留房';
            }
            else if($val['state']==6)
            {
                $list[$key]['state']='住房';
            }
            else if($val['state']==7)
            {
                $list[$key]['state']='休息';
            }
        }
        $count   = $this->ShopRoom->where($this->where)->count();
        $page    = new \Think\Page($count,$pagenum);// 实例化分页类 传入总记录数和每页显示的记录数
        $data=array("code"=>0,"msg"=>"","count"=>$count,"data"=>$list);
        echo json_encode($data);
    }
	public function close()
	{
        $id = I('post.id',0,'intval');
        $data=array("state"=>0);
        $res=$this->ShopRoom->where('id='.$id)->save($data);
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
        $res=$this->ShopRoom->where("id=".$id)->find();
        if($res['state']==1){
            $this->error("启用中的包间禁止删除！");
        }
        if ($this->ShopRoom->delete($id)!==false)
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
        $res=$this->ShopRoom->where('id='.$id)->save($data);
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
           $state=I('post.state');
           $data=array(
               'chain_id'=>$this->chain_id,
               'shop_id'=>$this->shop_id,
               'room_name'=>trim(I('post.room_name')),
               'category_id'=>I('post.category_id'),
               'room_capacity'=>I('post.room_capacity'),
               'floor_id'=>I('post.floor_id'),
               'createtime'=>time(),
               'state'=>$state,
           );

           // $data['state']=!empty($state) ? 1:0;
           if($this->ShopRoom->create()!==false)
           {
               if ($this->ShopRoom->add($data))
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
               $this->error($this->ShopRoom->getError());exit;
           }
       }


        $this->display('add');
    }
    public function editbed()
    {
        if($_GET['id'])
        {

            $list= $this->ShopBed->where('id='.$_GET['id'])->select();
            $this->assign('list',$list);
        }
        $this->display('editbed');
    }
    public function edit()
    {
        if($_POST['act']=='update')
        {
            $id=I('post.id');
            $data=array(
                'chain_id'=>$this->chain_id,
                'shop_id'=>$this->shop_id,
                'room_name'=>trim(I('post.room_name')),
                'floor_id'=>I('post.floor_id'),
                'category_id'=>I('post.category_id'),
                'room_capacity'=>I('post.room_capacity'),
                'state'=>I('post.state'),
            );
            //$state=I('post.state');
            // $data['state']=!empty($state) ? 1:0;
            $this->where.=" AND id!=".$id;
            if(unique($this->where,'room_name',$data['room_name'],$this->ShopRoom)== false)
            {
                $this->error("包间名称不能重复！");
                exit;
            }
            else
            {
                $res = $this->ShopRoom->where('id=' . $id)->save($data);
                //echo $this->ShopRoom->getLastSql();
                //die();
                if ($res !== false) {
                    $this->success("保存成功");
                } else {
                    $this->error("保存失败！");
                }
                return;
            }
         }
        if($_GET['id'])
        {

            $info= $this->ShopRoom->where('id='.$_GET['id'])->find();
            $this->assign('info',$info);
        }
        $this->display('edit');
    }
}
?>