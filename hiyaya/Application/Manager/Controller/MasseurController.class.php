<?php
// +----------------------------------------------------------------------
// | 门店列表页
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Author: Alina <309824428@qq.com>
// +----------------------------------------------------------------------
namespace Manager\Controller;
use Common\Controller\ManagerbaseController;
class MasseurController extends ManagerbaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->ShopMasseur = D("ShopMasseur");
        $this->chain_id=session('chain_id');
        $this->shop_id=session('shop_id');
        $this->assign('chain_id',session('chain_id'));
        $this->assign('shop_id',session('shop_id'));
        $item=M('ShopItem')->where('chain_id='.session('chain_id').' and shop_id='.session('shop_id'))->select();
        $this->assign('item',$item);
        $category=M('MasseurCategory')->where('chain_id='.session('chain_id').' and shop_id='.session('shop_id'))->select();
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
            $this->where.=" and  (nick_name like '%".$keywords."%' or masseur_name like '%".$keywords."%')";
        }
        if(I('get.tel'))
        {
            $tel= trim(I('get.tel'));
            $this->where.=" and  tel like '%".$tel."%'";
        }
        if(I('get.sex'))
        {
            $sex= trim(I('get.sex'));
            $this->where.=" and  sex ='".$sex."'";
        }
        // if(I('get.master_item'))
        // {
        //     $master_item= I('get.master_item');
        //     $this->where.=" and    find_in_set('".$master_item."',master_item)";
        // }
        if(I('get.category_id'))
        {
            $category_id= trim(I('get.category_id'));
            $this->where.=" and  category_id ='".$category_id."'";
        }

        $pagenum =!empty($limit) ? $limit :3;
        $pagecur = !empty($pagecur)? $pagecur:1;
        $list    = $this->ShopMasseur->where($this->where)->order('id')->page($pagecur.','.$pagenum)->select();
        foreach ($list as $key=> $val)
        {
            $list[$key]['chain_id'] =M('chain')->where('id='.$val['chain_id'])->getField('name');
            $list[$key]['shop_id'] =M('shop')->where('id='.$val['shop_id'])->getField('shop_name');
            $list[$key]['category_id'] =M('MasseurCategory')->where('id='.$val['category_id'])->getField('category_name');
            $list[$key]['createtime']=date("y-m-d H:i:s",$val['createtime']);
            if($val['state']==1)
            {
                $list[$key]['state']='启用';
            }
            else
            {
                $list[$key]['state']='锁定';
            }
        }
        $count   = $this->ShopMasseur->where($this->where)->count();
        $page    = new \Think\Page($count,$pagenum);// 实例化分页类 传入总记录数和每页显示的记录数
        $data=array("code"=>0,"msg"=>"","count"=>$count,"data"=>$list);
        echo json_encode($data);
    }
    public function isclose()
    {
        $id = I('post.id',0,'intval');
        $state =I('post.state',0,'intval');
        $data=array("state"=>$state);
        $res=$this->ShopMasseur->where('id='.$id)->save($data);
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
		$this->error("健康师禁止删除！");
        $res=$this->ShopMasseur->where("id=".$id)->find();
        if($res['state']==1){
            $this->error("启用中的技师禁止删除！");
        }
        if ($this->ShopMasseur->delete($id)!==false)
        {
            $this->success("删除成功");
        }
        else
        {

            $this->error("删除失败！");
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
	public function add()
    {
       if($_POST['act']=='add')
       {

		   $is_result=$this->ShopMasseur->where("shop_id=".$this->shop_id." and (masseur_name='".trim(I('post.masseur_name'))."' or nick_name='".trim(I('post.nick_name'))."' or tel='".trim(I('post.tel'))."')")->find();
		   if($is_result)
		   {
			   $this->error("姓名或昵称或电话重复");
		   }
           $data=array(
               'chain_id'=>$this->chain_id,
               'shop_id'=>$this->shop_id,
               'masseur_sn'=>trim(I('post.masseur_sn')),
               'masseur_name'=>preg_replace('# #','',I('post.masseur_name')),
			   'nick_name'=>preg_replace('# #','',I('post.nick_name')),
               'category_id'=>I('post.category_id','','intval'),
               'sex'=>trim(I('post.sex')),
               'tel'=>trim(I('post.tel')),
               'remark'=>trim(I('post.remark')),
               'cover'=>I('post.cover'),
               'createtime'=>time()
           );
           if(I('post.password'))
           {
               $password=I('post.password');
               $data['password']=sp_password(trim(I('post.password')));
           }
           if(is_array(I('post.master_item')))
           {
               $data['master_item']=implode(",",I('post.master_item'));
           }
           $state=I('post.state');
           $data['state']=!empty($state) ? $state:1;
           if($this->ShopMasseur->create()!==false)
           {
              $result = $this->ShopMasseur->add($data);
              $this->ShopMasseur->where("id=".$result)->save(array('token' => md5($result.time().'lovefat')));
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
               $this->error($this->ShopMasseur->getError());exit;
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
                'masseur_sn'=>trim(I('post.masseur_sn')),
                'masseur_name'=>preg_replace('# #','',I('post.masseur_name')),
			    'nick_name'=>preg_replace('# #','',I('post.nick_name')),
                'category_id'=>I('post.category_id','','intval'),
				'tel'=>trim(I('post.tel')),
                'sex'=>trim(I('post.sex')),
                'remark'=>trim(I('post.remark')),
                'createtime'=>time()
            );
            if(is_array(I('post.master_item')))
            {
                $data['master_item']=implode(",",I('post.master_item'));
            }
            if(I('post.password'))
            {
                $password=I('post.password');
                //$data['password']=!empty($password) ? md5(md5($password)):'';
                $data['password']=sp_password(trim(I('post.password')));
            }


            if(I('post.cover'))
            {
                $data['cover']=I('post.cover');
            }
            //$state=I('post.state');
           // $data['state']=!empty($state) ? 1:'0';
            $this->where.=" AND id!=".$id;
            if (unique($this->where,'masseur_sn',$data['masseur_sn'],$this->ShopMasseur)== false)
            {
                $this->error("技师工号不能重复！");
                exit;
            }
           
            else
            {
                $res = $this->ShopMasseur->where('id=' . $id)->save($data);
                if ($res !== false)
                {
                    $this->ShopMasseur->where("id=".$id)->save(array('token' => md5($res.time().'lovefat')));
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

            $info= $this->ShopMasseur->where('id='.$_GET['id'])->find();
            $info['master_item']=explode(',',($info['master_item']));
            $this->assign('info',$info);
        }
        $this->display('edit');
    }
}
?>