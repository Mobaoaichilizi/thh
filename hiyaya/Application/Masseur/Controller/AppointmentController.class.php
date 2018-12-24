<?php
// +----------------------------------------------------------------------
// | 预约列表页
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Author: Th2 <2791919673@qq.com>
// +----------------------------------------------------------------------
namespace Masseur\Controller;
use Common\Controller\MasseurbaseController;
class AppointmentController extends MasseurbaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->shopappointment = M("ShopAppointment");//预约表
        $this->shoproom = M("ShopRoom");//房间表
        $this->shopbed = M("ShopBed");//床位表
        $this->shopmasseur = M("ShopMasseur");//技师表
        $this->shopitem = M("ShopItem");//项目表
        $this->roomcategory = M("RoomCategory");//包间分类表
        $this->shopscheduling=M("ShopScheduling"); //排班表
        $this->ordersproject=M("OrdersProject"); //订单项目表
        $this->where="shop_id=".$this->shop_id;

    }
    //预约列表
    public function index()
    {
        $pagecur=!empty($_POST['pagecur']) ? $_POST['pagecur'] : 1;//当前第几个页
        $pagesize=!empty($_POST['pagesize']) ? $_POST['pagesize'] : 10;//每页显示个数
        $pagecur=($pagecur-1)*$pagesize;
        $result = $this->shopappointment->field('id,phone,name,male_num,female_num,reservation_time,room_id,masseur_id,project_id,is_lock,order_money')->where($this->where.' and status=1 and masseur_id='.$this->masseur_id)->order('createtime asc,id desc')->limit($pagecur.",".$pagesize)->select();
        $count = $this->shopappointment->field('id,phone,name,male_num,female_num,reservation_time,room_id,masseur_id,project_id,is_lock,order_money')->where($this->where.' and status=1 and masseur_id='.$this->masseur_id)->order('createtime asc,id desc')->count();
        if($result){
            foreach ($result as $key => $value) {
                $info[$key]['id'] = $value['id'];
                $info[$key]['phone'] = $value['phone'];
                $info[$key]['name'] = $value['name'];
                $info[$key]['male_num'] = $value['male_num'];
                $info[$key]['female_num'] = $value['female_num'];
                $info[$key]['reservation_time'] = $value['reservation_time'];
                $info[$key]['is_lock'] = $value['is_lock'];
                $info[$key]['order_money'] = $value['order_money'];
                if(!empty($value['room_id'])){
                    $info[$key]['room_name'] = $this->shoproom->where('id='.$value['room_id'])->getfield('room_name');

                }
                if(!empty($value['masseur_id'])){
                    $info[$key]['masseur_sn'] = $this->shopmasseur->where('id='.$value['masseur_id'])->getfield('masseur_sn');

                }
                if(!empty($value['project_id'])){
                    $info[$key]['project_name'] = $this->shopitem->where('id='.$value['project_id'])->getfield('item_name');
                    $info[$key]['cover'] = $this->shopitem->where('id='.$value['project_id'])->getfield('cover');
                    if(!empty($info[$key]['cover'])){
                        $info[$key]['cover'] = $this->host_url.$info[$key]['cover'];
                    }

                }

            }
            unset($data);
            $data['code'] = 0;
            $data['msg'] = 'success';
            $data['data'] = $info;
            $data['count'] = $count;
            outJson($data);
            
        }else{
            unset($data);
            $data['code'] = 0;
            $data['msg'] = 'success';
            $data['data'] = array();
            $data['count'] = 0;
            outJson($data);
        }
       
        
    }
    
}
?>