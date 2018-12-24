<?php
// +----------------------------------------------------------------------
// | 散客列表页
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Author: Th2 <2791919673@qq.com>
// +----------------------------------------------------------------------
namespace Api\Controller;
use Common\Controller\ApibaseController;
class GuestController extends ApibaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->shopguest = M("ShopGuest");//散客表
        $this->shopmember = M("ShopMember");//会员表
        $this->where="shop_id=".$this->shop_id;
        $this->user = $session_user['id'];

    }
    //会员列表
    public function index()
    {
        $pagecur=!empty($_POST['pagecur']) ? $_POST['pagecur'] : 1;//当前第几个页
        $pagesize=!empty($_POST['pagesize']) ? $_POST['pagesize'] : 10;//每页显示个数
        $pagecur=($pagecur-1)*$pagesize;
        $keywords = trim(I('post.keywords','','htmlspecialchars'));
        if(!empty($keywords))
        {
            $this->where.=" and member_name like '%".$keywords."%'";
        }
        $result = $this->shopmember->field('id,member_name,member_tel')->where($this->where.' and state=1 and identity=2')->order('id desc')->limit($pagecur.",".$pagesize)->select();

        if($result){
            unset($data);
            $data['code'] = 0;
            $data['msg'] = 'success';
            $data['data'] = $result;
            outJson($data);
        }else{
            unset($data);
            $data['code'] = 0;
            $data['msg'] = 'success';
            $data['data'] = array();
            outJson($data);
        }
       
        
    }
    
    

}
?>