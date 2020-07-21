<?php
// +----------------------------------------------------------------------
// | 会员列表页
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Author: Th2 <2791919673@qq.com>
// +----------------------------------------------------------------------
namespace Api\Controller;
use Common\Controller\ApibaseController;
class MemberController extends ApibaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->shop = M("Shop");//店铺表
        $this->shopuser = M("ShopUser");//管理人员表
        $this->shopmember = M("ShopMember");//会员表
        $this->shopnumcard = M('ShopNumcard');//次卡表
        $this->shopdeadlinecard = M('ShopDeadlinecard');//期限卡表
        $this->shopcoursecard = M('ShopCoursecard');//疗程卡表
        $this->shopcourseproject = M('ShopCourseproject');//疗程卡项目表
        $this->shopitem = M('ShopItem');//项目表
        $this->financial = M('ShopFinancial');//明细表
        $this->shoppackage = D("ShopPackage");//会员卡套餐表
        $this->shopcika = D("ShopCika");//次卡套餐表
        $this->shopqixian = D("ShopQixian");//期限卡套餐表
        $this->shopliaocheng = D("ShopLiaocheng");//疗程卡套餐表
        $this->charge = D("ShopCharge");//会员充值表
        $this->shopmasseur = D("ShopMasseur");//技师表
        $this->shopmemberlabel = D("ShopMemberlabel");//会员标签表
        $this->setting = D("Setting");//参数表
        $this->where="shop_id=".$this->shop_id;
		$this->chainid=$this->chain_id;
        $this->user = $this->user_id;

    }
    //会员列表
    public function index()
    {
        $pagecur=!empty($_POST['pagecur']) ? $_POST['pagecur'] : 1;//当前第几个页
        $pagesize=!empty($_POST['pagesize']) ? $_POST['pagesize'] : 10;//每页显示个数
        $pagecur=($pagecur-1)*$pagesize;
        $keywords = trim(I('post.keywords','','htmlspecialchars'));
		$this->wheret="chain_id=".$this->chainid;
        if(!empty($keywords))
        {
            $this->wheret.=" and member_name like '%".$keywords."%'";
        }
        $result = $this->shopmember->field('id,member_no,member_name,sex,member_tel,balance,birthday')->where($this->wheret.' and state=1 and identity=1')->order('id desc')->limit($pagecur.",".$pagesize)->select();
        $count = $this->shopmember->field('id,member_no,member_name,sex,member_tel,balance')->where($this->wheret.' and state=1 and identity=1')->count();
        if($result){
            unset($data);
            $data['code'] = 0;
            $data['msg'] = 'success';
            $data['data'] = $result;
            $data['count'] = $count;
            outJson($data);
        }else{
            unset($data);
            $data['code'] = 0;
            $data['msg'] = 'success';
            $data['data'] = array();
            outJson($data);
        }
       
        
    }
    //会员详情
    public function member_details(){
        $member_id = I('post.member_id');
        //基本信息
        $info = $this->shopmember->field('id,member_no,member_name,sex,member_tel,birthday,member_card,remark,is_msg,discount,balance,createtime')->where($this->where.' and id='.$member_id.' and state=1')->find();
        if($info){
			$info['source']="前台";
            $info['createtime'] = date('Y-m-d H:i',$info['createtime']);
            unset($data);
            $data['code'] = 0;
            $data['msg'] = 'success';
            $data['data'] = $info;
            outJson($data);
        }else{
            unset($data);
            $data['code'] = 0;
            $data['msg'] = 'success';
            $data['data'] = array();
            outJson($data);
        }
    }
    //会员卡券详情
    public function card_list(){
        $member_id = I('post.member_id');
        //次卡信息
        $info['member_card'] =  $this->shopmember->field('id,shop_id,member_card,balance,is_msg,discount')->where($this->where.' and id='.$member_id.' and state=1')->select();
        foreach ($info['member_card'] as $key => &$value) {
            $value['shop_name'] = $this->shop->where('id='.$value['shop_id'])->getfield('shop_name');
        }
       
        $info['numcard'] = $this->shopnumcard->field('id,project_id,shop_id,card_num')->where($this->where.' and member_id='.$member_id)->select();
        if(!empty($info['numcard'])){
            foreach ($info['numcard'] as $key => &$value) {
                $value['project_name'] = $this->shopitem->where('id='.$value['project_id'])->getfield('item_name');
                $value['shop_name'] = $this->shop->where('id='.$value['shop_id'])->getfield('shop_name');
            }
        }
        //期限卡信息
        $info['deadline'] = $this->shopdeadlinecard->field('id,project_id,shop_id,end_time,day_ceiling')->where($this->where.' and member_id='.$member_id)->select();
        if(!empty($info['deadline'])){
            foreach ($info['deadline'] as $key => &$value) {
                $value['project_name'] = $this->shopitem->where('id='.$value['project_id'])->getfield('item_name');
                if(time()>=$value['end_time']){
                    $value['due_time'] = 1;
                }else{
                    $value['due_time'] = 0;
                }
                $value['end_time'] = date('Y-m-d',$value['end_time']);
                $value['shop_name'] = $this->shop->where('id='.$value['shop_id'])->getfield('shop_name');
                $consumption_ceilings = $this->financial->where("transaction_type=3 and card_id=".$value['id']." and createtime > ".strtotime(date("Y-m-d"))." and createtime < ".(strtotime(date("Y-m-d"))+3600*24))->select();
                $used = 0;
                if(!empty($consumption_ceilings)){
                    foreach ($consumption_ceilings as $k => $v) {
                        if($v['transaction_num'] < 0){
                            $used += ((-1)*$v['transaction_num']);
                        }
                    }
                   
                    $value['day_ceiling'] = $value['day_ceiling'] - $used;
                    
                }
            }
        }
        //疗程卡信息
        $info['course'] = $this->shopcoursecard->field('id,shop_id')->where('member_id='.$member_id)->select();
        if(!empty($info['course'])){
            
            foreach ($info['course'] as $key => &$value) {
                $value['projects'] = $this->shopcourseproject->field('project_id,card_num')->where($this->where.' and card_id='.$value['id'])->select();
                $value['count'] = $this->shopcourseproject->field('project_id,card_num')->where('card_id='.$value['id'])->count();
                $value['shop_name'] = $this->shop->where('id='.$value['shop_id'])->getfield('shop_name');
                foreach ($value['projects'] as $k => &$v) {
                    $v['project_name'] = $this->shopitem->where('id='.$v['project_id'])->getfield('item_name');
                    $v['item_price'] = $this->shopitem->where('id='.$v['project_id'])->getfield('item_price');
                }
               
            }
        }
        if($info){
            unset($data);
            $data['code'] = 0;
            $data['msg'] = 'success';
            $data['data'] = $info;
            outJson($data);
        }else{
            unset($data);
            $data['code'] = 0;
            $data['msg'] = 'success';
            $data['data'] = array();
            outJson($data);
        }
    }
    //会员交易明细
    public function member_financial(){
        $member_id = I('post.member_id');
        $pagecur=!empty($_POST['pagecur']) ? $_POST['pagecur'] : 1;//当前第几个页
        $pagesize=!empty($_POST['pagesize']) ? $_POST['pagesize'] : 10;//每页显示个数
        $pagecur=($pagecur-1)*$pagesize;
        $result = $this->financial->field('card_id,project_id,createtime,transaction_type,transaction_money,transaction_days,transaction_num,now_money,now_num,now_days,type')->where('member_id='.$member_id)->order('createtime desc,id desc')->limit($pagecur.",".$pagesize)->select();
        if($result){
            foreach ($result as $key => $value) {
                $result[$key]['createtime'] = date('Y-m-d',$value['createtime']);
                if($value['transaction_type'] == '1'){
                    $result[$key]['send_money'] = $this->charge->where('id='.$value['card_id'])->getfield('send_money');
                }
                if(!empty($value['project_id'])){
                    $result[$key]['item_name'] = $this->shopitem->where('id='.$value['project_id'])->getfield('item_name');
                }
            }
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
    //新增会员
    public function add_member(){
        $member_tel = I('post.member_tel');
        if(empty($member_tel)){
            unset($data);
            $data['code'] = 1;
            $data['msg'] = '手机号码不能为空';
            outJson($data);
        }else{
            $count = $this->shopmember->where('chain_id='.$this->chain_id.' and member_tel='.$member_tel)->count();
            if($count > 1){
                unset($data);
                $data['code'] = 1;
                $data['msg'] = '此手机号码已绑定会员';
                outJson($data);
            }
        }
        // $member_no = I('post.member_no',0,'intval');
        $member_no = get_member_sn($this->shop_id);
       
        $member_name = I('post.member_name');
        $discount = I('post.discount');
        if($discount){
            $is_msg = 1;
        }else{
            $is_msg = 0;
        }
        if(empty($member_name)){
            unset($data);
            $data['code'] = 1;
            $data['msg'] = '会员姓名不能为空';
            outJson($data); 
        }
        $sex = I('post.sex');
        $birthday = I('post.birthday');
        $member_card = I('post.member_card');
        if(!empty($member_card)){
            $count = $this->shopmember->where('chain_id='.$this->chain_id.' and member_card='.$member_card)->count();
            if($count > 1){
                unset($data);
                $data['code'] = 1;
                $data['msg'] = '此实体卡号已绑定会员';
                outJson($data);
            } 
        }
        $masseur_id = I('post.masseur_id');
        $remark = I('post.remark');
        $type = I('post.type');
        $is_custom = I('post.is_custom',0,'intval');
        unset($res);
        $res = array(
            'chain_id'=>$this->chain_id,
            'shop_id'=>$this->shop_id,
            'shopuser_id'=>$this->user,
            'member_no'=>$member_no,
            'member_name'=>$member_name,
            'discount'=>$discount,
            'is_msg'=>$is_msg,
            'sex'=>$sex,
            'member_tel'=>$member_tel,
            'birthday'=>$birthday,
            'member_card'=>$member_card,
            'remark'=>$remark,
            'createtime'=>time(),
        );
        $member_id = $this->shopmember->add($res);
        if($member_id){
            $package_id = I('post.package_id',0,'intval');
            if($type == 1){
                if($is_custom == 0){//会员卡套餐充值
                    $package_amount = $this->shoppackage->where('id='.$package_id)->getField('package_amount');
                    $give_amount = $this->shoppackage->where('id='.$package_id)->getField('give_amount');
                    $balance = (float)((float)$package_amount+(float)$give_amount);
                    $sellcard_reward = $this->shoppackage->where('id='.$package_id)->getField('rec_reward');
                }else{
                    $package_id = 0;
                    $package_amount = $_POST['recharge_money'];
                    $give_amount = $_POST['send_money'];
                    $balance = (float)((float)$package_amount+(float)$give_amount);
                    $sellcard_reward = I('post.sellcard_reward',0,'float');
                }
                unset($res);
                $res = array(
                    'member_id' => $member_id,
                    'package_id' => $package_id,
                    'chain_id'=>$this->chain_id,
                    'shop_id'=>$this->shop_id,
                    'shopuser_id'=>$this->user,
                    'recharge_money' => $package_amount,
                    'send_money' => $give_amount,
                    'is_custom' => $is_custom,
                    'createtime' => time()
                );
               $charge = $this->charge->add($res);
                if($charge!==false){
                    unset($res);
                    $res = array('balance' => $balance);

                    $is_discount = $this->shoppackage->where('id='.$package_id)->getField('is_discount');
                    $discount = $this->shoppackage->where('id='.$package_id)->getField('discount');
                    if(!empty($is_discount)){
                        $res['discount'] = $discount;
                    }

                    $this->shopmember->where('id='.$member_id)->save($res);
                    $rest = array(
                        'member_id' => $member_id,
                        'chain_id'=>$this->chain_id,
                        'shop_id'=>$this->shop_id,
                        'package_id' => $package_id,
                        'identity' => 1,
                        'transaction_type' => 1,
                        'transaction_money' => $package_amount,
                        'card_id' => $charge,
                        'sellcard_masseur' => $masseur_id,
                        'sellcard_reward' => $sellcard_reward,
                        'now_money' => $balance,
                        'pay_way' => $_POST['pay_way'],
                        'remark' => $remark,
                        'is_custom' => $is_custom,
                        'createtime' => time(),
                        'shopuser_id' => $this->user,
                    );
                    $this->financial->add($rest);
                }else{
                    unset($data);
                    $data['code'] = 1;
                    $data['msg'] = '办理会员卡失败！';
                    outJson($data);
                }
            }else if($type == 2){
                if($is_custom == 0){
                    $package_amount = $this->shopcika->where('id='.$package_id)->getField('package_amount');
                    $item_id = $this->shopcika->where('id='.$package_id)->getField('item_id');
                    $item_num = $this->shopcika->where('id='.$package_id)->getField('item_num');
                    $average = round($package_amount/$item_num,2);
                    $sellcard_reward = $this->shopcika->where('id='.$package_id)->getField('rec_reward');
                }else{
                    $package_id = 0;
                    $item_id = I('post.item_id');
                    if(empty($item_id)){
                        unset($data);
                        $data['code'] = 1;
                        $data['msg'] = '请选择项目！';
                        outJson($data);
                    }
                    $item_num = I('post.card_num');
                    $package_amount = I('post.numcard_money');
                    $sellcard_reward = I('post.sellcard_reward');
                    $average = round($numcard_money/$card_num,2);
                }
                unset($res);
                $res = array(
                    'member_id' => $member_id,
                    'cika_id' => $package_id,
                    'project_id' => $item_id,
                    'card_num' => $item_num,
                    'chain_id'=>$this->chain_id,
                    'shop_id'=>$this->shop_id,
                    'numcard_money' => $package_amount,
                    'is_custom' => $is_custom,
                    'average' => $average,
                    'shopuser_id' => $this->user,
                    'createtime' => time()
                );
                $numcard = $this->numcard->add($res);
                if($numcard!==false){
                    unset($rest);
                    $rest = array(
                        'member_id' => $member_id,
                        'chain_id'=>$this->chain_id,
                        'shop_id'=>$this->shop_id,
                        'project_id' => $item_id,
                        'package_id' => $package_id,
                        'identity' => 1,
                        'card_id' => $numcard,
                        'transaction_type' => 2,
                        'transaction_money' => $package_amount,
                        'transaction_num' => $item_num,
                        'sellcard_masseur' => $masseur_id,
                        'sellcard_reward' => $sellcard_reward,
                        'now_money' => $package_amount,
                        'now_num' => $item_num,
                        'pay_way' => $_POST['pay_way'],
                        'remark' => $remark,
                        'shopuser_id' => $this->user,
                        'is_custom' => $is_custom,
                        'createtime' => time()
                    );
                    $this->financial->add($rest);
                    
                }else{
                    unset($data);
                    $data['code'] = 1;
                    $data['msg'] = '办理次卡失败！';
                    outJson($data);
                }
            }else if($type == 3){
                if($is_custom == 0){
                    $package_amount = $this->shopqixian->where('id='.$package_id)->getField('package_amount');
                    $item_id = $this->shopqixian->where('id='.$package_id)->getField('item_id');
                    $sellcard_reward = $this->shopqixian->where('id='.$package_id)->getField('rec_reward');
                    $effective_long = $this->shopqixian->where('id='.$package_id)->getField('expiry_during');
                    $day_ceiling = $this->shopqixian->where('id='.$package_id)->getField('limit_times');
                    $end_time = time()+86400*$effective_long;
                }else{
                    $package_id = 0;
                    $item_id = I('post.item_id');
                    if(empty($item_id)){
                        $this->error('请选择项目！');
                    }
                    $package_amount = I('post.card_money');
                    $effective_long = I('post.effective_long');
                    $day_ceiling = I('post.day_ceiling');
                    $sellcard_reward = I('post.sellcard_reward',0,'float');
                    $end_time = time()+86400*$effective_long;
                }
                unset($res);
                $res = array(
                    'member_id' => $member_id,
                    'qixian_id' => $package_id,
                    'project_id' => $item_id,
                    'chain_id'=>$this->chain_id,
                    'shop_id'=>$this->shop_id,
                    'card_money' => $package_amount,
                    'effective_long' => $effective_long,
                    'day_ceiling' => $day_ceiling,  
                    'shopuser_id' => $this->user,                     
                    'is_custom' => $is_custom,
                    'createtime' => time(),
                    'start_time' => time(),
                    'end_time' => $end_time,
                );
                $deadlinecard = $this->deadlinecard->add($res);
                if($deadlinecard !== false){
                    unset($rest);
                    $rest = array(
                        'member_id' => $member_id,
                        'chain_id'=>$this->chain_id,
                        'shop_id'=>$this->shop_id,
                        'project_id' => $item_id,
                        'package_id' => $package_id,
                        'identity' => 1,
                        'card_id' => $deadlinecard,
                        'transaction_type' => 3,
                        'transaction_money' => $package_amount,
                        'sellcard_masseur' => $masseur_id,
                        'sellcard_reward' => $sellcard_reward,
                        'shopuser_id' => $this->user,
                        'now_money' => $package_amount,
                        'now_days' => $effective_long,
                        'pay_way' => $_POST['pay_way'],
                        'remark' => $remark,
                        'is_custom' => $is_custom,
                        'createtime' => time()
                    );
                    $this->financial->add($rest);
                }else{
                    unset($data);
                    $data['code'] = 1;
                    $data['msg'] = '办理期限卡失败！';
                    outJson($data);
                }
            }else if($type == 4){
                if($is_custom == 0){
                    $card_money = $this->shopliaocheng->where('id='.$package_id)->getField('package_amount');
                    $project_id = $this->shopliaocheng->where('id='.$package_id)->getField('item');
                    $sellcard_reward = $this->shopliaocheng->where('id='.$package_id)->getField('rec_reward');
                    $description = $this->shopliaocheng->where('id='.$package_id)->getField('remark');
                    $projects=unserialize($project_id);
                    $num = 0;
                    foreach ($projects as $key => $value) {
                        $num += $value['number'];
                    }
                    $average = round($card_money/$num,2);
                    unset($res);
                    $res = array(
                        'member_id' => $member_id,
                        'liaocheng_id' => $package_id,
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
                                'package_id' => $package_id,
                                'card_id' => $coursecard,
                                'transaction_type' => 4,
                                'transaction_money' => $card_money,
                                'sellcard_masseur' => $masseur_id,
                                'sellcard_reward' => $sellcard_reward,
                                'shopuser_id' => $this->user,
                                'now_money' => $card_money,
                                'now_num' => $value['number'],
                                'pay_way' => $_POST['pay_way'],
                                'remark' => $remark,
                                'is_custom' => $is_custom,
                                'createtime' => time()
                            );
                            $this->financial->add($rest);
                        }
                    }
                }else{
                    $package_id = 0;
                    $card_money = I('post.card_money');
                    $description = I('post.description','');
                    $sellcard_reward = I('post.sellcard_reward');
                    $project_id = I('post.item_id');
                    $card_num = I('post.card_num');
                    $num = 0;
                    foreach ($card_num as $key => $value) {
                        $num += $value;
                    }
                    $average = round($card_money/$num,2);

                    unset($res);
                    $res = array(
                        'member_id' => $member_id,
                        'card_money' => $card_money,
                        'description' => $description,
                        'is_custom' => $is_custom,
                        'chain_id' => $this->chain_id,
                        'shop_id' => $this->shop_id,
                        'average' => $average,
                        'createtime' => time(),
                        'shopuser_id' => $this->user,
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
                                'transaction_num' => $card_num[$key],
                                'identity' => 1,
                                'card_id' => $coursecard,
                                'transaction_type' => 4,
                                'transaction_money' => $card_money,
                                'sellcard_masseur' => $masseur_id,
                                'sellcard_reward' => $sellcard_reward,
                                'shopuser_id' => $this->user,
                                'now_money' => $card_money,
                                'now_num' => $card_num[$key],
                                'pay_way' => $_POST['pay_way'],
                                'remark' => $remark,
                                'is_custom' => $is_custom,
                                'createtime' => time(),
                            );
                            $this->financial->add($rest);
                        }else{
                            unset($data);
                            $data['code'] = 1;
                            $data['msg'] = '办理疗程卡失败！';
                            outJson($data);
                        }
                    }
                }
            }
            if(!empty($masseur_id)){
                $bala = $this->shopmasseur->where('id='.$masseur_id)->getField('balance');
                unset($data);
                $data = array('id'=>$masseur_id,'balance'=>$bala+$sellcard_reward);
                $this->shopmasseur->save($data);
            }
            unset($data);
            $data['code'] = 0;
            $data['msg'] = '添加并办理成功！';
            outJson($data);
        }else{
            unset($data);
            $data['code'] = 1;
            $data['msg'] = '添加会员失败！';
            outJson($data);
        }
    }
    //会员编辑备注
    public function edit_remark(){
        $member_id = I('post.member_id');//会员id
        $remark = I('post.remark');//备注
        if(empty($member_id)){
            unset($data);
            $data['code'] = 1;
            $data['msg'] = '请选择会员！';
            outJson($data);
        }
        $result = $this->shopmember->where($this->where.' and id='.$member_id)->save(array('remark'=>$remark));
        if($result !== false){
            unset($data);
            $data['code'] = 0;
            $data['msg'] = 'success';
            outJson($data);
        }
    }
    //会员健康档案接口
    public function health_records(){
        $member_id = I('post.member_id');
        $result['labels'] = $this->shopmemberlabel->field('id,label')->where($this->where.' and member_id='.$member_id)->order('sort asc')->select();
        $result['default'] = $this->setting->field('id,title')->where('parentid=6 and status=1')->order('sort asc')->select();
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
    //添加会员标签
    public function add_label(){
        $member_id = I('post.member_id');
        $labels = I('post.labels');
        //$arr = json_decode($labels,true);
        // $labels = str_replace("'", '', $labels);
        // $labels = ltrim($labels,"'");
        // $labels = rtrim($labels,"'");
        // $arr = json_decode($labels,true);
        // file_put_contents('1.txt',var_export($labels,TRUE));
        $labels = explode(',', $labels);
        $this->shopmemberlabel->where('member_id='.$member_id)->delete();
        foreach ($labels as $key => $value) {
            $res = array(
                'member_id'=>$member_id,
                'label'=>$value,
                'chain_id' => $this->chain_id,
                'shop_id' => $this->shop_id,
                'create_time'=>time(),
            );
           
            $result = $this->shopmemberlabel->add($res);
            
        }
        unset($data);
        $data['code'] = 0;
        $data['msg'] = 'success';
        outJson($data);
        
    }
    //删除会员标签
    public function del_label(){
        $member_id = I('post.member_id');
        $label_id = I('post.label_id');
        $result = $this->shopmemberlabel->where('member_id='.$member_id.' and id='.$label_id)->delete();
        if($result !== false){
            unset($data);
            $data['code'] = 0;
            $data['msg'] = 'success';
            outJson($data);
        }else{
            unset($data);
            $data['code'] = 1;
            $data['msg'] = 'error';
            outJson($data);
        }
    }
    


    //会员交易明细
    public function allmember_financial(){
        $start_time = I('post.start_time');
        $end_time = I('post.end_time');
        if($start_time=='' || $end_time=='')
        {
            $start_time=time()-24*3600*30;
            $end_time=time();
        }else
        {
            $start_time=strtotime($start_time);
            $end_time=strtotime($end_time);
        }
        $pagecur=!empty($_POST['pagecur']) ? $_POST['pagecur'] : 1;//当前第几个页
        $pagesize=!empty($_POST['pagesize']) ? $_POST['pagesize'] : 10;//每页显示个数
        $pagecur=($pagecur-1)*$pagesize;
        $result = $this->financial->field('id,member_id,transaction_type,transaction_money,transaction_days,transaction_num,now_money,now_num,now_days,type,createtime')->where($this->where.' and createtime >'.$start_time.' and createtime<='.$end_time)->order('createtime desc,id desc')->limit($pagecur.",".$pagesize)->select();

        if($result){
            foreach ($result as $key => $value) {
                $result[$key]['createtime'] = date('Y-m-d',$value['createtime']);
                $result[$key]['member_no'] = $this->shopmember->where('id='.$value['member_id'])->getfield('member_no');
                $result[$key]['member_name'] = $this->shopmember->where('id='.$value['member_id'])->getfield('member_name');
                $result[$key]['member_tel'] = $this->shopmember->where('id='.$value['member_id'])->getfield('member_tel');
            }
            unset($data);
            $data['code'] = 0;
            $data['msg'] = '会员交易明细获取成功！';
            $data['data'] = $result;
            outJson($data);
        }else{
            unset($data);
            $data['code'] = 0;
            $data['msg'] = '暂无交易信息！';
            $data['data'] = array();
            outJson($data);
        }
    }


    //交易明细详情
    public function financial_detail(){
        $id = I('post.id');
        if(empty($id)){
            unset($data);
            $data['code'] = 1;
            $data['msg'] = '无此交易信息！';
            outJson($data);
        }
        $info = $this->financial->field('id,shop_id,project_id,transaction_type,transaction_money,transaction_days,transaction_num,now_money,now_num,now_days,type,shopuser_id,remark,pay_way,createtime')->where($this->where.' and id='.$id)->find();
        if(!empty($info['project_id'])){
            $info['item_name'] = $this->shopitem->where('id='.$info['project_id'])->getfield('item_name');
        }
        $info['shop_name'] = $this->shopuser->where('id='.$info['shopuser_id'])->getfield('name');
        $info['shop_phone'] = $this->shopuser->where('id='.$info['shopuser_id'])->getfield('username');
        unset($data);
        $data['code'] = 0;
        $data['msg'] = 'success';
        $data['data'] = $info;
        outJson($data);
    }
}
?>