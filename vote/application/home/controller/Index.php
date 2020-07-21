<?php

// +----------------------------------------------------------------------

// | 系统首页文件

// +----------------------------------------------------------------------

// | Copyright (c) 2017-2018 All rights reserved.

// +----------------------------------------------------------------------

// | Author: Liuguoqiang <415199201@qq.com>

// +----------------------------------------------------------------------

namespace app\home\controller;



use app\common\controller\HomeBasic;

use think\Request;

use think\Url;

use think\Db;

use app\home\model\LoginLog as LoginLogModel;



class Index extends HomeBasic

{

    public function login()

    {
        if(session('student_uid')){

            $this->redirect(Url::build("home/index/index"));
        }

        return $this->fetch();

    }

    public function index()

    {

        $student_uid=session('student_uid');
        

        
		if(empty($student_uid))

		{

			$this->redirect(Url::build("home/index/login"));

		}
        $student_number =  Db::name("Student")->where('id='.$student_uid)->value('student_id');

        $count = Db::name("Vote")->where("student_id = '".$student_number."' and votetime >' ".strtotime(date("Y-m-d"))."' and votetime < '".(strtotime(date("Y-m-d"))+3600*24)."'")->count();

		if($count > 0){

			$this->redirect(Url::build("home/index/vote_result"));
		}

		$result = Db::name("Config")->where("id=1")->find();

		$result['start_time'] = strtotime($result['web_start_time']);

		$result['end_time'] = strtotime($result['web_end_time']);

		$result['end_time'] = $result['end_time'] - time()+24*60*60;

		if($result['end_time'] < 0){

			$this->redirect(Url::build("home/index/vote_result"));

		}

		$where['app_name'] = ['eq', 'Home'];

		$where['status'] = ['eq', '1'];

		$result['count'] = Db::name("Teacher")->count();

		$result['total_vote'] = Db::name("Vote")->count();

		$result['click_num'] = Db::name("LoginLog")->where($where)->count();

		$fa_teacher = Db::name("Teacher")->where('type=1')->order("sort asc,id desc")->select();

		$unfa_teacher = Db::name("Teacher")->where('type=0')->order("sort asc,id desc")->select();

		$student_number =  Db::name("Student")->where('id='.$student_uid)->value('student_id');

		$this->assign('result',$result);

		$this->assign('fa_teacher',$fa_teacher);

		$this->assign('student_id',$student_number);

		$this->assign('unfa_teacher',$unfa_teacher);

		return $this->fetch();

		

    }



    //登陆提交

	public function dologin()

	{

		$student_id = input('post.student_id');

    	if(empty($student_id)){

    		$this->error('请输入学号！');

    	}

		$user=Db::name('Student');

		$result = $user->where("student_id='".$student_id."' and status=1")->find();

    

        if(!empty($result))

		{

            LoginLogModel::insertLog($student_id,'1','该学号登陆成功！');

            session('student_uid',$result['id']);

            $res['last_login_ip']=Request::instance()->ip();

            $res['last_login_time']=time();

            $user->where("id=".$result['id'])->update($res);

            $this->success('登录成功！',Url::build('Index/index'));

		

		}else{

            LoginLogModel::insertLog($student_id,'-1','该学号不存在');

			$this->error('该学号不存在！');

		}

		

	}

	public function information(){

		$result = Db::name("Config")->where("id=1")->find();

		$this->assign('result',$result);

		return $this->fetch();

	}

	//投票限制和投票记录存入

	public function student_vote(){

		$lawteacher_ids = input('choice/a');

		$unlawteacher_ids = input('choice1/a');

		$ip = input('ip');

		$student_id = input('student_id');

		if(empty($lawteacher_ids) && empty($unlawteacher_ids)){

			unset($data);

			$data['code'] = 0;

			$data['message'] = "请选择老师！";

			exit(json_encode($data));

		}

		$count = Db::name("Vote")->where("student_id = '".$student_id."' and votetime >' ".strtotime(date("Y-m-d"))."' and votetime < '".(strtotime(date("Y-m-d"))+3600*24)."'")->count();

		if($count > 0){

			unset($data);

			$data['code'] = 1;

			$data['message'] = "你已经投过票了";

			exit(json_encode($data));

		}

		// $count_ip = Db::name("Vote")->where("ip = '".$ip."' and votetime > ".(time()-600))->count();

		// if($count_ip > 0){

		// 	unset($data);

		// 	$data['code'] = 0;

		// 	$data['message'] = "请十分钟后再次投票！";

		// 	exit(json_encode($data));

		// }

		if(count($lawteacher_ids) > 5){

			unset($data);

			$data['code'] = 0;

			$data['message'] = "法学专业老师最多可选择五位！";

			exit(json_encode($data));

		}

		if(count($unlawteacher_ids) > 5){

			unset($data);

			$data['code'] = 0;

			$data['message'] = "非法学专业老师最多可选择五位！";

			exit(json_encode($data));

		}
		
		if(count($lawteacher_ids) == 1){

			$res = array(

				'ip' => $ip,

				'student_id' => $student_id,

				'teacher_id' => $lawteacher_ids[0],

				'votetime' => time(),

			);

			$result = Db::name("Vote")->insert($res);

		}else if(count($lawteacher_ids) > 0){

			foreach ($lawteacher_ids as $k => $v) {

				$res = array(
	
					'ip' => $ip,
	
					'student_id' => $student_id,
	
					'teacher_id' => $v,
	
					'votetime' => time(),
	
				);
	
				$result = Db::name("Vote")->insert($res);
	
			}
		}
		
		if(count($unlawteacher_ids) == 1){

			$res = array(

				'ip' => $ip,

				'student_id' => $student_id,

				'teacher_id' => $unlawteacher_ids[0],

				'votetime' => time(),

			);

			$result1 = Db::name("Vote")->insert($res);

		}else if(count($unlawteacher_ids) > 0){

			foreach ($unlawteacher_ids as $k1 => $v1) {

				$res = array(
	
					'ip' => $ip,
	
					'student_id' => $student_id,
	
					'teacher_id' => $v1,
	
					'votetime' => time(),
	
				);
	
				$result1 = Db::name("Vote")->insert($res);
	
			}
		}


		if((!empty($lawteacher_ids) && $result) || (!empty($unlawteacher_ids) && $result1)){

			unset($data);

			$data['code'] = 1;

			$data['message'] = "投票成功！";

			exit(json_encode($data));

		}else{

			unset($data);

			$data['code'] = 0;

			$data['message'] = "投票失败！";

			exit(json_encode($data));

		}



	}

	public function vote_result(){

		$fa_teacher = Db::name("Teacher")->where('type=1')->order("sort asc,id desc")->select();

		$unfa_teacher = Db::name("Teacher")->where('type=0')->order("sort asc,id desc")->select();

		foreach ($fa_teacher as $key => $value) {

			$law[$key]['id'] = $value['id'];

			$law[$key]['count'] = Db::name("Vote")->Join('dade_teacher','dade_vote.teacher_id = dade_teacher.id')->where("teacher_id=".$value['id'])->count();

			$law[$key]['name'] = $value['name'];

			

			$law[$key]['img_thumb'] = $value['img_thumb'];



		}

		$arr2 = array_map(create_function('$n', 'return $n["count"];'), $law);

		array_multisort($arr2,SORT_DESC,SORT_NUMERIC,$law);

		foreach ($unfa_teacher as $key => $value) {

			$unlaw[$key]['id'] = $value['id'];

			$unlaw[$key]['count'] = Db::name("Vote")->Join('dade_teacher','dade_vote.teacher_id = dade_teacher.id')->where("teacher_id=".$value['id'])->count();

			$unlaw[$key]['name'] = $value['name'];

			$unlaw[$key]['img_thumb'] = $value['img_thumb'];

		}

		$arr1 = array_map(create_function('$n', 'return $n["count"];'), $unlaw);

		array_multisort($arr1,SORT_DESC,SORT_NUMERIC,$unlaw);

		$total_count = Db::name("Vote")->where("1=1")->count(); 

		$this->assign('law',$law);

		$this->assign('unlaw',$unlaw);

		$this->assign('total_count',$total_count);

		return $this->fetch();

	}

	

}

