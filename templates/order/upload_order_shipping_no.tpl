<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<?php $this->render('before_body.tpl');?>
<body class="syn-asfe-body">
<?php $this->render('header.tpl');?>
<!-- content in -->
<div id="content">
<!--main content -->
	<div id="maincontent">
		<div id="main-body">
			<div class="main-head">
				<table style="width:600px">
					<?php if(isset($message)):?>
					<tr><td><span class="<?php echo $message->type;?>"><?php echo $message->data;?></span></td></tr>
					<?php endif;?>
					<tr><td>运单号文件:<input id="file_input" type="file" name="sn_file" style="margin-left:10px;width:auto;" /></td></tr>
					<tr>
						<td>
							<button id="upload_btn" class="button">上传</button>
							<button id="pause_btn" class="button" style="padding:5px 14px 6px;margin-left:30px;">暂停</button>
						</td>
					</tr>
					<tr>
					   <td>上传日志：
					       <div id="log_container"></div>
					   </td>
					</tr>
				</table>
			</div>
		</div>
	</div>
<!--end main content -->
</div><!-- end content -->
<!-- content out -->
<!-- end wrapper -->
<!-- footer in -->
<?php $this->render('footer.tpl')?>
<script type="text/javascript">
$(function(){
	$('.button' ).button();

	var file = null;
	$('#file_input').change(function(){
		file = this.files[0];
	});

	var $orders = null, $orderItems = null, $currentIndex = 0, $ordersCount = 0, $finishedCount = 0;
	
	function upload_order_shipping_no() {
	    var $orderNumber = $orderItems[0];
		$.post(url('order/uploadordershippingno') + $orderNumber, 
				{'orderNumber': $orderNumber, 
			    'shippingFee': $orderItems[1],
			    'shippingNo': $orderItems[2],
			    'shippingMethod': $orderItems[3]}, null, 'json'
		).done(function(ret){
		    if (ret.success) {
		    	$('#log_container').append('<p class="success">' + $orderNumber + '上传成功</p>');
			} else {
				$('#log_container').append('<p class="fail">' + $orderNumber + '上传失败原因：' + ret.msg + '</p>');
			}
		    $finishedCount++;
			$('#log_container .info').text('开始上传请稍等...' + $finishedCount + '/' + $ordersCount);
			if ($finishedCount == $ordersCount) {
			    if ($('#log_container .fail').length > 1) {
			        $('#log_container').append('<p class="info">上传结束,请核对上传过程中出现的问题</p>');
			    } else {
			    	$('#log_container').append('<p class="info">上传结束</p>');
				}
			}
		});
		$currentIndex++;
		if ($currentIndex < $ordersCount) {
			$order = $.trim($orders[$currentIndex]);
			$orderItems = $order.split(/\s+/);
			if ($orderItems.length != 4) {
				$('#log_container').append('<p class="fail">' + $order + '格式错误</p>');
			} else {
			    setTimeout(upload_order_shipping_no, 500);
			}
		}
	}

	function checkFormat() {
	    var valid = true;
	    for (var $i = 0; $i < $ordersCount; $i++) {
	    	$order = $.trim($orders[$i]);
			$orderItems = $order.split(/\s+/);
			if ($orderItems.length != 4) {
				$('#log_container').append('<p class="fail">' + $order + '格式错误</p>');
				valid = false;
			}
		}
		return valid;
	}
	
	$('#upload_btn').click(function(){
	    if (file　=== null) {
	        alert('请输入运单号文件');
	        return false;
		}
	    var reader = new FileReader();
	    reader.onload = function(e) {
		    $orders = $.trim(reader.result).split(/[\n|\r\n]{1,}/);
		    $ordersCount = $orders.length;
		    $currentIndex = 0;
		    $finishedCount = 0;
            if (checkFormat()) {
    		    $order = $.trim($orders[$currentIndex]);
    			$orderItems = $order.split(/\s+/);
    			upload_order_shipping_no($orderItems);
            } else {
            	$('#log_container').append('<p class="info">上传失败，请检查提示用的错误，修正后，再次提交！</p>');
            }
		}
	    reader.readAsText(file, 'gbk');
	    $('#log_container').empty().append('<p class="info">开始上传请稍等...</p>');
	});
});
</script>
<!-- footer out -->
</body>
</html>