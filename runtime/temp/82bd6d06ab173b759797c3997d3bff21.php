<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:82:"/www/web/toupiao_com/public_html/public/../application/admin/view/admin/index.html";i:1591263198;}*/ ?>
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
	<div class="layui-breadcrumb" lay-filter="breadcrumb"> <a><cite></cite></a> </div>
</div>
<div class="layui-fluid layui-anim layui-anim-fadein">
	<div class="layui-row layui-col-space15">
		<div class="layui-col-md12">
			<div class="layui-card">
				<div class="layui-card-body">
					<form action="javascript:;" onsubmit="return search()">
						<div class="test-table-reload-btn layui-form" id="search-box">
							<div class="layui-inline" style="margin-bottom: 10px;">
								<input class="layui-input search-where" name="account" placeholder="登录帐号" id="account" autocomplete="off">
							</div>
							<div class="layui-inline" style="margin-bottom: 10px;">
								<input class="layui-input search-where" name="name" placeholder="姓名" id="name" autocomplete="off">
							</div>

							<button class="layui-btn" style="margin-bottom: 10px;" onclick="search()" data-type="reload">搜索</button>
						</div>
					</form>
					<table class="layui-hide" id="data-list" lay-filter="data-list">
					</table>
					<script type="text/html" id="data-list-toolbar">
						<div class="layui-btn-container">
							<button class="layui-btn layui-btn-sm" bindJump="<?php echo Url('Admin/add'); ?>" style="background:#009688" lay-event="add">添加</button>
							<button class="layui-btn layui-btn-sm" bindJump="<?php echo Url('Admin/del'); ?>" style="background:#ff0000" lay-event="del">删除</button>
							<button class="layui-btn layui-btn-sm" bindJump="<?php echo Url('Admin/edit'); ?>" style="background:#1e9fff" lay-event="edit">编辑</button>
						</div>
					</script>
					<script type="text/html" id="statusTpl">
						{{#
						if (d.status==-1){
						d.status = '<span style="color:red">禁用</span>';
						}else if (d.status==1){
						d.status = '<span style="color:green">正常</span>';
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
	//搜索
	function search(){
		dataSearch({
			account:$("#account").val(),
			name:$("#name").val(),
		});
	}
	$run(function(){
		var url = "<?php echo Url('Admin/index',['act' => 'getData']); ?>";
		var fd = [
			{type:'checkbox'},
			{field:'id', title: 'ID', width:50,sort:true},
			{field:'admin_login', title: '登录帐号', minWidth:150,sort:true},
			{field:'admin_name', title: '姓名', minWidth:80,sort:true},
			{field:'role_name', title: '所属角色', minWidth:120,sort:true},
			{field:'last_login_ip', title: '登陆IP', minWidth:80,sort:true},
			{field:'create_time', title: '创建时间', width:150,sort:true},
			{field:'last_login_time', title: '最后登录时间', minWidth:150,sort:true},
			{field:'status', title: '状态',width:60, sort:true,templet:"#statusTpl"}
		];
		loadData(url,fd);
		//头工具栏事件
		table.on('toolbar(data-list)', function(obj){
			var checkStatus = table.checkStatus(obj.config.id);
			var jump = $(this).attr("bindJump");
			switch(obj.event){
				case 'add':
					openFrame('添加账号',jump,['600px','390px']);
					break;
				case 'edit':
					if(checkStatus.data.length==0){
						layer.msg('请选择一条记录');
					}else if(checkStatus.data.length>1){
						layer.msg('只能勾选一条记录');
					}else if(checkStatus.data.length==1){
						var data = checkStatus.data;
						openFrame('编辑帐号',jump+'?id='+data[0].id,['600px','350px']);
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