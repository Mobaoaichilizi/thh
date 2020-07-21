<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:81:"/www/web/toupiao_com/public_html/public/../application/admin/view/menu/index.html";i:1591263202;}*/ ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title></title>
<meta name="renderer" content="webkit">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="author" content="415199201@qq.com">
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
<link rel="stylesheet" href="/static/layuiadmin/layui/css/layui.css?v=<?php echo $js_debug; ?>" media="all">
<link rel="stylesheet" href="/static/layuiadmin/style/admin.css?v=<?php echo $js_debug; ?>" media="all">
</head>
<body>
	<div class="layui-card layadmin-header">
		<div class="layui-breadcrumb" lay-filter="breadcrumb">
			<a><cite>信息展示</cite></a>
		</div>
	</div>
	<div class="layui-fluid layui-anim layui-anim-fadein">
		<div class="layui-row layui-col-space15">
			<div class="layui-col-md12">
				<div class="layui-card">
					<div class="layui-card-body">
						
						<table class="layui-hide" id="data-list" lay-filter="data-list">
						</table>
						<script type="text/html" id="data-list-toolbar">
                            <div class="layui-btn-container">
							<button class="layui-btn layui-btn-sm" bindJump="<?php echo Url('Menu/addMenu'); ?>" style="background:#009688" lay-event="add">添加</button>
							<button class="layui-btn layui-btn-sm" bindJump="<?php echo Url('Menu/delMenu'); ?>" style="background:#ff0000" lay-event="del">删除</button>
							<button class="layui-btn layui-btn-sm" bindJump="<?php echo Url('Menu/editMenu'); ?>" style="background:#1e9fff" lay-event="edit">编辑</button>
							</div>
                        </script>
            			<script type="text/html" id="statusTpl">
				        {{#
                            if (d.status==0){
                                d.status = '<span style="color:red">不显示</span>';
                            }else if (d.status==1){
                                d.status = '<span style="color:green">显示</span>';
                            }
                        }}
                        {{d.status}}
                        </script>
					</div>
				</div>
			</div>
		</div>
	</div>
<script src="/static/layuiadmin/layui/layui.js?v=<?php echo $js_debug; ?>"></script> 
<script src="/static/layuiadmin/plus.js?v=<?php echo $js_debug; ?>"></script> 
	<script>
    $run(function(){
        var url = "<?php echo Url('Menu/index',array('act' => 'getData')); ?>";
        var fd = [
            {type:'checkbox'}
            ,{field:'id', title: 'ID', width:100}
            ,{field:'name', title: '菜单名称', minWidth:100}
            ,{field:'ico', title: '图标', minWidth: 150}
            ,{field:'app', title: '应用地址',width:250}
            ,{field:'listorder', title: '排序',width:100}
	        ,{field:'status', title: '状态',width:65, sort:true,templet:"#statusTpl"}
          ];
        loadData(url,fd,false);
      
        //头工具栏事件
        table.on('toolbar(data-list)', function(obj){
          var checkStatus = table.checkStatus(obj.config.id);
          var jump = $(this).attr("bindJump");
          switch(obj.event){
            case 'add':
             	openFrame('<b>添加菜单</b>',jump,['700px','530px']);
            break;
            case 'edit':
              if(checkStatus.data.length==0){
            	  layer.msg('请选择一条记录');
              }else if(checkStatus.data.length>1){
            	  layer.msg('只能勾选一条记录');
              }else if(checkStatus.data.length==1){
            	 var data = checkStatus.data;
            	 openFrame('<b>编辑菜单</b>',jump+'?id='+data[0].id,['700px','530px']);
              }
              break;
            case 'del':
            	if(checkStatus.data.length==0){
              	  layer.msg('请至少选择一记录');
                }else if(checkStatus.data.length>1){
              	  layer.msg('只能选择一条记录');
                }else{
              		layer.confirm('确定要删除吗？', function(index){
              			var data = checkStatus.data;
              			var url = jump;
              			post(url,{'id':data[0].id},function(ret){
              				var code = ret.code;
                         	 if(code==1){
                         		layer.msg(ret.msg, {
            						icon : 6,
            						shade : [ 0.1, '#393D49' ],
            						time : 2000
            					});
                         		table.reload('data-list');
                             } else{
                            	layer.msg(ret.msg,{icon: 5});
    	                     }
    	          		});
                	});
                }
            break;
          };
        });
     });
</script>
</body>
</html>