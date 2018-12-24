<?php
// +----------------------------------------------------------------------
// | 散客列表页
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Author: Alina <309824428@qq.com>
// +----------------------------------------------------------------------
namespace Manager\Controller;
use Common\Controller\ManagerbaseController;
class GuestController extends ManagerbaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->ShopGuest = M("ShopGuest");
        $this->shopmember = M("ShopMember");
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
            $where.="  and member_name like '%".$keywords."%'";
        }
        if(I('get.member_tel'))
        {
            $member_tel= I('get.member_tel');
            $where.=" and   member_tel like '%".$member_tel."%'";
        }

        $pagenum =!empty($limit) ? $limit :3;
        $pagecur = !empty($pagecur)? $pagecur:1;
        $list    = $this->shopmember->where($where.' and identity=2')->order('createtime desc,id desc')->page($pagecur.','.$pagenum)->select();
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
        }
        $count   = $this->shopmember->where($where.' and identity=2')->count();
        $page    = new \Think\Page($count,$pagenum);// 实例化分页类 传入总记录数和每页显示的记录数
        $data=array("code"=>0,"msg"=>"","count"=>$count,"data"=>$list);
        echo json_encode($data);
    }
    public function close()
    {

        $roleid   =I('post.id');
        // $data=array("state"=>0);
        // $res=$this->ShopGuest->where('id='.$roleid)->save($data);
        $res=$this->shopmember->where('id='.$roleid)->delete();
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
                'member_no'=>I('post.member_no'),
                'member_name'=>I('post.member_name'),
                'member_tel'=>I('post.member_tel'),
                'sex'=>I('post.sex'),
                'identity'=>2,
                'remark'=>I('post.remark'),
                'createtime'=>time()
            );
            $is_msg=I('post.is_msg');
            $data['is_msg']=!empty($is_msg) ? 1:'0';
            // $state=I('post.state');
            // $data['state']=!empty($state) ? 1:0;
            if($this->shopmember->add($data))
            {
                echo 'success';
            }
            else
            {
                echo 'error';
            }
            return;
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
                'member_no'=>I('post.member_no'),
                'member_name'=>I('post.member_name'),
                'member_tel'=>I('post.member_tel'),
                'sex'=>I('post.sex'),
                'remark'=>I('post.remark')
            );
            $is_msg=I('post.is_msg');
            $data['is_msg']=!empty($is_msg) ? 1:'0';
            $state=I('post.state');
            $data['state']=!empty($state) ? 1:'0';
            $res=$this->shopmember->where('id='.$id)->save($data);
            if($res)
            {
                echo 'success';
            }
            else
            {
                echo 'error';
            }
            return;
        }
        if($_GET['id'])
        {
            $info= $this->shopmember->where('id='.$_GET['id'])->find();
            $this->assign('info',$info);
        }
        $this->display('edit');
    }
    // public function become_member(){
    //     $id = I('post.id');
    //     $result = $this->shopmember->where('id='.$id)->save(array('identity'=>1,'createtime'=>time()));
    //     if($result !== false){
    //         echo 'success';
    //         // $this->success('添加会员成功',U('Member/index'));
    //     }else{
    //         echo 'error';
    //         // $this->success('添加会员失败',U('Guest/index'));
    //     }
    // }
    //成为会员
    public function become_member(){
        if(IS_POST){
            $id = I('post.id');
            $sex = I('post.sex');
            $is_msg = I('post.is_msg');
            $discount = I('post.discount',0);
            $result = $this->shopmember->where('id='.$id)->save(array('identity'=>1,'createtime'=>time(),'is_msg'=>$is_msg,'discount'=>$discount,'sex'=>$sex));
            if($result !== false){
                echo 'success';
                // $this->success('添加会员成功',U('Member/index'));
            }else{
                echo 'error';
                // $this->error('添加会员失败',U('Guest/index'));
            }
        }else{
            $id = I('get.id');
            $info = $this->shopmember->where('id='.$id)->find();
            $this->assign('info',$info);
            $this->display();
        }
        
    }
}
?>