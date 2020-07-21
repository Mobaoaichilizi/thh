<?php
// +----------------------------------------------------------------------
// | 门店列表页
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Author: Alina <309824428@qq.com>
// +----------------------------------------------------------------------
namespace Manager\Controller;
use Common\Controller\ManagerbaseController;
class CikaController extends ManagerbaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->ShopCika = D("ShopCika");
        $this->chain_id=session('chain_id');
        $this->shop_id=session('shop_id');
        $this->assign('chain_id',session('chain_id'));
        $this->assign('shop_id',session('shop_id'));
        $this->where=" chain_id=".session('chain_id')." and shop_id=".session('shop_id');
        $item=M('ShopItem')->where('chain_id='.session('chain_id').' and shop_id='.session('shop_id'))->select();
        $this->assign('item',$item);
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
            $keywords=trim(I('get.keywords'));
            $this->where.=" and   package_name like '%".$keywords."%'";
        }
        if(I('get.category_id'))
        {
            $category_id= I('get.category_id');
            $this->where.=" AND category_id=".$category_id;
        }
        $pagenum =!empty($limit) ? $limit :3;
        $pagecur = !empty($pagecur)? $pagecur:1;
        $list    = $this->ShopCika->where($this->where)->order('id')->page($pagecur.','.$pagenum)->select();
        foreach ($list as $key=> $val)
        {
            $list[$key]['chain_id'] =M('chain')->where('id='.$val['chain_id'])->getField('name');
            $list[$key]['shop_id'] =M('shop')->where('id='.$val['shop_id'])->getField('shop_name');
            $list[$key]['createtime']=date("y-m-d H:i:s",$val['createtime']);
            if($val['state']==1)
            {
                $list[$key]['state']='启用';
            }
            else
            {
                $list[$key]['state']='锁定';
            }
//            if($val['is_discount']==1)
//            {
//                $list[$key]['is_discount']='开';
//            }
//            else
//            {
//                $list[$key]['is_discount']='关';
//            }
        }
        $count   = $this->ShopCika->where($this->where)->count();
        $page    = new \Think\Page($count,$pagenum);// 实例化分页类 传入总记录数和每页显示的记录数
        $data=array("code"=>0,"msg"=>"","count"=>$count,"data"=>$list);
        echo json_encode($data);
    }

    //启用
    public function open()
    {
        $id = I('post.id',0,'intval');
        $data=array("state"=>1);
        $res=$this->ShopCika->where('id='.$id)->save($data);
        if($res)
        {
            echo 'success';
        }
        else
        {
            echo 'error';
        }
    }
    //锁定
    public function close()
    {

        $id = I('post.id',0,'intval');
        $data=array("state"=>0);
        $res=$this->ShopCika->where('id='.$id)->save($data);
        if($res)
        {
            echo 'success';
        }
        else
        {
            echo 'error';
        }
    }
    //是否开启会员打折
    public function isdiscount()
    {

        $id = I('post.id',0,'intval');
        $is_discount =I('post.is_discount',0,'intval');
        $data=array("is_discount"=>$is_discount);
        $res=$this->ShopCika->where('id='.$id)->save($data);
        if($res)
        {
            $this->success("成功");
        }
        else
        {
            $this->error("失败！");
        }
    }
    public function del()
    {
        $id = I('post.id',0,'intval');
        $res=$this->ShopCika->where("id=".$id)->find();
        if($res['state']==1){
            $this->error("启用中的会员卡套餐禁止删除！");
        }
        if ($this->ShopCika->delete($id)!==false)
        {
            $this->success("删除成功");
        }
        else
        {

            $this->error("删除失败！");
        }
    }
	public function add()
    {
       if($_POST['act']=='add')
       {

           $data=array(
               'chain_id'=>$this->chain_id,
               'shop_id'=>$this->shop_id,
               'package_name'=>trim(I('post.package_name')),
               'package_amount'=>I('post.package_amount'),
               'give_amount'=>I('post.give_amount'),
               'item_id'=>I('post.item_id'),
               'item_num'=>I('post.item_num'),
               'rec_reward'=>I('post.rec_reward'),
               'createtime'=>time()
           );
           $state=I('post.state');
           $data['state']=!empty($state) ? $state:1;
           $is_discount=I('post.is_discount');
           $data['is_discount']=!empty($is_discount) ? 1:'0';
           if($this->ShopCika->create()!==false)
           {
               if ($this->ShopCika->add($data))
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
               $this->error($this->ShopCika->getError());exit;
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
                'package_name'=>trim(I('post.package_name')),
                'package_amount'=>I('post.package_amount'),
                'item_id'=>I('post.item_id'),
                'item_num'=>I('post.item_num'),
                'give_amount'=>I('post.give_amount'),
                'rec_reward'=>I('post.rec_reward'),
            );
            //$state=I('post.state');
            //$data['state']=!empty($state) ? 1:'0';
//            $is_discount=I('post.is_discount');
//            $data['is_discount']=!empty($is_discount) ? 1:'0';
            $this->where.=" AND id!=".$id;
            if (unique($this->where,'package_name',$data['package_name'],$this->ShopCika)== false)
            {
                $this->error("套餐名称已经存在！");
                exit;
            }
            else
            {
                $res = $this->ShopCika->where('id=' . $id)->save($data);
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

            $info= $this->ShopCika->where('id='.$_GET['id'])->find();

            $this->assign('info',$info);
        }
        $this->display('edit');
    }
}
?>