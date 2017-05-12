<! DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" >
<html xmlns="http://www.w3.org/1999/xhtml">
<?php $this ->render( 'before_body.tpl'); ?>
<body class="syn-asfe-body">

<?php $this ->render('header.tpl');?>
<!-- content in -->

<div id="content"><!--main content -->
<div id="maincontent">

<div id="main-body">

<div id="main-content">

<?php $this->render('stock/left-pane.tpl');?>
<div class="right-main">

<div class="main-head">
	<h1><?php echo $stock_title;?></h1>
</div>

<div id="main-body">
    <?php $this->render('stock/'.$templatefile);?>
</div>

</div>

</div>
</div>
</div>
<!--end main content --></div>
<!-- end content -->
<!-- content out -->
<!-- end wrapper -->
<!-- footer in -->
<script type="text/javascript">
(function( $ ) {
	  $.fn.niceFileField = function() {
	    this.each(function(index, file_field) {
	      file_field = $(file_field);
	      var label = file_field.attr("data-label") || "Choose File";

	      file_field.css({"display": "none"});
	      file_field.after("<div class=\"nice_file_field input-append\"><input class=\"input fileinput\" type=\"text\"><a class=\"ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only\">" + label + "</a></div>");

	      var nice_file_field = file_field.next(".nice_file_field");
	      nice_file_field.find("a").click( function(){ file_field.click() } );
	      file_field.change( function(){
	        nice_file_field.find("input").val(file_field.val());
	      });
	    });
	  };
	})( jQuery );


$( 'button, input[type="submit"], div.button' ).button();
$(".nice_file_field").niceFileField();
$(".nice_file_field a").button();

$('div.button.exportStock').click(function(){
		window.open(url('stock/generatecsvfile'), "_blank");
	});

/*
$("form#upload_data").submit(function(){
    var formData = new FormData($(this)[0]);
    $.ajax({
        url: url('stock/uploadstockfile'),
        type: 'POST',
        data: formData,
        dataType: 'json',
        async: false,
        success: function (data) {
        	$('div.return-message').html('<p>更新了'+data['update_count']+'个库存。</p>');
        },
        cache: false,
        contentType: false,
        processData: false
    });

    return false;
});
*/


</script>

<?php $this ->render( 'footer.tpl'); ?>
<!-- footer out -->
</body>
</html>