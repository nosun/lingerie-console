<table>
<tbody>
<tr>
<td width="50%">
<h3 style="text-align:center;">下单国家组成</h3>
<div id="user_country_rank"></div>
</td>
<td>
<h3 style="text-align:center;">下单最多的十个国家</h3>
<div id="user_country_rank_table"  class="statistics_tb"></div>
</td>
</tr>
<tr>
<td>
<div>
<h3 style="text-align:center;">下单最多的用户TOP10</h3>
<div id="user_most_orderd"  class="statistics_tb"></div>
</div>
</td>
<td>
<div>
<h3 style="text-align:center;">付款最多的用户TOP10</h3>
<div id="user_most_payed"  class="statistics_tb"></div>
</div>
</td>
</tr>
</tbody>
</table>
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
          url: url('statistics/getdata/user'),
          dataType:"json",
          async: false
          }).responseText;

      var response_data = jQuery.parseJSON(response_str);
      var u_contry_rank_pie_dt = new google.visualization.arrayToDataTable(response_data['U_CONTRY_RANK_PIE']);
      var u_contry_rank_table_dt = new google.visualization.arrayToDataTable(response_data['U_CONTRY_RANK_TABLE']);
      var u_order_nums_rank_dt = new google.visualization.arrayToDataTable(response_data['U_ORDER_NUMS_RANK']);
      var u_order_amount_rank_dt = new google.visualization.arrayToDataTable(response_data['U_ORDER_AMOUNT_RANK']);

      var formatter = new google.visualization.NumberFormat({prefix: '$'});
      formatter.format(u_contry_rank_table_dt, 2); // Apply formatter to second column
      formatter.format(u_order_amount_rank_dt, 1); // Apply formatter to second column
      
      var chart = new google.visualization.PieChart(document.getElementById('user_country_rank'));
      chart.draw(u_contry_rank_pie_dt, {width:600,height:350});

      var table1 = new google.visualization.Table(document.getElementById('user_country_rank_table'));
      table1.draw(u_contry_rank_table_dt, {cssClassNames: getDefaultCssOptions()});

      var table2 = new google.visualization.Table(document.getElementById('user_most_orderd'));
      table2.draw(u_order_nums_rank_dt, {cssClassNames: getDefaultCssOptions()});

      var table3 = new google.visualization.Table(document.getElementById('user_most_payed'));
      table3.draw(u_order_amount_rank_dt, {cssClassNames: getDefaultCssOptions()});

      
      removeGoogleTableCss('google-visualization-table-table');
      google.visualization.events.addListener(table1 , 'sort',function(event) {removeGoogleTableCss('google-visualization-table-table');});
      google.visualization.events.addListener(table2 , 'sort',function(event) {removeGoogleTableCss('google-visualization-table-table');});
      google.visualization.events.addListener(table3 , 'sort',function(event) {removeGoogleTableCss('google-visualization-table-table');});

      
    }

    </script>