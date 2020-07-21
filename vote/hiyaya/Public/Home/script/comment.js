// star choose
jQuery.fn.rater	= function(options) {

	// 默认参数
	var settings = {
		enabled	: true,
		url		: '',
		method	: 'post',
		min		: 1,
		max		: 5,
		step	: 1,
		value	: null,
		after_click	: null,
		before_ajax	: null,
		after_ajax	: null,
		title_format	: null,
		info_format	: null,
		image	: '/Public/Home/images/stars.png',
		imageAll :'/Public/Home/images/stars-all.png',
		defaultTips :true,
		clickTips :true,
		width	: 50,
		height	: 50
	};

	// 自定义参数
	if(options) {
		jQuery.extend(settings, options);
	}

	//外容器
	var container	= jQuery(this);

	// 主容器
	var content	= jQuery('<ul class="rater-star"></ul>');
	content.css('background-image' , 'url(' + settings.image + ')');
	content.css('height' , settings.height);
	content.css('width' , (settings.width*settings.step) * (settings.max-settings.min+settings.step)/settings.step);
	//显示结果区域
	var result= jQuery('<div class="rater-star-result"></div>');
	container.after(result);
	//显示点击提示
	// 当前选中的
	var item	= jQuery('<li class="rater-star-item-current"></li>');
	item.css('background-image' , 'url(' + settings.image + ')');
	item.css('height' , settings.height);
	item.css('width' , 0);
	item.css('z-index' , settings.max / settings.step + 1);
	if (settings.value) {
		item.css('width' , ((settings.value-settings.min)/settings.step+1)*settings.step*settings.width);
	};
	
	content.append(item);
	result.html('非常满意');

	// 星星
	for (var value=settings.min ; value<=settings.max ; value+=settings.step) {
		item	= jQuery('<li class="rater-star-item"></li>');

		item.css('height' , settings.height);
		item.css('width' , (value-settings.min+settings.step)*settings.width);
		item.css('z-index' , (settings.max - value) / settings.step + 1);
		item.css('background-image' , 'url(' + settings.image + ')');

		if (!settings.enabled) {	// 若是不能更改，则隐藏
			item.hide();
		}

		content.append(item);
		result.html('');
	}

	content.mouseover(function(){
		if (settings.enabled) {
			jQuery(this).find('.rater-star-item-current').hide();
		}
	}).mouseout(function(){
			jQuery(this).find('.rater-star-item-current').show();
	})
	// 添加鼠标悬停/点击事件
	var shappyWidth=(settings.max-2)*settings.width;
	var happyWidth=(settings.max-1)*settings.width;
	var fullWidth=settings.max*settings.width;
	content.find('.rater-star-item').mouseover(function() {
		jQuery(this).prevAll('.rater-star-item-tips').hide();
		jQuery(this).attr('class' , 'rater-star-item-hover');
		jQuery(this).find(".popinfo").show();

		//当3分时用笑脸表示
		if(parseInt(jQuery(this).css("width"))==shappyWidth){
			jQuery(this).addClass('rater-star-happy');
		}
		//当4分时用笑脸表示
		if(parseInt(jQuery(this).css("width"))==happyWidth){
			jQuery(this).addClass('rater-star-happy');
		}
		//当5分时用笑脸表示
		if(parseInt(jQuery(this).css("width"))==fullWidth){
			jQuery(this).removeClass('rater-star-item-hover');
			jQuery(this).css('background-image' , 'url(' + settings.imageAll + ')');
			jQuery(this).css({cursor:'pointer',position:'absolute',left:'0',top:'0'});
		}
	}).mouseout(function() {
		var outObj=jQuery(this);
		outObj.css('background-image' , 'url(' + settings.image + ')');
		outObj.attr('class' , 'rater-star-item');
		outObj.find(".popinfo").hide();
		outObj.removeClass('rater-star-happy');
		// jQuery(this).prevAll('.rater-star-item-tips').show();
		//var startTip=function () {
		//outObj.prevAll('.rater-star-item-tips').show();
		//};
		//startTip();
	}).click(function() {
		//jQuery(this).prevAll('.rater-star-item-tips').css('display','none');
		// jQuery(this).parents(".rater-star").find(".rater-star-item-tips").remove();
		jQuery(this).parents(".goods-comm-stars").find(".rater-click-tips").remove();
		jQuery(this).prevAll('.rater-star-item-current').css('width' , jQuery(this).width());
		   if(parseInt(jQuery(this).prevAll('.rater-star-item-current').css("width"))==happyWidth||parseInt(jQuery(this).prevAll('.rater-star-item-current').css("width"))==shappyWidth){
			jQuery(this).prevAll('.rater-star-item-current').addClass('rater-star-happy');
			}
		else{
			jQuery(this).prevAll('.rater-star-item-current').removeClass('rater-star-happy');
			}
			if(parseInt(jQuery(this).prevAll('.rater-star-item-current').css("width"))==fullWidth){
			jQuery(this).prevAll('.rater-star-item-current').addClass('rater-star-full');
			}
		else{
			jQuery(this).prevAll('.rater-star-item-current').removeClass('rater-star-full');
			}
		var star_count		= (settings.max - settings.min) + settings.step;
		var current_number	= jQuery(this).prevAll('.rater-star-item').size()+1;
		var current_value	= settings.min + (current_number - 1) * settings.step;

		//显示当前分值
		if (typeof settings.title_format == 'function') {
			if(current_value==1) {
				jQuery(this).parents().nextAll('.rater-star-result').html('非常差');
				$('.tagbox .tagItem').addClass('hide').eq(0).removeClass('hide');
				$('#score').val(1);
				$('#lable_list').val('');
				arr_list=[];
			} else if(current_value==2) {
				jQuery(this).parents().nextAll('.rater-star-result').html('差');
				$('.tagbox .tagItem').addClass('hide').eq(1).removeClass('hide');
				$('#score').val(2);
				$('#lable_list').val('');
				arr_list=[];
			} else if(current_value==3) {
				jQuery(this).parents().nextAll('.rater-star-result').html('一般');
				$('.tagbox .tagItem').addClass('hide').eq(2).removeClass('hide');
				$('#score').val(3);
				$('#lable_list').val('');
				arr_list=[];
			} else if(current_value==4) {
				jQuery(this).parents().nextAll('.rater-star-result').html('满意');
				$('.tagbox .tagItem').addClass('hide').eq(3).removeClass('hide');
				$('#score').val(4);
				$('#lable_list').val('');
				arr_list=[];
			} else if(current_value==5) {
				jQuery(this).parents().nextAll('.rater-star-result').html('非常满意');
				$('.tagbox .tagItem').addClass('hide').eq(4).removeClass('hide');
				$('#score').val(5);
				$('#lable_list').val('');
				arr_list=[];
			}
			$('.tagbox .tagItem').find('.tag').removeClass('active');
		}
		$("#StarNum").val(current_value);
		//jQuery(this).parents().next('.rater-star-result').html(current_value);
		//jQuery(this).unbind('mouseout',startTip)
	})

	jQuery(this).html(content);

}

// 星星打分
$(function(){
	var options	= {
	max	: 5,
	title_format: function(value) {}
	}
	$('#rate-comm-1').rater(options);
});
