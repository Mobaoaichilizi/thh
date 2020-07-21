<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:83:"/www/web/toupiao_com/public_html/public/../application/admin/view/teacher/edit.html";i:1592977087;}*/ ?>


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

      <form class="layui-form layui-form-pane" action="" onSubmit="return false;" lay-filter="component-form-element">

        <input type="hidden" name="id" value="<?php echo $result['id']; ?>">

      <div class="layui-row layui-col-space10 layui-form-item">

		    <div class="layui-col-lg12">

              <label class="layui-form-label">教师姓名：</label>

              <div class="layui-input-block">

                  <input type="text" name="name" lay-verify="required" value="<?php echo $result['name']; ?>"  placeholder="请输入教师姓名"

                          autocomplete="off" class="layui-input">

              </div>

           </div>

          <div class="layui-col-lg12">

            <label class="layui-form-label">图片：</label>

            <div class="layui-input-block">

                <input type="text" name="img_thumb" id="img_thumb" <?php if($result['img_thumb'] == ''): ?> onclick="uploadFileFrame('img_thumb')" <?php endif; ?> lay-verify="required" placeholder="请点击上传"   autocomplete="off" class="layui-input uploadFile" value="<?php echo $result['img_thumb']; ?>">

          

          </div>

          </div>

		  

		   <div class="layui-col-lg12">

            <label class="layui-form-label">介绍：</label>

            <div class="layui-input-block">

              <input type="text" name="description" lay-verify="required" placeholder="请输入介绍" autocomplete="off" class="layui-input" value="<?php echo $result['description']; ?>">

            </div>

          </div>
          <!-- <div class="layui-col-lg12">

            <label class="layui-form-label">详情：</label>

            <div class="layui-input-block">
              
              <textarea style="width:500px;height:200px;padding:5px;" name="content" id="demo" lay-verify="content" class="field-content"><?php echo $result['content']; ?></textarea>
            
            </div>

          </div> -->
         

        <div class="layui-col-lg12">

          <label class="layui-form-label">是否法学老师：</label>

          <div class="layui-input-block">

            <select name="type" lay-verify="required">

              <option value="1"  <?php if($result['type'] == '1'): ?>selected<?php endif; ?>>是</option>

              <option value="0"  <?php if($result['type'] == '0'): ?>selected<?php endif; ?>>否</option>

          </select>

          </div>

        </div>

        <div class="layui-col-lg12">

          <label class="layui-form-label">排序：</label>

          <div class="layui-input-block">

            <input type="number" name="sort" lay-verify="required|number" placeholder="请输入排序" autocomplete="off" class="layui-input" value="<?php echo $result['sort']; ?>">

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

<script src="/static/layuiadmin/jquery-3.4.1.min.js"></script>  
<script>

$run(function(){
  
  frameSubmit("<?php echo Url('Teacher/edit'); ?>",true);
  
});

$("#img_thumb").bind("input propertychange", function () {
      
  var img_thumb = $("#img_thumb").val(); 
        
  if(img_thumb == ''){
          
    uploadFileFrame('img_thumb');
        
  }
      
  });
  // layui.use('layedit', function(){
  //   var layedit = layui.layedit;
  //   layedit.build('demo');
  // });

</script>

</body>

</html>

