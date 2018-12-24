<?php
// +----------------------------------------------------------------------
// | 门店列表页
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Author: Alina <309824428@qq.com>
// +----------------------------------------------------------------------
namespace Manager\Controller;
use Common\Controller\ManagerbaseController;
class ProductController extends ManagerbaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->ShopProduct = D("ShopProduct");
        $this->chain_id=session('chain_id');
        $this->shop_id=session('shop_id');
        $this->assign('chain_id',session('chain_id'));
        $this->assign('shop_id',session('shop_id'));
        $category=M('ProductCategory')->where('chain_id='.session('chain_id').' and shop_id='.session('shop_id'))->select();
        $this->assign('category',$category);
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
            $this->where.=" AND product_name like '%".$keywords."%'";
        }
        $pagenum =!empty($limit) ? $limit :3;
        $pagecur = !empty($pagecur)? $pagecur:1;
        $list    = $this->ShopProduct->where($this->where)->order('id')->page($pagecur.','.$pagenum)->select();
        foreach ($list as $key=> $val)
        {
            $list[$key]['chain_id'] =M('chain')->where('id='.$val['chain_id'])->getField('name');
            $list[$key]['shop_id'] =M('shop')->where('id='.$val['shop_id'])->getField('shop_name');
            $list[$key]['category_id'] =M('ProductCategory')->where('id='.$val['category_id'])->getField('category_name');
            if($val['state']==1)
            {
                $list[$key]['state']='启用';
            }
            else
            {
                $list[$key]['state']='锁定';
            }
            if($val['is_discount']==1)
            {
                $list[$key]['is_discount']='是';
            }
            else
            {
                $list[$key]['is_discount']='否';
            }
        }
        $count   = $this->ShopProduct->where($this->where)->count();
        $page    = new \Think\Page($count,$pagenum);// 实例化分页类 传入总记录数和每页显示的记录数
        $data=array("code"=>0,"msg"=>"","count"=>$count,"data"=>$list);
        echo json_encode($data);
    }
    //启用
    public function open()
    {
        $id = I('post.id',0,'intval');
        $data=array("state"=>1);
        $res=$this->ShopProduct->where('id='.$id)->save($data);
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
        $res=$this->ShopProduct->where('id='.$id)->save($data);
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
        $is_discount = I('post.is_discount');
        if(intval($is_discount)==1)
        {
            $data=array("is_discount"=>1);
        }
        else
        {
            $data=array("is_discount"=>0);
        }

        $res=$this->ShopProduct->where('id='.$id)->save($data);
      // echo  $this->ShopProduct->getLastSql();
        if($res)
        {
            $this->success("成功");
        }
        else
        {
            $this->error("失败！");
        }
    }

    public function upload(){
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
        $upload->savePath  =     ''; // 设置附件上传（子）目录
        // 上传文件
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
        }else{// 上传成功

            // 取得成功上传的文件信息

            // 保存当前数据对象
            echo json_encode(array('cover'=> $upload->rootPath.$info['file']['savepath'].$info['file']['savename'],'state'=>'success','code'=>0));
            // $this->success('上传成功！');
        }
    }
	//单个删除
    public function del()
    {
        $id = I('post.id',0,'intval');
        $res=$this->ShopProduct->where("id=".$id)->find();
        if($res['state']==1){
            $this->error("启用中的产品禁止删除！");
        }
        if ($this->ShopProduct->delete($id)!==false)
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
               'product_sn'=>trim(I('post.product_sn')),
               'product_name'=>trim(I('post.product_name')),
               'product_price'=>I('post.product_price'),
               'cover'=>I('post.cover'),
               'category_id'=>I('post.category_id'),
               'rec_reward'=>I('post.rec_reward'),
               'createtime'=>time()

           );
           $is_discount=I('post.is_discount');
           $data['is_discount']=$is_discount=='on' ? 1:0;

           if($this->ShopProduct->create()!==false)
           {
               if ($this->ShopProduct->add($data))
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
               $this->error($this->ShopProduct->getError());exit;
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
                'product_sn'=>trim(I('post.product_sn')),
                'product_name'=>trim(I('post.product_name')),
                'product_price'=>I('post.product_price'),
                'category_id'=>I('post.category_id'),
                'rec_reward'=>I('post.rec_reward')
            );
            if(I('post.cover'))
            {
                $data['cover']=I('post.cover');
            }
            $is_discount=I('post.is_discount');
            $data['is_discount']=$is_discount=='on' ? 1:'0';
            $this->where.=" AND id!=".$id;
            if (unique($this->where,'product_sn',$data['product_sn'],$this->ShopProduct)== false)
            {
                $this->error("产品编号不能重复！");
                exit;
            }
            else if(unique($this->where,'product_name',$data['product_name'],$this->ShopProduct)== false)
            {
                $this->error("产品名称不能重复！");
                exit;
            }
            else
            {
                $res = $this->ShopProduct->where('id=' . $id)->save($data);
                //echo $this->ShopProduct->getLastSql();
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
            $info= $this->ShopProduct->where('id='.$_GET['id'])->find();
            $this->assign('info',$info);
        }
        $this->display('edit');
    }
}
?>