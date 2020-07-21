<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:82:"/www/web/toupiao_com/public_html/public/../application/admin/view/login/index.html";i:1591323892;}*/ ?>
<!DOCTYPE html>
<html lang="en"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta charset="UTF-8">
	<title>欢迎您登录管理系统</title>
	<link rel="stylesheet" href="/static/css/login.css">
	<script src="/static/js/jquery-1.10.2.min.js"></script>
	<script src="/static/js/particles.js"></script>
</head>
<body style="width: 100%; height: 100%; overflow: hidden; background-color: rgb(36, 114, 180);">
	<div id="particles-js"><canvas width="1430" height="715" style="width: 100%; height: 100%;"></canvas></div>
	 <form action="<?php echo Url('dologin'); ?>" method="post" class="login-form">
		<div class="wrapper_login">
			<ul class="form">
				<li class="ele_form ele_input ele_username">
					 <input type="text" name="username" placeholder="请填写用户名" autocomplete="off" />
				</li>
		  <li class="ele_form ele_input ele_password">
					 <input type="password" name="password" placeholder="请填写密码" autocomplete="off" />
				</li>
		  <li class="ele_form ele_input ele_code">
					  <input type="text" name="verify" placeholder="请填写验证码" autocomplete="off"><div><a class="reloadverify" title="换一张" href="javascript:void(0)">换一张？</a></div>
		  </li>
		 <li class="ele_form">
				<img class="verifyimg" alt="点击切换" src="<?php echo Url('Login/verify'); ?>">
				</li>
                 <div class="check-tips"></div>
		  <li class="ele_form ele_submit">
			  <input type="submit" value="登录"><br>
			</li>
		  </ul>
		</div>
	</form>

<script>
	window.particlesJS&&particlesJS("particles-js",{particles:{color:"#46BCF3",shape:"circle",opacity:1,size:1,size_random:!0,nb:200,line_linked:{enable_auto:!0,distance:100,color:"#46BCF3",opacity:.5,width:1,condensed_mode:{enable:!1,rotateX:600,rotateY:600}},anim:{enable:!0,speed:2.5}},interactivity:{enable:!0,mouse:{distance:250},detect_on:"canvas",mode:"grab",line_linked:{opacity:.35},events:{onclick:{enable:!0,mode:"push",nb:3}}},retina_detect:!0});
	
	$(function(){
		$("form").submit(function(){

    		var self = $(this);


    		$.post(self.attr("action"), self.serialize(), success, "json");

    		return false;
    		function success(data){
    			if(data.code){

    				window.location.href = data.url;

    			} else {



    				self.find(".check-tips").text(data.msg);



    				//刷新验证码



    				$(".reloadverify").click();



    			}



    		}



    	});



			//初始化选中用户名输入框



			$("#itemBox").find("input[name=username]").focus();



			//刷新验证码



			var verifyimg = $(".verifyimg").attr("src");



            $(".reloadverify").click(function(){



                if( verifyimg.indexOf('?')>0){



                    $(".verifyimg").attr("src", verifyimg+'&random='+Math.random());



                }else{



                    $(".verifyimg").attr("src", verifyimg.replace(/\?.*$/,'')+'?'+Math.random());



                }



            });
			  });
</script>

</body></html>