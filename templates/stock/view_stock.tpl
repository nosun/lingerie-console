<div  style="width:800px;overflow:hidden;">
<div class="distribution-group">
<div class="button exportStock" style="float:right;display:inline-block;margin:10px 0">导出库存</div>
</div>
</div>
<div id="stock_table" class="stock_tb" style="width:800px;"></div>


	<script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
    
    // Load the Visualization API and the piechart package.
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
          url: url('stock/ajaxgetstockdata'),
          dataType:"json",
          async: false
          }).responseText;

      var response_data = jQuery.parseJSON(response_str);
      var stock_dt = new google.visualization.arrayToDataTable(response_data);

      /*
      var formatter = new google.visualization.NumberFormat({prefix: '$'});
      formatter.format(u_contry_rank_table_dt, 2); // Apply formatter to second column
      formatter.format(u_order_amount_rank_dt, 1); // Apply formatter to second column
      
      var chart = new google.visualization.PieChart(document.getElementById('user_country_rank'));
      chart.draw(u_contry_rank_pie_dt, {width:600,height:350});

      */
      var table1 = new google.visualization.Table(document.getElementById('stock_table'));
      table1.draw(stock_dt, {cssClassNames: getDefaultCssOptions()});

      removeGoogleTableCss('google-visualization-table-table');
      google.visualization.events.addListener(table1 , 'sort',function(event) {removeGoogleTableCss('google-visualization-table-table');});

      
    }

    </script>