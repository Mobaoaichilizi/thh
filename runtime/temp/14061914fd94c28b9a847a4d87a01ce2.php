<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:82:"/www/web/toupiao_com/public_html/public/../application/admin/view/index/index.html";i:1591334126;s:71:"/www/web/toupiao_com/public_html/application/admin/view/index/menu.html";i:1591263200;}*/ ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>后台管理系统</title>
<meta name="renderer" content="webkit">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
<link rel="stylesheet" href="/static/layuiadmin/layui/css/layui.css?v=<?php echo $js_debug; ?>" media="all">
<link rel="stylesheet" href="/static/layuiadmin/style/admin.css?v=<?php echo $js_debug; ?>" media="all">
<style>
.scroll-wrapper {
	-webkit-overflow-scrolling: touch;
	overflow-y: auto;
}
.scroll-wrapper iframe {
	height: 102%;
}
/*禁止选中文字*/
   * {
	moz-user-select: -moz-none;
	-moz-user-select: none;
	-o-user-select:none;
	-khtml-user-select:none;
	-webkit-user-select:none;
	-ms-user-select:none;
	user-select:none;
}
</style>
</head>
<body class="layui-layout-body">
<div id="LAY_app" class="layui-anim layui-anim-fadein">
  <div class="layui-layout layui-layout-admin">
    <div class="layui-header"> 
      <!-- 头部区域 -->
      <ul class="layui-nav layui-layout-left">
        <li class="layui-nav-item layadmin-flexible" lay-unselect>
          <a href="javascript:;" layadmin-event="flexible" title="侧边伸缩">
            <i class="layui-icon layui-icon-shrink-right" id="LAY_app_flexible"></i>
          </a>
        </li>
      </ul>
      <ul class="layui-nav layui-layout-right" lay-filter="layadmin-layout-right">
        <li class="layui-nav-item" lay-unselect > <a href="javascript:;" id="refresh" layadmin-event="refresh" title="刷新"> <i class="layui-icon layui-icon-refresh-3"></i> </a> </li>
        <li class="layui-nav-item layui-hide-xs" lay-unselect> <a href="javascript:;" layadmin-event="theme"> <i class="layui-icon layui-icon-theme"></i> </a> </li>
        <li class="layui-nav-item layui-hide-xs" lay-unselect> <a href="javascript:;" layadmin-event="note"> <i class="layui-icon layui-icon-note"></i> </a> </li>
        <li class="layui-nav-item layui-hide-xs" lay-unselect> <a href="javascript:;" layadmin-event="fullscreen"> <i class="layui-icon layui-icon-screen-full"></i> </a> </li>
        <li class="layui-nav-item" lay-unselect style="margin-right: 10px;"> <a href="javascript:;"> <cite>您好：<?php echo $admin_user['admin_login']; ?> </cite> </a>
          <dl class="layui-nav-child">
		  	<dd style="text-align: center;"><a href="javascript:;" onClick="clearCache()">清除缓存</a></dd>
            <dd style="text-align: center;"><a lay-href="<?php echo Url('Index/editPwd'); ?>">修改密码</a></dd>
            <dd style="text-align: center;"><a href="javascript:;" onClick="logout()">退出登录</a></dd>
          </dl>
        </li>
       
      </ul>
    </div>
    <!-- 侧边菜单 -->
    <div class="layui-side layui-side-menu">
      <div class="layui-side-scroll">
        <div class="layui-logo" lay-href="<?php echo Url('Index/welcome'); ?>"> <i class="layui-icon layui-icon-console" style="font-size:20px;"></i> <span style="margin-left:2px;font-size:18px;">管理系统</span> </div>
        	<ul class="layui-nav layui-nav-tree" lay-shrink="all" id="LAY-system-side-menu" lay-filter="layadmin-system-side-menu">
           <?php if(is_array($menu_list) || $menu_list instanceof \think\Collection || $menu_list instanceof \think\Paginator): $k = 0; $__LIST__ = $menu_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($k % 2 );++$k;?>
            <li data-name="component" class="layui-nav-item">
              <a href="javascript:;" lay-tips="<?php echo $item['name']; ?>" lay-direction="2">
                <i class="layui-icon <?php echo $item['ico']; ?>"></i>
                <cite><?php echo $item['name']; ?></cite>
              </a>
                 <?php if(is_array($item['voo']) || $item['voo'] instanceof \think\Collection || $item['voo'] instanceof \think\Paginator): $i = 0; $__LIST__ = $item['voo'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$dd): $mod = ($i % 2 );++$i;?>
              <dl class="layui-nav-child">
                <dd data-name="grid">
                  <a data-id="<?php echo $dd['id']; ?>" <?php if($dd['ttt']){ ?> href="javascript:;" <?php }else{ ?> lay-href="<?php echo Url($dd['app'].'/'.$dd['model'].'/'.$dd['action']); ?>" <?php } ?>>
				  <i class="layui-icon <?php echo $dd['ico']; ?>"></i>
               	  <cite><?php echo $dd['name']; ?></cite>
				  </a>
                  <?php if($dd['ttt']){ ?>
                  <dl class="layui-nav-child">
                  <?php if(is_array($dd['ttt']) || $dd['ttt'] instanceof \think\Collection || $dd['ttt'] instanceof \think\Paginator): $i = 0; $__LIST__ = $dd['ttt'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$tt): $mod = ($i % 2 );++$i;?>        	
                	<dd><a lay-href="<?php echo Url($tt['app'].'/'.$tt['model'].'/'.$tt['action']); ?>">
						<i class="layui-icon <?php echo $tt['ico']; ?>"></i>
						<cite><?php echo $tt['name']; ?></cite>
					</a></dd>
                  <?php endforeach; endif; else: echo "" ;endif; ?>
                  </dl>
                   <?php } ?>
                </dd>

         
              </dl>
              
                 <?php endforeach; endif; else: echo "" ;endif; ?>
              
            </li>
              <?php endforeach; endif; else: echo "" ;endif; ?>
            
            
          <li data-name="get" class="layui-nav-item"> <a href="javascript:;" lay-href="<?php echo Url('Index/editPwd'); ?>" lay-tips="修改密码" lay-direction="2"> <i class="layui-icon layui-icon-util"></i> <cite>修改密码</cite> </a> </li>
          <li data-name="get" class="layui-nav-item"> <a href="javascript:;" onClick="logout()" lay-tips="注销登录" lay-direction="2"> <i class="layui-icon layui-icon-auz"></i> <cite>注销登录</cite> </a> </li>  
            
            
          </ul>
      </div>
    </div>
    <!-- 页面标签 -->
    <div class="layadmin-pagetabs" id="LAY_app_tabs">
      <div class="layui-icon layadmin-tabs-control layui-icon-prev" layadmin-event="leftPage"></div>
      <div class="layui-icon layadmin-tabs-control layui-icon-next" layadmin-event="rightPage"></div>
      <div class="layui-icon layadmin-tabs-control layui-icon-down">
        <ul class="layui-nav layadmin-tabs-select" lay-filter="layadmin-pagetabs-nav">
          <li class="layui-nav-item" lay-unselect> <a href="javascript:;"></a>
            <dl class="layui-nav-child layui-anim-fadein">
              <dd layadmin-event="refresh"><a href="javascript:;">刷新当前标签页</a></dd>
              <dd layadmin-event="closeThisTabs"><a href="javascript:;">关闭当前标签页</a></dd>
              <dd layadmin-event="closeOtherTabs"><a href="javascript:;">关闭其它标签页</a></dd>
              <dd layadmin-event="closeAllTabs"><a href="javascript:;">关闭全部标签页</a></dd>
            </dl>
          </li>
        </ul>
      </div>
      <div class="layui-tab" lay-unauto lay-allowClose="true" lay-filter="layadmin-layout-tabs">
        <ul class="layui-tab-title" id="LAY_app_tabsheader">
          <li lay-id="<?php echo Url('Index/welcome'); ?>" lay-attr="<?php echo Url('Index/welcome'); ?>" class="layui-this"><i class="layui-icon layui-icon-home"></i></li>
        </ul>
      </div>
    </div>
    <!-- 主体内容 -->
    <div class="layui-body" id="LAY_app_body">
      <div class="layadmin-tabsbody-item layui-show">
        <iframe src="<?php echo Url('Index/welcome'); ?>" frameborder="0" class="layadmin-iframe"></iframe>
      </div>
    </div>
    <!-- 辅助元素，一般用于移动设备下遮罩 -->
    <div class="layadmin-body-shade" layadmin-event="shade"></div>
  </div>
</div>
<script src="/static/layuiadmin/layui/layui.js?v=<?php echo $js_debug; ?>"></script> 
<script src="/static/layuiadmin/plus.js?v=<?php echo $js_debug; ?>"></script> 
<script>  
   function logout(){
	  layer.confirm('确定要退出吗?', function(index){
			var url = "<?php echo Url('Index/logout'); ?>";
			post(url,{},function(ret){
				var code = ret.code;
           	 if(code==1){
           		layer.msg(ret.msg, {icon: 6});
           		window.location.href="<?php echo Url('Admin/Login/index'); ?>";
               } else{
              	layer.msg(ret.msg,{icon: 5});
               }
    		});
  	});
  }
  function clearCache()
  {
		var url = "<?php echo Url('Index/clearCache'); ?>";
		post(url,{},function(ret){
			var code = ret.code;
		 if(code==1){
			layer.msg(ret.msg, {icon: 6});
		   } else{
			layer.msg(ret.msg,{icon: 5});
		   }
		});
  }
  $run(function(){
	  layer.load();
	  document.onkeydown = function (e) {
		   e = e || window.event;if ((e.ctrlKey && e.keyCode == 82) || 
		       e.keyCode == 116) {
		   	 $("#refresh").click();
			   return false;
		   }
	  }

	  setTimeout(function(){
		  $("#LAY_app").css("display","block");
		  layer.closeAll('loading');
	  },250);

	  $(".layui-side-menu a").dblclick(function(){
		  $("#refresh").click();
	  });
	  $(".top-nav-cus a").dblclick(function(){
		  $("#refresh").click();
	  });
	  $("#LAY_app_tabsheader").dblclick(function(){
		  $("#refresh").click();
		  return false;
	  });
	});
  </script>
</body>
</html>