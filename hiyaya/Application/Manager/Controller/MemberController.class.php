<?php
// +----------------------------------------------------------------------
// | 会员列表页
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Author: HT2 <2791919673@qq.com>
// +----------------------------------------------------------------------
namespace Manager\Controller;
use Common\Controller\ManagerbaseController;
class MemberController extends ManagerbaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->ShopMember = D("ShopMember");//会员表
        $this->chain_id=session('chain_id');
        $this->shop_id=session('shop_id');
        $this->user_id=session('user_id');
        $this->ShopPackage = D("ShopPackage");//会员卡套餐表
        $this->ShopCika = D("ShopCika");//次卡套餐表
        $this->ShopQixian = D("ShopQixian");//期限卡套餐表
        $this->ShopLiaocheng = D("ShopLiaocheng");//疗程卡套餐表
        $this->financial = D("ShopFinancial");//明细表
        $this->numcard = D("ShopNumcard");//次卡表
        $this->deadlinecard = D("ShopDeadlinecard");//期限卡表
        $this->coursecard = D("ShopCoursecard");//疗程卡表
        $this->product = D("ShopProduct");//产品表
        $this->charge = D("ShopCharge");//会员卡充值表
        $this->item = D("ShopItem");//项目表
        $this->setting = D("Setting");//分类表
        $this->courseproject = D("ShopCourseproject");//疗程卡关联项目表
        $this->ShopMasseur = D("ShopMasseur");//技师表
        $this->ShopMemberlabel = D('ShopMemberlabel');//会员标签表
        $this->numcardfinancial = D("ShopNumcardfinancial");//次卡明细表
        $this->deadlinefinancial = D("ShopDeadlinefinancial");//期限卡明细表
        $this->coursefinancial = D("ShopCoursefinancial");//疗程卡明细表
		$this->shop=M('Shop');
    }
    public function index()
    {
        $this->display('index');
    }
    public function json()
    {

        $limit   =I('get.limit');
        $pagecur=I('get.page');
        $where=" chain_id=".$this->chain_id;
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
		if(I('get.member_no'))
        {
            $member_no= I('get.member_no');
            $where.=" and  member_no like '%".$member_no."%'";
        }
        if(I('get.card_type'))
        {
            $card_type= I('get.card_type');
            if($card_type == '1'){//次卡
                $ids = $this->numcard->where(" chain_id=".$this->chain_id.' and card_num>0')->group("member_id")->getfield('member_id',true);
            }else if($card_type == '2'){//疗程卡
                $ids = $this->coursecard->where(" chain_id=".$this->chain_id)->group("member_id")->getfield('member_id',true);
            }else if($card_type == '3'){//期限卡
                $ids = $this->deadlinecard->where(" chain_id=".$this->chain_id.' and end_time>'.time())->group("member_id")->getfield('member_id',true);
                
            }
            if(!empty($ids)){
                $ids = implode(',', $ids);
                $where.=" and id in (".$ids.")";
            }else{
                $where.=" and id=''";
            }
            
        }

        $pagenum =!empty($limit) ? $limit :3;
        $pagecur = !empty($pagecur)? $pagecur:1;
        $list    = $this->ShopMember->where($where.' and identity=1')->order('createtime desc,id desc')->page($pagecur.','.$pagenum)->select();
        // dump($this->ShopMember->getlastsql());
        foreach ($list as $key=> $val)
        {
            $list[$key]['chain_id'] =M('chain')->where('id='.$val['chain_id'])->getField('name');
            $list[$key]['shop_id'] =M('shop')->where('id='.$val['shop_id'])->getField('shop_name');
            $list[$key]['createtime']=date("y-m-d H:i:s",$val['createtime']);
            if($val['is_msg']==1)
            {
                $list[$key]['is_msg']='开';
            }
            else
            {
                $list[$key]['is_msg']='关';
            }
            if($val['state']==1)
            {
                $list[$key]['state']='启用';
            }
            else
            {
                $list[$key]['state']='锁定';
            }
        }
        $count   = $this->ShopMember->where($where.' and identity=1')->count();
        $page    = new \Think\Page($count,$pagenum);// 实例化分页类 传入总记录数和每页显示的记录数
        $data=array("code"=>0,"msg"=>"","count"=>$count,"data"=>$list);
        echo json_encode($data);
    }
    public function isclose()
    {
        $id = I('post.id',0,'intval');
        $state =I('post.state',0,'intval');
        $data=array("state"=>$state);
        $res=$this->ShopMember->where('id='.$id)->save($data);
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
               'birthday'=>I('post.birthday'),
               'remark'=>I('post.remark'),
               'member_card'=>I('post.member_card'),
               'state'=>I('post.state'),
               'sex'=>I('post.sex'),
               'createtime'=>time()
            );
            $data['is_msg'] = I('post.is_msg',0,'intval');
            if(!empty($data['is_msg'])){
                $data['discount'] = I('post.discount');
            }
            $member_card = I('post.member_card');
            if(!empty($member_card)){
                $count = $this->ShopMember->where('chain_id='.$this->chain_id." and member_card='".$member_card."'")->count();

                if($count > 0){
                    $this->error('此实体卡号已绑定会员');
                } 
            }
            $member_tel = I('post.member_tel');
            $count1 = $this->ShopMember->where('chain_id='.$this->chain_id.' and member_tel='.$member_tel)->count();
            if($count1 > 0){
                $this->error('此手机号码已绑定会员');
            }
           if($this->ShopMember->create()!==false){
               if($this->ShopMember->add($data))
               {
                  // echo 'success';
                $this->success("添加成功！", U("Member/index"));
               }
               else
               {
                   // echo 'error';
                $this->error('添加失败!');
               }
           }else{
                $this->error($this->ShopMember->getError());exit;

           }
       }
		$shop_list=$this->shop->where("id=".$this->shop_id)->find();
		
        $member_no = get_member_sn($shop_list['start_id'],$shop_list['id']);
	
        $this->assign('member_no',$member_no);
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
                'birthday'=>I('post.birthday'),
                'remark'=>I('post.remark'),
                'member_card'=>I('post.member_card'),
                'state'=>I('post.state'),
                'sex'=>I('post.sex')
            );
            $is_msg=I('post.is_msg');
            $data['is_msg']=!empty($is_msg) ? 1:'0';
            if($this->ShopMember->create()!==false){
                $res=$this->ShopMember->where('id='.$id)->save($data);
                if($res)
                {
                    // echo 'success';
                    $this->success("编辑成功！", U("Member/index"));
                }
                else
                {
                    // echo 'error';
                    $this->error('编辑失败!');
                }
            }else{
                $this->error($this->ShopMember->getError());exit;
            }
         }
        if($_GET['id'])
        {
            $info= $this->ShopMember->where('id='.$_GET['id'])->find();
            $this->assign('info',$info);
        }
        $this->display('edit');
    }

    public function del()
    {
        $id = I('post.id',0,'intval');
        $this->error("会员不能删除！");
        if ($this->ShopMember->delete($id)!==false) {
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
    }
   
    //会员详情页面
    public function detail(){
        $id = I('get.id',0,'intval');
        $info= $this->ShopMember->where('id='.$id)->find();
        $this->assign('result',$info);
        $this->display('detail');
    }
    //会员详情接口
    public function info(){
        $id = I('get.id',0,'intval');
        $where="chain_id=".$this->chain_id." and shop_id=".$this->shop_id;
        $info['balance'] = $this->ShopMember->where('id='.$id)->getField('balance');
        $member = $this->ShopMember->where('id='.$id)->find();
        if(!empty($member['is_msg'])){
            $info['balance'].='('.$member['discount'].'折卡)';
        }else{
            $info['balance'].='(无折扣卡)';
        }
        $info['last_charge_time'] = $this->charge->where($where.' and member_id='.$id)->order('createtime desc')->limit(1)->getField('createtime');
        if($info['last_charge_time']){
            $info['last_charge_time'] = date('Y-m-d H:i:s', $info['last_charge_time']);
        }else{
            $info['last_charge_time'] = '暂无数据';
        }
        
        $total_charge_money = $this->charge->where($where.' and member_id='.$id.' and status=1')->getField('recharge_money',true);
        $total_send_money = $this->charge->where($where.' and member_id='.$id.' and status=1')->getField('send_money',true);
        $info['total_charge_money'] = 0;
        if($total_charge_money){
            foreach ($total_charge_money as $key => $value) {
                $info['total_charge_money'] += $value;
            }
            foreach ($total_send_money as $k => $v) {
                $info['total_charge_money'] += $v;
            }
        }
        $res[] = $info;
        unset($data);
        $data=array("code"=>0,"msg"=>"","data"=>$res);

        exit(json_encode($data));

    }
    //充值
    public function charge(){
        if(IS_POST){
            $is_custom = I('post.is_custom',0,'intval');
            $member_id = I('post.member_id');
            $package_id = I('post.package_id','0','intval');
            $count = $this->charge->where('member_id='.$member_id.' and status=1')->count();
            if($count == 0){
                $this->ShopMember->where('id='.$member_id)->save(array('shopuser_id' => $this->user_id));
            }
            if($this->charge->create()!==false){
                if($is_custom){
                    $package_id = 0;
                    $charge = $this->charge->add();
                    $this->charge->where('id='.$charge)->save(array('shopuser_id' => $this->user_id,'package_id'=>$package_id));
                    $package_amount = $_POST['recharge_money'];
                    $give_amount = $_POST['send_money'];
                    $balance = (float)((float)$package_amount+(float)$give_amount);
                    $cardsell_reward = I('post.sellcard_reward',0,'float');
                }else{
                    $package_amount = $this->ShopPackage->where('id='.$package_id)->getField('package_amount');
                    $give_amount = $this->ShopPackage->where('id='.$package_id)->getField('give_amount');
                    $balance = (float)((float)$package_amount+(float)$give_amount);
                    $cardsell_reward = $this->ShopPackage->where('id='.$package_id)->getField('rec_reward');
                    unset($res);
                    $res = array(
                        'member_id' => $member_id,
                        'package_id' => $package_id,
                        'chain_id' => $this->chain_id,
                        'shop_id' => $this->shop_id,
                        'recharge_money' => $package_amount,
                        'send_money' => $give_amount,
                        'is_custom' => 0,
                        'createtime' => time(),
                        'shopuser_id' => $this->user_id,
                    );
                    $charge = $this->charge->add($res);
                }
                    
                if($charge!==false){
                    $balance_old = $this->ShopMember->where('id='.$member_id)->getField('balance');
                    $balance_new = (float)((float)$balance_old + (float)$balance);
                    unset($res);
                    $res = array('balance' => $balance_new);

                    $is_discount = $this->ShopPackage->where('id='.$package_id)->getField('is_discount');
                    $discount = $this->ShopPackage->where('id='.$package_id)->getField('discount');
                    if(!empty($is_discount)){
                        $res['discount'] = $discount;
                    }

                    $this->ShopMember->where('id='.$member_id)->save($res);
                    $rest = array(
                        'member_id' => $member_id,
                        'chain_id' => $this->chain_id,
                        'shop_id' => $this->shop_id,
                        'package_id' => $package_id,
                        'identity' => 1,
                        'transaction_type' => 1,
                        'transaction_money' => $package_amount,
                        'card_id' => $charge,
                        'sellcard_masseur' => $_POST['sellcard_masseur'],
                        'sellcard_reward' => $cardsell_reward,
                        'now_money' => $balance_new,
                        'pay_way' => $_POST['pay_way'],
                        'remark' => $_POST['remark'],
                        'is_custom' => $is_custom,
                        'createtime' => time(),
                        'shopuser_id' => session('user_id'),

                    );
                    $this->financial->add($rest);
                    if(!empty($_POST['sellcard_masseur'])){
                        $bala = $this->ShopMasseur->where('id='.$_POST['sellcard_masseur'])->getField('balance');
                        unset($data);
                        $data = array('id'=>$_POST['sellcard_masseur'],'balance'=>$bala+$cardsell_reward);
                        $this->ShopMasseur->save($data);
                    }

                    $this->success('办理成功！');
                }else{
                    $this->error('办理失败！');
                }
            }
        }else{
            $id = I('get.id',0,'intval');
            $info= $this->ShopMember->where('id='.$id)->find();
            $where=" chain_id=".$this->chain_id." and shop_id=".$this->shop_id;
            $card_list = $this->ShopPackage->where($where)->select();
            $pay_list = $this->setting->where('parentid=1 and status=1 and id!=2')->select();
            if(!empty($card_list)){
                foreach ($card_list as $key => $value) {
                    if($value['is_discount'] == 1){
                        $card_list[$key]['package_name'] = $value['package_name'].'('.$value['discount'].'折卡)';
                    }
                }
            }
            
            $masseur_list = $this->ShopMasseur->where($where.' and state=1')->select();
            $this->assign('card_list',$card_list);
            $this->assign('pay_list',$pay_list);
            $this->assign('masseur_list',$masseur_list);
            $this->assign('info',$info);
            $this->display('charge');
        }
        

    }
    //办理次卡
    public function transact_num(){
        if(IS_POST){
            $is_custom = I('post.is_custom',0,'intval');
            $sellcard_masseur = I('post.sellcard_masseur',0,'intval');
            $member_id = I('post.member_id');
            $cika_id = I('post.cika_id','0','intval');
            if($this->numcard->create()!==false){
                if($is_custom){
                    $item_id = I('post.project_id');
                    if(!is_numeric($item_id)){
                        $this->error('请选择项目！');
                    }
                    $cika_id = 0;
                    $count = $this->numcard->where('member_id='.$member_id.' and project_id='.$item_id)->count();
                    if($count > 0){
                        $project_name = $this->item->where('id='.$item_id)->getField('item_name');
                        $this->error('此项目'.$project_name.'已办理，请直接充值');
                       
                    }
                    $numcard = $this->numcard->add(); 
                    $average = round($_POST['numcard_money']/$_POST['card_num'],2);
                    unset($res);
                    $res = array('id'=>$numcard,'average'=>$average,'shopuser_id'=>session('user_id'),'cika_id'=>$cika_id);
                    $this->numcard->save($res);
                    $package_amount = $_POST['numcard_money'];
    
                    $item_num = I('post.card_num');
                    $cardsell_reward = I('post.sellcard_reward',0,'float');
                }else{
                    if(empty($cika_id)){
                         $this->error('请选择次卡套餐！');
                    }
                    $package_amount = $this->ShopCika->where('id='.$cika_id)->getField('package_amount');
                    $item_id = $this->ShopCika->where('id='.$cika_id)->getField('item_id');
                    $count = $this->numcard->where('member_id='.$member_id.' and project_id='.$item_id)->count();
                    if($count > 0){
                        $project_name = $this->item->where('id='.$item_id)->getField('item_name');

                        $this->error('此项目'.$project_name.'已办理，请直接充值');
                    }
                    $item_num = $this->ShopCika->where('id='.$cika_id)->getField('item_num');
                    $package_amount = $this->ShopCika->where('id='.$cika_id)->getField('package_amount');
                    $average = round($package_amount/$item_num,2);
                 
                    $cardsell_reward = $this->ShopCika->where('id='.$cika_id)->getField('rec_reward');
                    unset($res);
                    $res = array(
                        'member_id' => $member_id,
                        'cika_id' => $cika_id,
                        'project_id' => $item_id,
                        'card_num' => $item_num,
                        'chain_id' => $this->chain_id,
                        'shop_id' => $this->shop_id,
                        'numcard_money' => $package_amount,
                        'is_custom' => 0,
                        'average' => $average,
                        'shopuser_id' => session('user_id'),
                        'createtime' => time()
                    );
                    $numcard = $this->numcard->add($res);
                }
                
                if($numcard!==false){
                    unset($rest);
                    $rest = array(
                        'member_id' => $member_id,
                        'chain_id' => $this->chain_id,
                        'shop_id' => $this->shop_id,
                        'project_id' => $item_id,
                        'package_id' => $cika_id,
                        'identity' => 1,
                        'card_id' => $numcard,
                        'transaction_type' => 2,
                        'transaction_money' => $package_amount,
                        'transaction_num' => $item_num,
                        'sellcard_masseur' => $sellcard_masseur,
                        'sellcard_reward' => $cardsell_reward,
                        'now_money' => $package_amount,
                        'now_num' => $item_num,
                        'pay_way' => $_POST['pay_way'],
                        'remark' => $_POST['numcard_remark'],
                        'shopuser_id' => session('user_id'),
                        'is_custom' => $is_custom,
                        'createtime' => time()
                    );
                    $this->financial->add($rest);
                    $this->numcardfinancial->add($rest);
                    if(!empty($sellcard_masseur)){
                        $bala = $this->ShopMasseur->where('id='.$sellcard_masseur)->getField('balance');
                        unset($data);
                        $data = array('id'=>$sellcard_masseur,'balance'=>$bala+$cardsell_reward);
                        $this->ShopMasseur->save($data);
                    }

                    $this->success('办理成功！');
                }else{
                    $this->error('办理失败！');
                }
            }
        }else{
            $id = I('get.id',0,'intval');
            $info= $this->ShopMember->where('id='.$id)->find();
            $where=" chain_id=".$this->chain_id." and shop_id=".$this->shop_id;
            $num_list = $this->ShopCika->where($where.' and state=1')->select();
            $pay_list = $this->setting->where('parentid=1 and status=1 and id!=2')->select();
            $project = $this->item->where($where.' and state=1')->select();
            $masseur_list = $this->ShopMasseur->where($where.' and state=1')->select();

            $this->assign('masseur_list',$masseur_list);
            $this->assign('pay_list',$pay_list);
            $this->assign('num_list',$num_list);
            $this->assign('info',$info);
            $this->assign('project',$project);
            $this->display('transact_num');
        }
        

    }
    //办理期限卡
    public function transact_deadline(){
        if(IS_POST){
            $is_custom = I('post.is_custom',0,'intval');
            $sellcard_masseur = I('post.sellcard_masseur',0,'intval');
            $member_id = I('post.member_id');
            $qixian_id = I('post.qixian_id','0','intval');
            if($this->deadlinecard->create()!==false){
                if($is_custom){
                    $qixian_id = 0;
                    $item_id = I('post.project_id');
                    if(!is_numeric($item_id)){
                        $this->error('请选择项目！');
                    }
                   $count = $this->deadlinecard->where('member_id='.$member_id.' and project_id='.$item_id.' and end_time >'.(strtotime(date("Y-m-d"))+3600*24))->count();
                    if($count > 0){
                         $project_name = $this->item->where('id='.$item_id)->getField('item_name');
                        $this->error('此项目'.$project_name.'已办理，请直接延长有效期');
                       
                    }
                    $package_amount = $_POST['card_money'];
                    if(empty($package_amount)){
                        $this->error('请输入办卡价格！');
                    }
                    $balance = (float)$package_amount;
                    $effective_long = I('post.effective_long');
                    if(empty($effective_long)){
                        $this->error('请输入有效时长！');
                    }
                    $day_ceiling = I('post.day_ceiling');
                    if(empty($day_ceiling)){
                        $this->error('请输入单日使用上限！');
                    }
                    $sellcard_reward = I('post.sellcard_reward',0,'float');
                    $deadlinecard = $this->deadlinecard->add();
                    $end_time = time()+86400*$effective_long;
                    unset($res);
                    $res = array('end_time' => $end_time);
                    $this->deadlinecard->where('id='.$deadlinecard)->save($res);
                   
                }else{
                    if(empty($qixian_id)){
                        $this->error('请选择套餐！');
                    }
                    $package_amount = $this->ShopQixian->where('id='.$qixian_id)->getField('package_amount');
                    $item_id = $this->ShopQixian->where('id='.$qixian_id)->getField('item_id');
                   $count = $this->deadlinecard->where('member_id='.$member_id.' and project_id='.$item_id.' and end_time >'.(strtotime(date("Y-m-d"))+3600*24))->count();
                    if($count > 0){
                         $project_name = $this->item->where('id='.$item_id)->getField('item_name');
                        $this->error('此项目'.$project_name.'已办理，请直接延长有效期');
                       
                    }
                    $package_amount = $this->ShopQixian->where('id='.$qixian_id)->getField('package_amount');
                    $balance = (float)$package_amount;
                    $sellcard_reward = $this->ShopQixian->where('id='.$qixian_id)->getField('rec_reward');
                    $effective_long = $this->ShopQixian->where('id='.$qixian_id)->getField('expiry_during');
                    $day_ceiling = $this->ShopQixian->where('id='.$qixian_id)->getField('limit_times');
                    unset($res);
                    $end_time = time()+86400*$effective_long;
                    $res = array(
                        'member_id' => $member_id,
                        'qixian_id' => $qixian_id,
                        'project_id' => $item_id,
                        'chain_id' => $this->chain_id,
                        'shop_id' => $this->shop_id,
                        'card_money' => $package_amount,
                        'effective_long' => $effective_long,
                        'day_ceiling' => $day_ceiling,  
                        'shopuser_id' => session('user_id'),                     
                        'is_custom' => 0,
                        'createtime' => time(),
                        'start_time' => time(),
                        'end_time' => $end_time,
                    );
                    $deadlinecard = $this->deadlinecard->add($res);
                }
                if($deadlinecard!==false){
                    unset($rest);
                    $rest = array(
                        'member_id' => $member_id,
                        'chain_id' => $this->chain_id,
                        'shop_id' => $this->shop_id,
                        'project_id' => $item_id,
                        'package_id' => $qixian_id,
                        'identity' => 1,
                        'card_id' => $deadlinecard,
                        'transaction_type' => 3,
                        'transaction_money' => $package_amount,
                        'transaction_days' => $effective_long,
                        'sellcard_masseur' => $sellcard_masseur,
                        'sellcard_reward' => $sellcard_reward,
                        'shopuser_id' => session('user_id'),
                        'now_days' => $effective_long,
                        'pay_way' => $_POST['pay_way'],
                        'remark' => $_POST['deadline_remark'],
                        'is_custom' => $is_custom,
                        'createtime' => time()
                    );
                    $this->financial->add($rest);
                    $this->deadlinefinancial->add($rest);
                    if(!empty($sellcard_masseur)){
                        $bala = $this->ShopMasseur->where('id='.$sellcard_masseur)->getField('balance');
                        unset($data);
                        $data = array('id'=>$sellcard_masseur,'balance'=>$bala+$sellcard_reward);
                        $this->ShopMasseur->save($data);
                    }

                    $this->success('办理成功！');
                }else{
                    $this->error('办理失败！');
                }
            }

        }else{
            $id = I('get.id',0,'intval');
            $info= $this->ShopMember->where('id='.$id)->find();
            $where=" chain_id=".$this->chain_id." and shop_id=".$this->shop_id;
            $deadline_list = $this->ShopQixian->where($where.' and state=1')->select();
            
            $pay_list = $this->setting->where('parentid=1 and status=1 and id!=2')->select();
            $project = $this->item->where($where.' and state=1')->select();
            $masseur_list = $this->ShopMasseur->where($where.' and state=1')->select();
        
            

            $this->assign('card_list',$project);
            $this->assign('pay_list',$pay_list);
            $this->assign('masseur_list',$masseur_list);
            $this->assign('deadline_list',$deadline_list);
            $this->assign('info',$info);
            $this->display('transact_deadline');
        }
        

    }

    
    //办理疗程卡
    public function transact_course(){
        if(IS_POST){
            $is_custom = I('post.is_custom',0,'intval');
            $sellcard_masseur = I('post.sellcard_masseur',0,'intval');
            $member_id = I('post.member_id');
            $liaocheng_id = I('post.liaocheng_id','0','intval');
            $course_remark = I('post.course_remark');
            $pay_way = I('post.pay_way');
            if($this->coursecard->create()!==false){
                if($is_custom){
                    $liaocheng_id = 0;
                    $card_money = I('post.card_money');
                    $description = I('post.description');
                    $sellcard_reward = I('post.sellcard_reward');
                    $project_id = I('post.project_id');
                    if(empty($project_id)){
                        $this->error('请选择项目！');
                    }
                    $card_num = I('post.card_num');
                    $num = 0;
                    foreach ($card_num as $key => $value) {
                        $num += $value;
                    }
                    $average = round($card_money/$num,2);

                    unset($res);
                    $res = array(
                        'liaocheng_id'=>$liaocheng_id,
                        'member_id' => $member_id,
                        'card_money' => $card_money,
                        'description' => $description,
                        'is_custom' => $is_custom,
                        'chain_id' => $this->chain_id,
                        'shop_id' => $this->shop_id,
                        'average' => $average,
                        'createtime' => time(),
                        'shopuser_id' => session('user_id'),
                    );
                    $coursecard = $this->coursecard->add($res);
                   

                    foreach ($project_id as $key => $value) {
                        unset($res);
                        $res = array(
                            'chain_id' => $this->chain_id,
                            'shop_id' => $this->shop_id,
                            'project_id' => $value,
                            'card_num' => $card_num[$key],
                            'card_id' => $coursecard,
                            'member_id' => $member_id,
                        );
                       $courseproject = $this->courseproject->add($res);
                       if(($coursecard!==false) && ($courseproject !== false)){
                            unset($rest);
                            $rest = array(
                                'member_id' => $member_id,
                                'chain_id' => $this->chain_id,
                                'shop_id' => $this->shop_id,
                                'project_id' => $value,
                                'courseproject_id' => $courseproject,
                                'transaction_num' => $card_num[$key],
                                'identity' => 1,
                                'card_id' => $coursecard,
                                'transaction_type' => 4,
                                'transaction_money' => $card_money,
                                'sellcard_masseur' => $sellcard_masseur,
                                'sellcard_reward' => $sellcard_reward,
                                'shopuser_id' => session('user_id'),
                                'now_money' => $card_money,
                                'now_num' => $card_num[$key],
                                'pay_way' => $pay_way,
                                'remark' => $course_remark,
                                'is_custom' => $is_custom,
                                'createtime' => time(),
                            );
                            $this->financial->add($rest);
                            $this->coursefinancial->add($rest);
                        }
                    }

                }else{
                    if(empty($liaocheng_id)){
                        $this->error('请选择套餐！');
                    }
                    $card_money = $this->ShopLiaocheng->where('id='.$liaocheng_id)->getField('package_amount');
                    $project_id = $this->ShopLiaocheng->where('id='.$liaocheng_id)->getField('item');
                    $sellcard_reward = $this->ShopLiaocheng->where('id='.$liaocheng_id)->getField('rec_reward');
                    $description = $this->ShopLiaocheng->where('id='.$liaocheng_id)->getField('remark');
                    $projects=unserialize($project_id);
                    $num = 0;
                    foreach ($projects as $key => $value) {
                        $num += $value['number'];
                    }
                    $average = round($card_money/$num,2);
                    unset($res);
                    $res = array(
                        'member_id' => $member_id,
                        'liaocheng_id' => $liaocheng_id,
                        'chain_id' => $this->chain_id,
                        'shop_id' => $this->shop_id,
                        'card_money' => $card_money,
                        'description' => $description,
                        'is_custom' => 0,
                        'average' => $average,
                        'createtime' => time(),
                        'shopuser_id' => session('user_id'),
                    );
                    $coursecard = $this->coursecard->add($res);
                    //项目
                    foreach ($projects as $key => $value) {
                       unset($res);
                       $res = array(
                            'chain_id' => $this->chain_id,
                            'shop_id' => $this->shop_id,
                            'project_id'=>$value['item_detail'],
                            'card_num'=>$value['number'],
                            'card_id'=>$coursecard,
                            'member_id'=>$member_id,
                       );
                      $courseproject = $this->courseproject->add($res);
                      if(($coursecard!==false) && ($courseproject !== false)){
                            unset($rest);
                            $rest = array(
                                'member_id' => $member_id,
                                'chain_id' => $this->chain_id,
                                'shop_id' => $this->shop_id,
                                'project_id' => $value['item_detail'],
                                'transaction_num' => $value['number'],
                                'identity' => 1,
                                'package_id' => $liaocheng_id,
                                'card_id' => $coursecard,
                                'courseproject_id' => $courseproject,
                                'transaction_type' => 4,
                                'transaction_money' => $card_money,
                                'sellcard_masseur' => $sellcard_masseur,
                                'sellcard_reward' => $sellcard_reward,
                                'shopuser_id' => session('user_id'),
                                'now_money' => $card_money,
                                'now_num' => $value['number'],
                                'pay_way' => $pay_way,
                                'remark' => $course_remark,
                                'is_custom' => $is_custom,
                                'createtime' => time()
                            );
                            $this->financial->add($rest);
                            $this->coursefinancial->add($rest);
                        }
                    }
                    
                }
                
                if(($coursecard!==false) && ($courseproject !== false)){
                    if(!empty($sellcard_masseur)){
                        $bala = $this->ShopMasseur->where('id='.$sellcard_masseur)->getField('balance');
                        unset($data);
                        $data = array('id'=>$sellcard_masseur,'balance'=>$bala+$sellcard_reward);
                        $this->ShopMasseur->save($data);
                    }
                    $this->success('办理成功！');
                }else{
                    $this->error('办理失败！');
                }
                    
            }


        }else{
            $id = I('get.id',0,'intval');
            $info= $this->ShopMember->where('id='.$id)->find();
            $where="shop_id=".$this->shop_id;
            $course_list = $this->ShopLiaocheng->where($where)->select();
            
            $pay_list = $this->setting->where('parentid=1 and status=1 and id!=2')->select();
            $masseur_list = $this->ShopMasseur->where($where.' and state=1')->select();
            $project = $this->item->where($where.' and state=1')->select();

            $this->assign('card_list',$project);
            $this->assign('masseur_list',$masseur_list);
            $this->assign('pay_list',$pay_list);
            $this->assign('course_list',$course_list);
            $this->assign('info',$info);
            $this->display('transact_course');
        }
        

    }
    //添加标签
    public function transact_archives(){
        if(IS_POST){
            $member_id = I('post.member_id');
            $label = I('post.label');
            $sort = I('post.sort');
            unset($res);
            $res = array(
                'member_id'=>$member_id,
                'label'=>$label,
                'sort'=>$sort,
                'chain_id'=>$this->chain_id,
                'shop_id'=>$this->shop_id,
                'create_time'=>time()
            );
            $result = $this->ShopMemberlabel->add($res);
            if($result !== false){
                $this->success('添加成功！');
            }else{
                $this->error('添加失败！');
            }
        }else{
            $id = I('get.id',0,'intval');
            $this->assign('member_id',$id);
            $this->display('transact_archives');
        }
    }
    //次卡接口
    public function num(){
        $id = I('get.id',0,'intval');
        $where="chain_id=".$this->chain_id." and shop_id=".$this->shop_id;
        $num_record = $this->numcard->where($where.' and member_id='.$id)->select();
        if($num_record){
            foreach ($num_record as $key => $value) {
                $info[$key]['product_name'] = $this->item->where($where.' and id='.$value['project_id'])->getField('item_name');
                $info[$key]['card_num'] = $value['card_num'];
                $info[$key]['id'] = $value['id'];
            }
        }
        else{
            // $info[0]['product_name'] = '暂无数据';
            // $info[0]['card_num'] = '暂无数据';
            $info[]=array();
        }
        unset($data);
        $data=array("code"=>0,"msg"=>"","data"=>$info);
        exit(json_encode($data));


    }

    //期限卡接口
    public function deadline(){
        $id = I('get.id',0,'intval');
        $where="chain_id=".$this->chain_id." and shop_id=".$this->shop_id;
        $deadline_record = $this->deadlinecard->where($where.' and member_id='.$id)->select();

         if($deadline_record){
            foreach ($deadline_record as $key => $value) {
                $info[$key]['id'] = $value['id'];
                $info[$key]['project_name'] = $this->item->where($where.' and id='.$value['project_id'])->getField('item_name');
                $info[$key]['end_time'] = date('Y-m-d',$value['end_time']);
                $info[$key]['effective_long'] = $value['effective_long'];
                // $info[$key]['day_ceiling'] = $value['day_ceiling'];

                $consumption_ceilings = $this->financial->where("transaction_type=3 and card_id=".$value['id']." and createtime > ".strtotime(date("Y-m-d"))." and createtime < ".(strtotime(date("Y-m-d"))+3600*24))->select();
                $used = 0;
                if(!empty($consumption_ceilings)){
                    foreach ($consumption_ceilings as $k => $v) {
                        if($v['transaction_num'] < 0){
                            $used += ((-1)*$v['transaction_num']);
                        }
                    }
                   
                    $info[$key]['day_ceiling'] = $value['day_ceiling'] - $used;
                }else{
                    $info[$key]['day_ceiling'] = $value['day_ceiling'];
                }
            }
        }else{
            $info[] = array();
        }
        $data=array("code"=>0,"msg"=>"","data"=>$info);
        exit(json_encode($data));

    }
    
    //疗程卡接口
    public function course(){
        $id = I('get.id',0,'intval');
        $where="chain_id=".$this->chain_id." and shop_id=".$this->shop_id;
        $course_record = $this->coursecard->where($where.' and member_id='.$id)->select();
         if($course_record){
            foreach ($course_record as $key => $value) {
                $project_id = $this->courseproject->where('card_id='.$value['id'])->getField('project_id',true);
                $card_num = $this->courseproject->where('card_id='.$value['id'])->getField('card_num',true);
               
                $num = 0;
                foreach ($card_num as $k => $v) {
                    $num += $v;
                }
                $info[$key]['id'] = $value['id'];
                $info[$key]['product_name'] = $this->item->where($where.' and id='.$project_id[0])->getField('item_name');
                $info[$key]['product_name'].='等'.count($card_num).'个项目';
                
                $info[$key]['remaining_num'] = $num;
                $info[$key]['description'] = $value['description'];
                
               

            }
        }else{
            $info[] = array();
        }
        $data=array("code"=>0,"msg"=>"","data"=>$info);
        exit(json_encode($data));
        

    }

    //健康档案接口
    public function archives(){
        $id = I('get.id',0,'intval');
        $where="shop_id=".$this->shop_id;
        $labels = $this->ShopMemberlabel->field('id,label,sort,create_time')->where($where.' and member_id='.$id)->order('create_time desc,sort asc')->select();
        if(empty($labels)){
            $labels[] = array();
        }else{
            foreach ($labels as $key => $value) {
                $labels[$key]['create_time'] = date("Y-m-d H:i:s",$value['create_time']);
            }
        }
        $data=array("code"=>0,"msg"=>"","data"=>$labels);
        exit(json_encode($data));
        

    }
    //删除会员标签
    public function delete(){
        $id = I('post.id');
        $result = $this->ShopMemberlabel->where('id='.$id)->delete();
        if($result !== false){
            echo 'success';
        }else{
            echo 'error';
        }
    }

//次卡充值页面
public function recharge()
{

    if(IS_POST){
        $is_custom = $_POST['is_custom']?$_POST['is_custom']:0;
        if((!empty($_POST['cika_id'])) && (empty($is_custom)))
        {
            $item=M('ShopCika')->where('id=' . $_POST['cika_id'])->find();
            $money=$item['package_amount'];
            $num=$item['item_num'];
            $project_id=$item['item_id'];
            $package_id = $_POST['cika_id'];
        }
        else
        {
            $money=$_POST['numcard_money'];
            $num=$_POST['card_num'];
            $project_id=$_POST['project_id'];
            $package_id=0;

        }
        $card_num=$this->numcard->where('id=' . $_POST['numcard'])->getField('card_num');
        $numcard_money=$this->numcard->where('id=' . $_POST['numcard'])->getField('numcard_money');

        $data = array(
            'member_id' => $_POST['member_id'],
            'chain_id' => $_POST['chain_id'],
            'shop_id' => $_POST['shop_id'],
            'project_id' => $project_id,
            'package_id' =>$package_id,
            'identity' => 1,
            'card_id' => $_POST['numcard'],
            'transaction_type' => 2,
            'transaction_money' => $money,
            'transaction_num' => $num,
            'now_money' => $numcard_money+$_POST['numcard_money'],
            'now_num' => $card_num+$num,
            'sellcard_masseur' => $_POST['sellcard_masseur'],
            'sellcard_reward' => $_POST['sellcard_reward'],
            'pay_way' => $_POST['pay_way'],
            'remark' => $_POST['numcard_remark'],
            'is_custom' => !empty($_POST['is_custom']) ? $_POST['is_custom']:0,
            'createtime' => time(),
            'shopuser_id' => session('user_id'),
        );
        if($this->financial->add($data))
        {
            $this->numcardfinancial->add($data);
            $data_arr = array(
                'project_id' =>$project_id,
                'card_num' =>$card_num + $num,
                'numcard_money' =>$numcard_money + $money,
                'cika_id' =>$package_id,
            );
            $result = $this->numcard->where('id=' . $_POST['numcard'])->save($data_arr);
            
            if($result!==false)
            {
                $this->success('办理成功！');
            }
            else
            {
                $this->error('办理失败！');
            }
        }
    }else {
        $id = I('get.id', 0, 'intval');
        $itemid = I('get.itemid', 0, 'intval');
        $info = M("ShopNumcard")->where('id=' . $itemid)->find();
        $cika_id = $info['cika_id'];
        if(empty($cika_id)){
            $map = "item_id='".$info['project_id']."' and chain_id=" . $this->chain_id . " and shop_id=" . $this->shop_id;
        }else{
            $map = "id='".$cika_id."'  and chain_id=" . $this->chain_id . " and shop_id=" . $this->shop_id;
        }
        $where .= "chain_id=" . $this->chain_id . " and shop_id=" . $this->shop_id;
        $num_list = $this->ShopCika->where($map . ' and state=1')->select();
        $pay_list = $this->setting->where('parentid=1 and status=1 and id!=2')->select();
        $project = $this->item->where($where . ' and state=1')->select();
        $masseur_list = $this->ShopMasseur->where($where . ' and state=1')->select();
        $this->assign('masseur_list', $masseur_list);
        $this->assign('num_list', $num_list);
        $this->assign('pay_list', $pay_list);
        $this->assign('info', $info);
        $this->assign('project', $project);
        $this->display('recharge');
    }
}

//次卡次数扣除
public function consume()
{
    if(IS_POST){
        $id = I('post.numcard', 0, 'intval');
        $deduct_num = I('post.deduct_num', 1, 'intval');
        $numcard_remark = I('post.numcard_remark');
        $card_num = I('post.card_num');
        $info = M("ShopNumcard")->where('id=' . $id)->find();
        $data=array('card_num'=>$info['card_num']-$deduct_num,'numcard_money'=>$info['numcard_money']-($info['average']*$deduct_num));
        if($card_num < $deduct_num){
            $this->error('扣除次数不能大于实际剩余次数！');
        }
        $result = $this->numcard->where('id=' . $id)->save($data);
        if($result!==false)
        {
            $data_arr = array(
                'member_id' => $info['member_id'],
                'chain_id' => $info['chain_id'],
                'shop_id' => $info['shop_id'],
                'project_id' => $info['project_id'],
                'package_id' =>$info['cika_id'],
                'identity' => 1,
                'card_id' => $id,
                'transaction_type' => 2,
                'transaction_money' =>(-1)*$info['average']*$deduct_num,
                'transaction_num' =>(-1)*$deduct_num,
                'now_money' => $info['numcard_money']-($info['average']*$deduct_num),
                'now_num' => $info['card_num']-$deduct_num,
                'shopuser_id'=> session('user_id'),
                'remark' => $numcard_remark,
                'createtime' => time(),
                'type'=>2,
            );
            if($this->financial->add($data_arr))
            {
                $this->numcardfinancial->add($data_arr);
                $this->success('扣除成功！');
            }
            else
            {
                $this->error('扣除失败！');
            }
        }
        else
        {
            $this->error('扣除失败！');
        }
    }else{
        $id = I('get.id', 0, 'intval');
        $itemid = I('get.itemid', 0, 'intval');
        $info = M("ShopNumcard")->where('id=' . $itemid)->find();
        $where .= "chain_id=" . $this->chain_id . " and shop_id=" . $this->shop_id;
        $project = $this->item->where($where . ' and state=1')->select();
        $this->assign('info', $info);
        $this->assign('project', $project);
        $this->display('consume');
    }

}

//期限卡次数扣除
public function deadline_deduct()
{
    if(IS_POST){
        $id = I('post.deadlinecard_id', 0, 'intval');
        $deduct_num = I('post.deduct_num', 1, 'intval');
        $deadline_remark = I('post.deadline_remark');
        $day_ceiling = I('post.day_ceiling');
        $effective_long = I('post.effective_long');
        if($effective_long <= 0){
            $this->error('此期限卡已到期！');
        }
       
        if(!empty($consumption_ceilings)){
            foreach ($consumption_ceilings as $k => $v) {
    
                $used += ((-1)*$v['transaction_num']);
              
            }
        }
        if($day_ceiling == 0){
            $this->error('今天已经到达单日使用上限！');
        }else if($day_ceiling < $deduct_num){
            $this->error('扣除次数不能大于单日剩余次数上限！');
        }
        $info = $this->deadlinecard->where('id='.$id)->find();
        $info['effective_long'] = floor(($info['end_time']-strtotime(date("Y-m-d")))/(3600*24));
        if($info['effective_long'] <= 0){
            $info['effective_long'] = 0;
        }
        $is_custom = empty($info['qixian_id'])?1:0;
        $data_arr = array(
            'member_id' => $info['member_id'],
            'chain_id' => $info['chain_id'],
            'shop_id' => $info['shop_id'],
            'project_id' => $info['project_id'],
            'package_id' =>$info['qixian_id'],
            'identity' => 1,
            'card_id' => $id,
            'transaction_type' => 3,
            'transaction_num' =>(-1)*$deduct_num,
            'is_custom' => $is_custom,
            'now_days' => $info['effective_long'],
            'shopuser_id'=> session('user_id'),
            'remark' => $deadline_remark,
            'createtime' => time(),
            'type'=>2,
        );
        if($this->financial->add($data_arr))
        {
            $this->deadlinefinancial->add($data_arr);
            $this->success('扣除成功！');
        }
        else
        {
            $this->error('扣除失败！');
        }
       
        
    }else{
        $id = I('get.id', 0, 'intval');
        $itemid = I('get.itemid', 0, 'intval');
        $info = $this->deadlinecard->where('id=' . $itemid)->find();
        $where .= "chain_id=" . $this->chain_id . " and shop_id=" . $this->shop_id;
        $project = $this->item->where($where . ' and state=1')->select();
        // $consumption_ceilings = $this->financial->where("transaction_type=3 and card_id=".$itemid." and createtime > ".strtotime(date("Y-m-d"))." and createtime < ".(strtotime(date("Y-m-d"))+3600*24))->select();
        
        $consumption_ceilings = $this->deadlinefinancial->where("transaction_type=3 and card_id=".$itemid." and createtime > ".strtotime(date("Y-m-d"))." and createtime < ".(strtotime(date("Y-m-d"))+3600*24))->select();
        
        $used = 0;
        if(!empty($consumption_ceilings)){
            foreach ($consumption_ceilings as $k => $v) {
                if($v['transaction_num'] < 0){
                    $used += ((-1)*$v['transaction_num']);
                }
            }
           
            $info['day_ceiling'] = $info['day_ceiling'] - $used;
        }
        $info['effective_long'] = floor(($info['end_time']-strtotime(date("Y-m-d")))/(3600*24));
        if($info['effective_long'] <= 0){
            $info['effective_long'] = 0;
        }
        $this->assign('info', $info);
        $this->assign('project', $project);
        $this->display('deadline_deduct');
    }

}
//期限卡延长有效期
public function deadline_increase()
{
    if(IS_POST){
        $increase_num = I('post.increase_num');
        $id = I('post.deadlinecard_id', 0, 'intval');
        $info = $this->deadlinecard->where('id='.$id)->find();
        $is_custom = empty($info['qixian_id'])?1:0;
        unset($data_arr);
        $data_arr = array(
            'member_id' => $info['member_id'],
            'chain_id' => $info['chain_id'],
            'shop_id' => $info['shop_id'],
            'project_id' => $info['project_id'],
            'package_id' =>$info['qixian_id'],
            'identity' => 1,
            'card_id' => $id,
            'transaction_type' => 3,
            'transaction_days' => $increase_num,
            'is_custom' => $is_custom,
            'shopuser_id'=> session('user_id'),
            'remark' => '',
            'createtime' => time(),

        );
        $result = $this->financial->add($data_arr);
        $this->deadlinefinancial->add($data_arr);
        if($result !== false){
            $res = array('end_time'=>$info['end_time']+$increase_num*86400,'effective_long'=>$info['effective_long']+$increase_num);
            $rest = $this->deadlinecard->where('id='.$id)->save($res);
            if($rest !== false){
                $this->success('延长成功!');
            }else{
                $this->error('延长失败！');
            }
        }
       
    }else{
        $id = I('get.id', 0, 'intval');
        $itemid = I('get.itemid', 0, 'intval');
        $info = $this->deadlinecard->where('id=' . $itemid)->find();
        $info['end_time'] = date("Y-m-d",$info['end_time']);
        $this->assign('info', $info);
        $this->display('deadline_increase');
    }
}
//疗程卡次数扣除
public function course_deduct(){
    if(IS_POST){
        $id = I('post.course_id', 0, 'intval');
        $courseproject_id = I('post.project_id');
        $deduct_num = I('post.deduct_num');
        $course_remark = I('post.course_remark','');
        if(empty($courseproject_id)){
            $this->error('请选择项目！');
        }
        $courseproject = $this->courseproject->where('id='.$courseproject_id)->find();
        if($courseproject['card_num'] < $deduct_num){
            $this->error('扣除次数不能大于实际剩余次数！');
        }
        $info = $this->coursecard->where('id=' . $id)->find();
        $is_custom = empty($info['liaocheng_id'])?1:0;
        unset($data_arr);
        $data_arr = array(
            'member_id' => $info['member_id'],
            'chain_id' => $info['chain_id'],
            'shop_id' => $info['shop_id'],
            'project_id' => $courseproject['project_id'],
            'package_id' =>$info['liaocheng_id'],
            'identity' => 1,
            'card_id' => $id,
            'courseproject_id'=>$courseproject['id'],
            'transaction_type' => 4,
            'transaction_money' => (-1)*$info['average']*$deduct_num,
            'transaction_num' => (-1)*$deduct_num,
            'now_money' => $info['card_money']-$info['average']*$deduct_num,
            'now_num' => $courseproject['card_num']-$deduct_num,
            'is_custom' => $is_custom,
            'shopuser_id'=> session('user_id'),
            'remark' => $course_remark,
            'createtime' => time(),
            'type'=>2,
        );
        $result = $this->financial->add($data_arr);
        $this->coursefinancial->add($data_arr);
        if($result !== false){
            unset($res);
            $res = array(
                'id'=>$id,
                'card_money'=>$info['card_money']-$info['average']*$deduct_num,
            );
            $ress = $this->coursecard->save($res);
            unset($rest);
            $rest = array(
                'id' => $courseproject_id,
                'card_num' => $courseproject['card_num']-$deduct_num,
            );
            $restt = $this->courseproject->save($rest);
            if(($ress !== false) && ($restt !== false)){
                $this->success('扣除成功！');
            }else{
                $this->error('扣除失败！');
            }
        }
    }else{
        $id = I('get.id', 0, 'intval');
        $itemid = I('get.itemid', 0, 'intval');
        $info = $this->coursecard->where('id=' . $itemid)->find();
        $projects = $this->courseproject->where('card_id='.$itemid)->select();
        if(!empty($projects)){
            foreach ($projects as $key => $value) {
                $projects[$key]['item_name'] = $this->item->where('id='.$value['project_id'])->getfield('item_name');
               
            }
        }
  
        $this->assign('info',$info);
        $this->assign('projects',$projects);
        $this->display();
    }
}



}
?>