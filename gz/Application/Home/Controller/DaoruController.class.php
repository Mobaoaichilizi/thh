<?php

namespace Home\Controller;
use Common\Controller\HomebaseController;
class DaoruController extends HomebaseController {
	public function _initialize() {
		
		$agent = $_SERVER['HTTP_USER_AGENT']; 
		


	}

    public function start(){
		ob_end_clean();
		$res=M("Crm")->order('id asc')->select();
		foreach($res as $row)
		{
			$resc=M("User")->where("username!='".$row['mobile']."'")->find();
			if($resc)
			{
				unset($data);
				$data=array(
					'username' => $row['mobile'],
					'password' => $row['password'],
					'role' => 2,
					'inviter_share_number' => $row['inviter_share_number'],
					'share_number' => $row['share_number'],
					//'balance' => $row['sy_money'],
					'is_login' => 0,
					'createtime' => $row['regtime'],
				);
				$result=M('User')->where("username!='".$row['mobile']."'")->add($data);
				if($result)
				{
					unset($datac);
					if($row['sex']=='男')
					{
						$sex=2;
					}else if($row['sex']=='女')
					{
						$sex=1;
					}else
					{
						$sex=0;
					}
					$datac=array(
						'user_id' => $result,
						'name' => $row['truename'],
						'img_thumb' => $row['face'],
						'sex' => $sex,
						'card' => $row['card_num'],
						'age' => $row['age'],
						'status' => 1,
						'is_first' => 1,
					);
					M('Patientmember')->add($datac);
					
				}
				echo $result."成功！";
				flush();
				ob_flush();
				sleep(1);
			}
		}
	}
	public function time(){
		ob_end_clean();
		$res = M('User')->where('role=1')->order('id asc')->select();
		foreach ($res as $row) {
		
				$data=array(
					'doctor_id' => $row['id'],
					'createtime' => $row['createtime'],
				);
				$result = M('Consultation')->add($data);
				echo $result."成功！";
				flush();
				ob_flush();
				sleep(1);
			
			
		}

	}
}