<div class="return-message">
<?php if(isset($update_count)):?>
<p>更新了 <?php echo $update_count;?> 个库存。</p>
<?php endif;?>
<?php if(isset($unchanged) && count($unchanged) > 0):?>
<p>*以下库存的更新请求没有产生作用：<br/>
<?php foreach($unchanged as $v):?>
		<?php echo $v?><br/>
<?php 	endforeach;?>
</p>
<?php endif;?>
</div>

<div class="upload_stock_method">
<h3>方法1：上传.csv文件</h3>
<p>请首先将Excel转换成.csv格式的文件</p>
<form id="upload_data" method="post" enctype="multipart/form-data" action="<?php echo url('stock/insertnewstockfile');?>">
    <?php if(isset($overwrite) && $overwrite):?>
        <input type="hidden" name="overwrite" />
    <?php endif;?>
    <input name="upload_file" class="nice_file_field" type="file" />
    <button>Submit</button>
</form>
</div>

<div class="upload_stock_method">
<h3>方法2：直接输入库存</h3>

<div class="intro" style="margin:10px;">
<p>在下面的文本框中填入要增加的库存数量。格式为：</p>
<p>
<span class="code-format" style='font-family:"Courier New"'>sn	color	size	stock_qty	supplier(optional)	type(optional)	weight(kg,optional)   bought_price(optional)  supplier_sn(optional)</span><br/>
序列号	颜色	大小	增加数量	供货商（可选）	进货价格（可选）    供应商编号（可选）    类型（可选）		重量（可选）
</p>
<p>每个值之间用制表符(\t)或逗号(,)分隔。可以直接复制Excel表中的数据填入。</p>
<p style='font-family:"Courier New"'>
2173-1,as shown,One size,3<br/>
7641	as shown	XL	50	自己定做    20.12   7641-S	corset	0.26

</p>
</div>
</div>

<div class="input-data" style="margin:10px;">
<form action="<?php echo url('stock/insertnewstockdata');?>" method="post">
<textarea name="stock_data" style="width:50%;height:300px;"></textarea>
<?php if(isset($overwrite) && $overwrite):?>
<input type="hidden" name="overwrite" value="1" />
<?php endif;?>
<div style="width:50%; text-align:right;margin-top:10px;">
<input style="width:200px;" type="submit" value="加入库存"/>
</div>
</form>
</div>