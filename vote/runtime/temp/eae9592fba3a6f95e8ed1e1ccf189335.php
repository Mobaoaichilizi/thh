<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:81:"/www/web/toupiao_com/public_html/public/../application/home/view/index/login.html";i:1593310709;}*/ ?>
﻿<!doctype html><html><head>

<meta charset="utf-8"><meta name="keywords" content="" /><meta name="description" content="" />

<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0" />

<title>首页</title>

<link rel="stylesheet" href="/static/css/public.css">

</head>

<body>

<script src="/static/js/jquery.min.js"></script>

<style type="text/css">
    .instructions { width:100%; margin:20px auto; float: left; color:#666; line-height:2em;}
</style>

<DIV class="pet_mian">

    

    <div class="loginbox">

        <div class="logo"><img src="/static/images/loginbg.jpg"></div>

        <div class="login">

            <div class="dlbox">

            	<form action="<?php echo Url('dologin'); ?>" method="post" id="form">

                    <!-- <dd><input placeholder="请输入您的学号" type="text"  name="student_id" id="student_id" oninput="value=value.replace(/[^\d]/g,'')"></dd> -->
                    <dd><input placeholder="请输入您的学号" type="text"  name="student_id" id="student_id"></dd>

	                <dd><a class="but">立即登入</a></dd>

	            </form>

            </div>
            <div class="instructions">
                <p style="color: #F00; font-weight: bold;">说明：</p>
                <p>1、每个手机只能登陆一个学号，首次投票后，将绑定学号与登录手机，不可再登陆其它学号。</p>
                <p>2、每个学号每天只能投票一次，法学专业教师可投5人，法学以外专业教师可投5人。</p>
            </div>
        </div>
         
    </div>
    

   

</DIV>

<!-- -->

<script>

    $(function(){

        $('.but').click(function(){

            var self = $('#form');

            $.post(self.attr("action"), self.serialize(), success, "json");

            return false;

            function success(data){

                if(data.code){

                    window.location.href = data.url;

                } else {

                    alert(data.msg);

                }

            }

        });

    })

</script>



</body>

</html>