<?php
	include_once("ServerAPI.php");
	$appKey	="3argexb630vte";//appKey
    $appSecret	= "QhioQ2KX4Hmf";//secret
	$rongyun = new ServerAPI($appKey,$appSecret);
	//这里需要curl验证token是否合法
	$act = !empty($_POST['act'])?trim($_POST['act']):'';
	//获取融云token
	if($act == 'getToken')
	{
		$userId = $_POST['userId'];
		$uname = $_POST['uname'];
		$face = $_POST['face'];
		$token = $rongyun->getToken($userId,$uname,$face);
		echo $token;
	}
	//创建群组
	if($act == 'createGroup')
	{
		$userId = $_POST['userId'];
		$groupId = $_POST['groupId'];
		$groupName = $_POST['groupName'];
		$group = $rongyun->groupCreate($userId,$groupId,$groupName);
		echo $group;
	}
	//修改群组
	if($act == 'refreshGroup')
	{
		$groupId = $_POST['groupId'];
		$groupName = $_POST['groupName'];
		$group = $rongyun->groupRefresh($groupId,$groupName);
		echo $group;
	}
	//加入群组
	if($act == 'joinGroup')
	{
		$userId = $_POST['userId'];
		$groupId = $_POST['groupId'];
		$groupName = $_POST['groupName'];
		$userIdArr = explode(",",$userId);
		echo $rongyun->groupJoin($userIdArr,$groupId,$groupName);
	}
	//删除群组，解散群组
	if($act == 'delGroup')
	{
		$userId = $_POST['userId'];
		$groupId = $_POST['groupId'];
		echo $rongyun->groupDismiss($userId, $groupId);	
	}
	//将用户从群中移除，不再接收该群组的消息。
	if($act == 'quitGroup')
	{
		$userId = $_POST['userId'];
		$groupId = $_POST['groupId'];
		$userIdArr = explode(",",$userId);
		echo $rongyun->groupQuit($userIdArr,$groupId);
	}
	//查询群成员
	if($act == 'groupUserQuery')
	{
		$groupId = $_POST['groupId'];
		echo $rongyun->groupUserQuery($groupId);
	}
	//刷新用户信息
	if($act == 'userRefresh')
	{
		$userId = $_POST['userId'];
		$face = $_POST['face'];
		$uname = $_POST['uname'];
		echo $rongyun->userRefresh($userId,$face,$uname);
	}
?>