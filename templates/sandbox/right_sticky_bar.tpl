  
<div class="feedback-container"">
<div id = "feedback-helpful" style="position:fixed; top: 300px; display:none;">
<a href="" title="like" style="border:none;background-image:url(images/common/like-unlike-v.png);background-repeat:no-scroll 0 0 transparent;display:block;height:100px;width:40px;"></a>
</div>
<div id = "feedback-unhelpful" style="position:fixed; top: 410px; display:none;">
<a href="" title="dislike" style="border:none;background-image:url(images/common/like-unlike-v.png);background-position: -43px 0; background-repeat:no-scroll 0 0 transparent;display:block;height:100px;width:40px;"></a>
</div>
</div>

<script type="text/javascript"'>
	$(document).ready(function(){
			attachCss($('#feedback-helpful, #feedback-unhelpful'));
			$('#feedback-helpful').css({'display':'block'});
			$('#feedback-unhelpful').css({'display':'block'});
		}
	);
	/*Attach a scroll div to the right*/
	function attachCss(scrollDiv){
	var pageWidth = 960;
	var tagWidth = 40;

		if(screen.width>=pageWidth + tagWidth){
			if($.browser.msie&&$.browser.version<=6){
				b={right:"-"+ tagWidth + "px"}
			}else{
				b={right:(document.documentElement.clientWidth-pageWidth)/2-tagWidth+"px"};
			}
		}else{
			b={right:"-"+ tagWidth + "px"}
		}
		scrollDiv.css(b);
	}
</script>