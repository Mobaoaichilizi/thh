<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:83:"/www/web/toupiao_com/public_html/public/../application/admin/view/config/index.html";i:1591587212;}*/ ?>

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
<div class="layui-card">
    <div class="layui-card-body">
        <form class="layui-form layui-form-pane" action="#" onSubmit="return false" lay-filter="component-form-element">
            
            <input type="hidden" name="id" value="<?php echo $result['id']; ?>">
            <div class="layui-row layui-col-space10 layui-form-item">

                <div class="layui-col-lg12">
                    <label class="layui-form-label">评选标题：</label>
                    <div class="layui-input-block">
                        <input type="text" name="web_title" maxlength="18" lay-verify="required" placeholder="请输入系统标题" autocomplete="off" class="layui-input" value="<?php echo $result['web_title']; ?>">
                    </div>
                </div>

                <div class="layui-col-lg12">
                    <label class="layui-form-label">评选介绍：</label>
                    <div class="layui-input-block">
                        <input type="text" name="web_description" lay-verify="required" placeholder="请输入系统介绍" autocomplete="off" class="layui-input" value="<?php echo $result['web_description']; ?>">
                    </div>
                </div>
                <div class="layui-col-lg12">
                    <label class="layui-form-label">活动开始时间：</label>
                    <div class="layui-input-block">
                        <input type="text" name="web_start_time" lay-verify="required" placeholder="请输入活动开始时间" autocomplete="off" class="layui-input" value="<?php echo $result['web_start_time']; ?>">
                    </div>
                </div> 
                <div class="layui-col-lg12">
                    <label class="layui-form-label">活动结束时间：</label>
                    <div class="layui-input-block">
                        <input type="text" name="web_end_time" lay-verify="required" placeholder="请输入活动结束时间" autocomplete="off" class="layui-input" value="<?php echo $result['web_end_time']; ?>">
                    </div>
                </div> 
                <div class="layui-col-lg12">
                    <label class="layui-form-label">系统版权：</label>
                    <div class="layui-input-block">
                        <input type="text" name="web_copyright" lay-verify="required" placeholder="请输入系统版权" autocomplete="off" class="layui-input"  value="<?php echo $result['web_copyright']; ?>">
                    </div>
                </div>

                <div class="layui-col-lg12">
                    <label class="layui-form-label">评选联系方式：</label>
                    <div class="layui-input-block">
                        <input type="text" name="web_phone" lay-verify="required" placeholder="请输入作者联系方式" autocomplete="off" class="layui-input" value="<?php echo $result['web_phone']; ?>">
                    </div>
                </div>
                <div class="layui-col-lg12">
                    <label class="layui-form-label">系统版本：</label>
                    <div class="layui-input-block">
                        <input type="text" name="web_version" lay-verify="required" placeholder="请输入系统版本" autocomplete="off" class="layui-input" value="<?php echo $result['web_version']; ?>">
                    </div>
                </div> 


            </div>
            <div class="layui-form-item">
                <div class="layui-input-block">
                    <button class="layui-btn" lay-submit lay-filter="component-form-element">提交</button>
                    <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                </div>
            </div>
        </form>
    </div>
</div>
<script src="/static/layuiadmin/layui/layui.js?v=<?php echo $js_debug; ?>"></script> 
<script src="/static/layuiadmin/plus.js?v=<?php echo $js_debug; ?>"></script>
<script>
    $run(function(){
        form.render(null, 'component-form-element');
        element.render('breadcrumb', 'breadcrumb');
        form.on('submit(component-form-element)', function(data){
            var btn = $(this);
            if (btn.hasClass("layui-btn-disabled")) {
                layer.msg('数据传输中，请勿重复提交...', {
                    shade : [ 0.1, '#393D49' ],
                    time : 2000
                });
                return false;
            }
            btn.addClass('layui-btn-disabled');
            var loading = layer.load(2, {
                shade : [ 0.1, '#393D49' ],
            });
            layer.msg('正在提交数据...', {
                shade : [ 0.1, '#393D49' ],
                time : 2000
            });
            post("<?php echo Url('Config/index'); ?>", data.field, function(ret){
                var code = ret.code;
                layer.close(loading);
                setTimeout(function(){
                    btn.removeClass('layui-btn-disabled');
                }, 1000);
                if(code==1){
                    setTimeout(function() {
                        parent.layer.close(index);
                    }, 1000);
                    layer.msg(ret.msg, {
                        shade : [ 0.1, '#393D49' ],
                        time : 2000,
                        icon : 6
                    });
                }else{
                    layer.msg(ret.msg, {
                        shade : [ 0.1, '#393D49' ],
                        time : 2000,
                        icon : 5
                    });
                }
            });
            return false;
        });
    });
</script>
</body>
</html>
