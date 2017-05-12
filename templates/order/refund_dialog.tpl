
<!-- dialog contents -->
<div class="specific_info">Order # <?php echo $orderInfo->number;?></div>

<div class="form_message" style="display: none;"></div>

<form class="form">
    <div class="form-row radio-button">
        <label class="default-label">
            <input type="radio" name="refund_type" value="full">
            
              <strong>Full refund</strong>
              <em>Refund the full amount <span class="full_amount"><?php echo $orderInfo->pay_amount;?></span></em>
            
        </label>
    </div>

    <div class="form-row radio-button">
        <label class="default-label selected">
            <input type="radio" name="refund_type" value="partial"> <strong>Partial refund</strong>
            <em>Refund a partial amount</em>
        </label>
        
        <div class="partial-amount" style="display:none;">
        	<label for="amount" class="default-label">Amount:</label>
        	<input class="field" type="text" name="amount" value="<?php echo $orderInfo->pay_amount?>"/>
        </div>
        
    </div>
    
    
    
  </form>
