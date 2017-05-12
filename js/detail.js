<script type="text/javascript" src="<?php echo url('js/tiny_mce/jquery.tinymce.js');?>"></script>
<!--  <script type="text/javascript">
	$(document).ready(function() {
		$('textarea.addContent').tinymce({
			// Location of TinyMCE script
			script_url : '<?php echo url('js/tiny_mce/tiny_mce.js');?>',
			// General options
			theme : "advanced",
			// Theme options
			theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,separator,help",
			theme_advanced_buttons2 : "",
			theme_advanced_buttons3 : "",
			theme_advanced_buttons4 : "",
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			theme_advanced_statusbar_location : "bottom",
			theme_advanced_resizing : true,
			// Example content CSS (should be your site CSS)
			content_css : "css/content.css",
			// Drop lists for link/image/media/template dialogs
			template_external_list_url : "lists/template_list.js",
			external_link_list_url : "lists/link_list.js",
			external_image_list_url : "lists/image_list.js",
			media_external_list_url : "lists/media_list.js"
			// Replace values for the template plugin
		});
	});
</script>-->
<script type="text/javascript" charset="utf-8">
	$(document).ready(function(){
		$('#link_references').click(function() {
			if($(this).hasClass('on')){
				$(this).removeClass('on');
				$(this).addClass('off');
			}else if($(this).hasClass('off')){
				$(this).removeClass('off');
				$(this).addClass('on');
			}
			  $('#references').animate({
			    height: 'toggle'
			  }, 500, function() {
			    // Animation complete.
			  });
			});
		$('#link_references').click();
	});
</script>