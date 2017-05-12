<table class="t-orders"><caption></caption>
<thead>
<tr class="t-header">
<td>姓名</td>
<td>邮箱</td>
<td>国家</td>
<td>电话</td>
<td>客户网站</td>
<td>留言</td>
<td>时间</td>
<td>lingerie网站</td>
</tr>
</thead>
<tbody>

<?php $userCount = 0;
foreach($wholesaleUserList as $k=>$v):
$userCount++;
?>
<tr class="t-row order-row <?php echo ($userCount % 2 == 0)? 'odd':'even';?>">
<td class="o-number"><?php echo $v->name;?></td>
<td class="o-created"><?php echo $v->email;?></td>
<td class="o-created"><?php echo $v->country;?></td>
<td class="o-status"><?php echo $v->phone;?></td>
<td class="o-status_payment">
<?php if(!empty($v->website)){
	if (!startsWith($v->website, 'http://')) {
		$v->website = 'http://' . $v->website;	
	}
}
?>
<a href="<?php echo $v->website; ?>" target="_blank"><?php echo $v->website; ?></a></td>
<td class="o-payment_method"><?php echo $v->comment;?></td>
<td class="o-status_shipping"><?php echo $v->created;?></td>
<td class="o-shipping_method"><?php echo $v->site_name;?></td>
</tr>
<?php endforeach;?>
</tbody>
</table>