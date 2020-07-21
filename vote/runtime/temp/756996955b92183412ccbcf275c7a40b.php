<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:84:"/www/web/toupiao_com/public_html/public/../application/admin/view/teacher/index.html";i:1592357260;}*/ ?>
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
	<style>
		#teacher_img{
			height: 35px;
		}
	</style>
<div class="layui-card layadmin-header">
	<div class="layui-breadcrumb" lay-filter="breadcrumb"> <a><cite></cite></a> </div>
</div>
<div class="layui-fluid layui-anim layui-anim-fadein">
	<div class="layui-row layui-col-space15">
		<div class="layui-col-md12">
			<div class="layui-card">
				<div class="layui-card-body">
					<form action="javascript:;" onSubmit="return search()">
						<div class="test-table-reload-btn layui-form" id="search-box">
							<div class="layui-inline" style="margin-bottom: 10px;">
								<input class="layui-input search-where" name="name" placeholder="姓名" id="name" autocomplete="off">
							</div>

							<button class="layui-btn" style="margin-bottom: 10px;" onClick="search()" data-type="reload">搜索</button>
						</div>
					</form>
					<table class="layui-hide" id="data-list" lay-filter="data-list">
					</table>
					<script type="text/html" id="data-list-toolbar">
						<div class="layui-btn-container">
							<button class="layui-btn layui-btn-sm" bindJump="<?php echo Url('Teacher/add'); ?>" style="background:#009688" lay-event="add">添加</button>
							<button class="layui-btn layui-btn-sm" bindJump="<?php echo Url('Teacher/del'); ?>" style="background:#ff0000" lay-event="del">删除</button>
							<button class="layui-btn layui-btn-sm" bindJump="<?php echo Url('Teacher/edit'); ?>" style="background:#1e9fff" lay-event="edit">编辑</button>
						</div>
					</script>
					<script type="text/html" id="imgTpl">
						{{#
						if (d.img_thumb!==''){
						d.img_thumb = '<img src='+d.img_thumb+' id="teacher_img">';
						}else{
						d.img_thumb = '<img src=>';
						}
						}}
						{{d.img_thumb}}
					</script>
					<script type="text/html" id="statusTpl">
						{{#
						if (d.type==0){
						d.type = '<span style="color:red">否</span>';
						}else if (d.type==1){
						d.type = '<span style="color:green">是</span>';
						}
						}}
						{{d.type}}
					</script>
					
					
					
				</div>
			</div>
		</div>
	</div>
</div>
<script src="/static/layuiadmin/layui/layui.js?v=<?php echo $js_debug; ?>"></script>
<script src="/static/layuiadmin/plus.js?v=<?php echo $js_debug; ?>"></script>
<script>
	//搜索
	function search(){
		dataSearch({
			name:$("#name").val(),
		});
	}
	$run(function(){
		var url = "<?php echo Url('Teacher/index',['act' => 'getData']); ?>";
		var fd = [
			{type:'checkbox'},
			{field:'id', title: 'ID', width:50,sort:true},
			{field:'name', title: '姓名', minWidth:80,sort:true},
			{field:'img_thumb', title: '图片', width:150,templet:"#imgTpl"},
			{field:'description', title: '介绍', minWidth:180,sort:true},
			{field:'count_vote', title: '票数', width:180,sort:true},
			{field:'create_time', title: '创建时间', width:150,sort:true},
			{field:'sort', title: '排序', width:80,sort:true},
			{field:'type', title: '是否法学老师',width:50, sort:true,templet:"#statusTpl"}
		];
		loadData(url,fd);
		//头工具栏事件
		table.on('toolbar(data-list)', function(obj){
			var checkStatus = table.checkStatus(obj.config.id);
			var jump = $(this).attr("bindJump");
			switch(obj.event){
				case 'add':
					openFrame('添加账号',jump,['680px','530px']);
					break;
				case 'edit':
					if(checkStatus.data.length==0){
						layer.msg('请选择一条记录');
					}else if(checkStatus.data.length>1){
						layer.msg('只能勾选一条记录');
					}else if(checkStatus.data.length==1){
						var data = checkStatus.data;
						openFrame('编辑帐号',jump+'?id='+data[0].id,['680px','550px']);
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