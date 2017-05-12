<div style="max-width:1050px; overflow:hidden;">

<h3 style="text-align:center;">爆款产品(<?php echo date('Y/m/d', $filters['starttime']) . '-' . date('Y/m/d', $filters['endtime'])?>)</h3>
<?php foreach($popularProducts as $k=>$order_item):?>
<div class="order-item-grid">
<table width="200px" class="t-order-item">
<tbody>
<tr><th>
<dl>
<dt><h3 style="padding-top:0;margin-top:5px;">SN: <?php echo $order_item->SN;?> </h3></dt>
<dd>
售出<span class="oitem-qty"><?php echo $order_item->Quantity;?></span >件
</dd>

<dd>
<span style="font-size:16px;"><?php echo '$'.$order_item->Contribution;?></span>
</dd>
</dl>
</th>

<th class="prodImage">
<?php if(isset($order_item->imageSource)):?>
<a href="<?php echo url($order_item->imageSource);?>"><img src="<?php 
if(startsWith($order_item->imageSource, 'http')){
	echo $order_item->imageSource;
}else{
	echo url(get_thumbnail($order_item->imageSource));
}?>" title="" alt="" class="product_thumb" style="float:none;"/></a>
<?php else:?>
<img src="<?php echo url('files/default.jpg');?>" title="" alt="" class="product_thumb"/>
<?php endif;?>
</th>

</tr>

</tbody>
</table>
</div>
<?php endforeach;?>
</div>

<div  style="width:1050px; margin-top:20px;">
<h3 style="text-align:center;">所有产品购买情况(<?php echo date('Y/m/d', $filters['starttime']) . '-' . date('Y/m/d', $filters['endtime'])?>)</h3>
<div id="prod_selling_table" class="statistics_tb"></div>
</div>
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
    
    // Load the Visualization API and the piechart package.
    google.load('visualization', '1', {'packages':['corechart']});
    google.load('visualization', '1', {'packages':['table']});
    
    // Set a callback to run when the Google Visualization API is loaded.
    google.setOnLoadCallback(drawChart);


    function removeGoogleTableCss(clsName){
        $('.'+clsName).removeClass(clsName);
    }

    function getDefaultCssOptions(){
        return {
      	    headerRow: 'someclass',
      	    tableRow: 'someclass',
      	    oddTableRow: 'someclass',
      	    selectedTableRow: 'someclass',
      	    hoverTableRow: 'someclass',
      	    headerCell: 'header-cell',
      	    tableCell: 'someclass',
      	    rowNumberCell: 'someclass'
      	  };
    }

    function drawChart() {
      var response_str = $.ajax({
          url: url('statistics/getdata/product'),
          dataType:"json",
          async: false
          }).responseText;

      var response_data = jQuery.parseJSON(response_str);
      var p_selling_rank_dt = new google.visualization.arrayToDataTable(response_data['P_SELLING_DATA']);

      var formatter = new google.visualization.NumberFormat({prefix: '$'});
      formatter.format(p_selling_rank_dt, 2); // Apply formatter to second column

      var p_selling_rank_table = new google.visualization.Table(document.getElementById('prod_selling_table'));
      p_selling_rank_table.draw(p_selling_rank_dt, {cssClassNames: getDefaultCssOptions()});

      removeGoogleTableCss('google-visualization-table-table');
      google.visualization.events.addListener(p_selling_rank_table , 'sort',function(event) {removeGoogleTableCss('google-visualization-table-table');});
    }

    </script>