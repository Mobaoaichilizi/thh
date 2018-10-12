// JavaScript Document


	var _default = 3; //默认显示图片个数
	var _loading = 1; //每次点击按钮后加载的个数
	var _content = [];//用来存储li循环内容
	function init(){
		var lis = $(".lanren .hidden li");
		$(".lanren ul.list").html("");
		for(var n=0;n<_default;n++){
			lis.eq(n).appendTo(".lanren ul.list");
		}
		$(".lanren ul.list img").each(function(){
			$(this).attr('src',$(this).attr('realSrc'));
		})
		for(var i=_default;i<lis.length;i++){
			_content.push(lis.eq(i));
		}
		$(".lanren .hidden").html("");
	}
	init();
	function loadMore(){
		var k =0,t,i;
		var mLis = $(".lanren ul.list li").length;
		for(i= mLis-_default;i<mLis-_loading;i++){
			if(i == _content.length){
				$('.lanren .more').html("<p>全部加载完毕...</p>");
				break;
			}
			_content[i].appendTo(".lanren ul.list");
			t = mLis + k;
			k++;
			$(".lanren ul.list img").eq(t).each(function(){
				$(this).attr('src',$(this).attr('realSrc'));
			});
		}
	}
