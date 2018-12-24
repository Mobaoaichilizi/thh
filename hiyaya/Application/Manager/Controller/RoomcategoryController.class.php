<?php
// +----------------------------------------------------------------------
// | 门店列表页
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Author: Alina <309824428@qq.com>
// +----------------------------------------------------------------------
namespace Manager\Controller;
use Common\Controller\ManagerbaseController;
class RoomcategoryController extends ManagerbaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->RoomCategory = D("RoomCategory");
        $this->chain_id=session('chain_id');
        $this->shop_id=session('shop_id');
        $this->assign('chain_id',session('chain_id'));
        $this->assign('shop_id',session('shop_id'));
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
            $keywords=trim(I('get.keywords'));
            $this->where.=" and  category_name like '%".$keywords."%'";
        }
        $pagenum =!empty($limit) ? $limit :3;
        $pagecur = !empty($pagecur)? $pagecur:1;
        $list    = $this->RoomCategory->where($this->where)->order('id')->page($pagecur.','.$pagenum)->select();
        foreach ($list as $key=> $val)
        {
            $list[$key]['chain_id'] =M('chain')->where('id='.$val['chain_id'])->getField('name');
            $list[$key]['shop_id'] =M('shop')->where('id='.$val['shop_id'])->getField('shop_name');
        }
        $count   = $this->RoomCategory->where($this->where)->count();
        $page    = new \Think\Page($count,$pagenum);// 实例化分页类 传入总记录数和每页显示的记录数
        $data=array("code"=>0,"msg"=>"","count"=>$count,"data"=>$list);
        echo json_encode($data);
    }
	public function close()
	{


        $id   =I('post.id',0,'intval');
        $data=array("state"=>0);
        $count=M('ShopRoom')->where("category_id=".$id)->count();
        if($count>0)
        {
            $this->error("此分类下面还有包房信息，不能删除！");
            return;
        }
        $res=$this->RoomCategory->delete($id);
        if($res)
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
               'category_name'=>trim(I('post.category_name')),
               'sort'=>I('post.sort')
           );

           if($this->RoomCategory->create()!==false)
           {
               if ($this->RoomCategory->add($data))
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
               $this->error($this->RoomCategory->getError());exit;
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
                'category_name'=>trim(I('post.category_name')),
                'sort'=>I('post.sort')
            );

            $this->where.=" AND id!=".$id;
            if (unique($this->where,'category_name',$data['category_name'],$this->RoomCategory)== false)
            {
                $this->error("包间分类名称不能重复！");
                exit;
            }
            else
            {
                $res = $this->RoomCategory->where('id=' . $id)->save($data);
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
            $info= $this->RoomCategory->where('id='.$_GET['id'])->find();
            $this->assign('info',$info);
        }
        $this->display('edit');
    }
}
?>