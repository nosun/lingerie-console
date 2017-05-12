<table>
<tr>
<td>
<h3 style="text-align:center;">每日下单金额</h3>
<div id="order_amount_line_chart"></div>
</td>
</tr>
<tr>
<td>
<h3 style="text-align:center;">每日订单数量</h3>
<div id="order_numbers_line_chart"></div>
</td>
</tr>
<tr>
<td>
<h3 style="text-align:center;">每日订单件数</h3>
<div id="order_product_qty_line_chart"></div>
</td>
</tr>
<tr>
<td>
<h3 style="text-align:center;">每日下单情况记录</h3>
<div id="orders_table" class="statistics_tb"></div>
</td>
</tr>
<tr>
<td>
<h3 style="text-align:center;">每日利润</h3>
<div id="orders_profit_table" class="statistics_tb"></div>
<table style="width:100%; text-align: right;">
<tr>
	<td>总计:<span id="total_profit"></span></td>
</tr>
</table>
</td>
</tr>
</table>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
    // Load the Visualization API and the piechart package.
    google.load('visualization', '1', {'packages':['corechart']});
    google.load('visualization', '1', {'packages':['table']});
    
    // Set a callback to run when the Google Visualization API is loaded.
    google.setOnLoadCallback(drawChart);


    function convertDate(datestr){
        var dates = datestr.split('-');
        return new Date(dates[0], dates[1] - 1, dates[2]);
    }

    function convertDateForJsonData(data, position){
        var data = data.splice(1,data.length - 1);
        for(var i = 0; i < data.length;i++){
    	  data[i][position] = convertDate(data[i][position]);
      }
      return data;
    }


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

    function concatJSONForColumnChart(data, aix_pos, head_pos, value_pos){
        var newhead = [];
        var newbody = [];
        var aixs = {};

        //first get all head.
        for(var i = 0; i < data.length; i++){
            if(!$.inArray(data[i][head_pos], newhead)){
                newhead.push(data[i][head_pos]);
            }
        }

        
        for(var i = 0; i < data.length;i++){
            if(data[i][aix_pos] in aixs){
                //key already exisits.
                record_pos = aixs[data[i][aix_pos]];
                newbody[record_pos].push(data[i][value_pos]);
            }else{
                var record = [];
                record.push(data[i][aix_pos]);	
                record.push(data[i][value_pos]);
            }
        }
    }

    function drawChart() {
      var response_str = $.ajax({
          url: url('statistics/getdata/order'),
          dataType:"json",
          async: false
          }).responseText;
      var response_data = jQuery.parseJSON(response_str);

      /***********************Order Revenue Chart **************************************/
      var order_revenue_data = convertDateForJsonData(response_data['O_REVENUE_DATA'], 0);
      
      var o_revenue_dt = new google.visualization.DataTable();
      o_revenue_dt.addColumn('date', '日期');
      o_revenue_dt.addColumn('number', '销售额');
      o_revenue_dt.addColumn('number','产品销售额');
      o_revenue_dt.addColumn('number', '其它费用');
      o_revenue_dt.addRows(order_revenue_data);

      var formatter = new google.visualization.NumberFormat({prefix: '$'});
      formatter.format(o_revenue_dt, 1);
      formatter.format(o_revenue_dt, 2);
      formatter.format(o_revenue_dt, 3);

      var chart = new google.visualization.LineChart(document.getElementById('order_amount_line_chart'));
      chart.draw(o_revenue_dt, {width:1000,height:560});
      /*****************End Order Revenue Chart **************************************/
      
      /***********************Order Validation Chart **************************************/
	  var order_validation_data = convertDateForJsonData(response_data['O_VALIDATION_DATA'], 0);
	  var o_validation_dt = new google.visualization.DataTable();
	  o_validation_dt.addColumn('date', '日期');
      o_validation_dt.addColumn('number', '总下单数');
      o_validation_dt.addColumn('number', '付款订单数');
      o_validation_dt.addRows(order_validation_data);

      var chart = new google.visualization.LineChart(document.getElementById('order_numbers_line_chart'));
      chart.draw(o_validation_dt, {width:1000,height:560});
      /******************End Order Validation Chart **************************************/
      
      /******************Order Product Qty Chart *****************************************/
      var order_product_qty_data = convertDateForJsonData(response_data['O_PRODUCT_QTY_DATA'], 0);
      var o_order_product_dt = new google.visualization.DataTable();
      o_order_product_dt.addColumn('date', '日期');
      o_order_product_dt.addColumn('number', '下单件数');
      o_order_product_dt.addRows(order_product_qty_data);
      var chart = new google.visualization.LineChart(document.getElementById('order_product_qty_line_chart'));
      chart.draw(o_order_product_dt, {width:1000,height:560,series:[{color: '#DC3912'}]});
      
      /*****************End Order Product Qty Chart **************************************/
      var order_bysite_data = convertDateForJsonData(response_data['O_BYSITE_DATA'], 1);
	  var o_bysite_dt = new google.visualization.DataTable();
	  o_bysite_dt.addColumn('string', '网站');
	  o_bysite_dt.addColumn('date', '下单时间');
	  o_bysite_dt.addColumn('number', '销售额');
	  o_bysite_dt.addColumn('number', '总下单量');
	  o_bysite_dt.addColumn('number', '有效下单量');
	  o_bysite_dt.addRows(order_bysite_data);
      formatter.format(o_bysite_dt, 2);
	  
      var ordersTable = new google.visualization.Table(document.getElementById('orders_table'));  
      ordersTable.draw(o_bysite_dt, {cssClassNames: getDefaultCssOptions()});
      removeGoogleTableCss('google-visualization-table-table');
      google.visualization.events.addListener(ordersTable , 'sort',function(event) {removeGoogleTableCss('google-visualization-table-table');});

      /*************************利润统计***********************************************/
      var order_profit_data = convertDateForJsonData(response_data['O_PROFIT_DATA'], 0);
	  var o_profit_dt = new google.visualization.DataTable();
	  o_profit_dt.addColumn('date', '发货时间');
	  o_profit_dt.addColumn('number', '销售额');
	  o_profit_dt.addColumn('number', '运费');
	  o_profit_dt.addColumn('number', '供应商成本');
	  o_profit_dt.addColumn('number', '利润');
	  o_profit_dt.addRows(order_profit_data);

	  var total_profit = 0.0;
	  for(key in order_profit_data) {
		  total_profit += order_profit_data[key][4];
	  }
	  document.getElementById('total_profit').innerHTML = Math.round(total_profit * 100) / 100;
	  dollarformatter = new google.visualization.NumberFormat({prefix: '$'});
	  rmbformatter = new google.visualization.NumberFormat({prefix: '￥'});
	  dollarformatter.format(o_profit_dt, 1);
	  rmbformatter.format(o_profit_dt, 2);
	  rmbformatter.format(o_profit_dt, 3);
	  rmbformatter.format(o_profit_dt, 4);
	  
      var ordersProfitTable = new google.visualization.Table(document.getElementById('orders_profit_table'));  
      ordersProfitTable.draw(o_profit_dt, {cssClassNames: getDefaultCssOptions()});
      removeGoogleTableCss('google-visualization-table-table');
      google.visualization.events.addListener(ordersTable , 'sort',function(event) {removeGoogleTableCss('google-visualization-table-table');});
      /*
      var siteOrderAmountView = new google.visualization.DataView(o_bysite_dt);
      siteOrderAmountView.setColumns([1, 0, 2]);

      var chart = new google.visualization.ColumnChart(document.getElementById('columnchart-test'));
      chart.draw(siteOrderAmountView, {width:1200,height:350});
      
      var siteOrderNumsView = new google.visualization.DataView(o_bysite_dt);
      siteOrderNumsView.setColumns([1, 0, 3, 4]);
      */
    }

    </script>