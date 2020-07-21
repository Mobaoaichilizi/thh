<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:81:"/www/web/toupiao_com/public_html/public/../application/home/view/index/index.html";i:1593401663;}*/ ?>
﻿<!doctype html><html><head>

<meta charset="utf-8"><meta name="keywords" content="" /><meta name="description" content="" />

<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0" />

<title>首页</title>

<link rel="stylesheet" href="/static/css/public.css">

</head>

<body>

<script src="/static/js/jquery.min.js"></script>

<DIV class="pet_mian">



	<div class="banner"><img src="/static/images/banner.jpg"></div>

    <div class="titbox">

    	<dd>参与选手<p><?php echo $result['count']; ?></p></dd>

    	<dd>累计投票<p><?php echo $result['total_vote']; ?></p></dd>

    	<dd>访问量<p><?php echo $result['click_num']; ?></p></dd>

    </div>



    <div class="timebox"><!-- <i>22</i>天<i>16</i>时<i>27</i>分<i>48</i>秒 --></div>

    <script type="text/javascript">

        function countDown(times){

          var timer=null;

          timer=setInterval(function(){

            var day=0,

               hour=0,

               minute=0,

               second=0;//时间默认值

            if(times > 0){

              day = Math.floor(times / (60 * 60 * 24));

              hour = Math.floor(times / (60 * 60)) - (day * 24);

              minute = Math.floor(times / 60) - (day * 24 * 60) - (hour * 60);

              second = Math.floor(times) - (day * 24 * 60 * 60) - (hour * 60 * 60) - (minute * 60);

            }

            if (day <= 9) day = '0' + day;

            if (hour <= 9) hour = '0' + hour;

            if (minute <= 9) minute = '0' + minute;

            if (second <= 9) second = '0' + second;

            //

            // console.log(day+"天:"+hour+"小时："+minute+"分钟："+second+"秒");

            $(".timebox").html('');

            $(".timebox").append("<i>"+day+"</i>天<i>"+hour+"</i>时<i>"+minute+"</i>分<i>"+second+"</i>秒");

            times--;

          },1000);

          if(times<=0){

            clearInterval(timer);

          }

        }

       

        countDown(<?php echo $result['end_time']; ?>);

    </script>

    <div class="titbox2">

    	<dd><img src="/static/images/qb1.jpg"><?php echo $result['web_start_time']; ?> 至 <?php echo $result['web_end_time']; ?></dd>

    	<a href="<?php echo url('Index/information'); ?>"><dd><img src="/static/images/qb2.jpg">活动介绍</dd></a>

    </div>

    <form action="" method="post" id="form1">

    <div class="bigbox">

    	<div class="tit">法学专业老师（按姓氏笔画排序）</div>

        <?php if(is_array($fa_teacher) || $fa_teacher instanceof \think\Collection || $fa_teacher instanceof \think\Paginator): $i = 0; $__LIST__ = $fa_teacher;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>

            <div class="piao">

                <dd class="tx l"><img src="/static/images/loading1.gif" data-src="<?php echo $vo['img_thumb']; ?>" class="teacherpic"></dd>

                <dd class="nr r">

                    <?php echo $vo['name']; ?><p class="bt2" style="height: 30px" title="<?php echo $vo['description']; ?>"><?php echo $vo['description']; ?></p>

                    <input type="checkbox"  value="<?php echo $vo['id']; ?>" name="choice[]">

                </dd>

            </div>

        <?php endforeach; endif; else: echo "" ;endif; ?>



    	

    </div>



    <div class="bigbox">

    	<div class="tit">法学以外专业老师（按姓氏笔画排序）</div>

        <?php if(is_array($unfa_teacher) || $unfa_teacher instanceof \think\Collection || $unfa_teacher instanceof \think\Paginator): $i = 0; $__LIST__ = $unfa_teacher;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>

            <div class="piao">

                <dd class="tx l"><img src="/static/images/loading1.gif" data-src="<?php echo $vo['img_thumb']; ?>" class="teacherpic"></dd>

                <dd class="nr r">

                    <?php echo $vo['name']; ?><p class="bt2" style="height: 30px" title="<?php echo $vo['description']; ?>"><?php echo $vo['description']; ?></p>

                    <input type="checkbox"  value="<?php echo $vo['id']; ?>" name="choice1[]">

                    

                </dd>

            </div>

        <?php endforeach; endif; else: echo "" ;endif; ?>

    	

    </div>



    <div class="m40"></div>

    <input type="hidden" id="student_id" value="<?php echo $student_id; ?>" name="student_id">

    <input type="hidden" id="ip" value="" name="ip">

    <div class="fotbox1">已选择：<b class="checked_num">0</b>/<?php echo $result['count']; ?><a class="fbut1">投票</a></div>

    </form>

    <script src="http://pv.sohu.com/cityjson?ie=utf-8"></script>



    <script type="text/javascript">

         //活动结束关闭页面

        window.onload = function(){

           var tim = <?php echo $result['end_time']; ?>;

           if(tim <= 0){

            $(".fotbox1").html("投票已结束！");

            $(".fotbox1").css({"background-color":"#ccc","font-size":"16px","text-align":"center","color":"#fff"});  

            

           }

        }

        $(function(){

            $("#ip").val(returnCitySN['cip']);

            $("input[type='checkbox']").bind("click",function(){

                var len = $("input[name='choice[]']:checked").length;

                

                var len1 = $("input[name='choice1[]']:checked").length;

               

                $('.checked_num').html(len+len1);

            });

        });



        $(function(){



    //加上判断是否达到数量要求

        if($("input[name='choice[]']:checked").size()>=5){

             $("input[name='choice[]']").removeAttr("checked");

             $("input[name='choice[]']").attr("disabled","disabled");

             $("input[name='choice[]']").removeAttr("disabled");

         }



        var num = 0;

        $("input[name='choice[]']").each(function(){

            $(this).click(function(){

                if($(this)[0].checked) {

                    ++num;

                    if(num == 5) {

                        //alert("最多选择 5项 的上限已满, 其他选项将会变为不可选.");

                        $("input[name='choice[]']").each(function(){

                            if(!$(this)[0].checked) {

                                $(this).attr("disabled", "disabled");

                            }

                        });

                    }

                } else {

                    --num;

                    if(num <= 4) {

                        $("input[name='choice[]']").each(function(){

                            if(!$(this)[0].checked) {

                                $(this).removeAttr("disabled");

                            }

                        });

                    }

                }

            });

        });





        if($("input[name='choice1[]']:checked").size()>=5){

             $("input[name='choice1[]']").removeAttr("checked");

             $("input[name='choice1[]']").attr("disabled","disabled");

             $("input[name='choice1[]']").removeAttr("disabled");

         }



        var num1 = 0;

        $("input[name='choice1[]']").each(function(){

            $(this).click(function(){

                if($(this)[0].checked) {

                    ++num1;

                    if(num1 == 5) {

                        //alert("最多选择 5项 的上限已满, 其他选项将会变为不可选.");

                        $("input[name='choice1[]']").each(function(){

                            if(!$(this)[0].checked) {

                                $(this).attr("disabled", "disabled");

                            }

                        });

                    }

                } else {

                    --num1;

                    if(num1 <= 4) {

                        $("input[name='choice1[]']").each(function(){

                            if(!$(this)[0].checked) {

                                $(this).removeAttr("disabled");

                            }

                        });

                    }

                }

            });

        });

    })



    $('.fbut1').click(function(){

        var formData = $("#form1").serialize();

        $.ajax({

            type: 'post',

            url: "<?php echo url('Index/student_vote'); ?>",

            data: formData,

            success: function(result) {

                var msg=jQuery.parseJSON(result);

                if(msg.code == 0){

                    alert(msg.message);

                }else if(msg.code == 1){

                    alert(msg.message);

                    window.location.href="<?php echo Url('Home/Index/vote_result'); ?>";

                }

                

            }

        });

    });

// onload是等所有的资源文件加载完毕以后再绑定事件
window.onload = function(){
	// 获取图片列表，即img标签列表
	var imgs = document.querySelectorAll('.teacherpic');

	// 获取到浏览器顶部的距离
	function getTop(e){
		return e.offsetTop;
	}

	// 懒加载实现
	function lazyload(imgs){
		// 可视区域高度
		var h = window.innerHeight;
		//滚动区域高度
		var s = document.documentElement.scrollTop || document.body.scrollTop;
		for(var i=0;i<imgs.length;i++){
			//图片距离顶部的距离大于可视区域和滚动区域之和时懒加载
			if ((h+s)>getTop(imgs[i])) {
				// 真实情况是页面开始有2秒空白，所以使用setTimeout定时2s
				(function(i){
					setTimeout(function(){
						// 不加立即执行函数i会等于9
						// 隐形加载图片或其他资源，
						//创建一个临时图片，这个图片在内存中不会到页面上去。实现隐形加载
						var temp = new Image();
						temp.src = imgs[i].getAttribute('data-src');//只会请求一次
						// onload判断图片加载完毕，真是图片加载完毕，再赋值给dom节点
						temp.onload = function(){
							// 获取自定义属性data-src，用真图片替换假图片
							imgs[i].src = imgs[i].getAttribute('data-src')
						}
					},2000)
				})(i)
			}
		}
	}
	lazyload(imgs);

	// 滚屏函数
	window.onscroll =function(){
		lazyload(imgs);
	}
}



   



    </script>

    



</DIV>



</body>

</html>