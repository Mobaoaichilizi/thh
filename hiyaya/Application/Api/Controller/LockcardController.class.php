<?php
// +----------------------------------------------------------------------
// | 锁牌列表页
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Author: Th2 <2791919673@qq.com>
// +----------------------------------------------------------------------
namespace Api\Controller;
use Common\Controller\ApibaseController;
class LockcardController extends ApibaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->shoplockcard = M("ShopLockcard");//锁牌表
        $this->where="shop_id=".$this->shop_id;

    }
    //锁牌列表
    public function index()
    {
        $pagecur=!empty($_POST['pagecur']) ? $_POST['pagecur'] : 1;//当前第几个页
        $pagesize=!empty($_POST['pagesize']) ? $_POST['pagesize'] : 10;//每页显示个数
        $pagecur=($pagecur-1)*$pagesize;
        $keywords = trim(I('post.keywords','','htmlspecialchars'));
        if(!empty($keywords))
        {
            $this->where.=" and card_member like '%".$keywords."%'";
        }
        $result = $this->shoplockcard->field('id,card_number,is_lock')->where($this->where.' and status=1')->order('sort asc,id desc')->limit($pagecur.",".$pagesize)->select();
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