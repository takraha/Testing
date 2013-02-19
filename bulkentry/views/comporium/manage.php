		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
		<script src="../../../../../themes/comporium/js/jquery.modal.min.js"></script>

<style>
	.error {
		background:red;
	}
	
	.no_error {
		background:white;
	}
	
	.over {
		background-color:green;
	}
	
</style>
<script>
	$(function() {
			//forces the account id to only be 8 numbers
			//$("#account_id, #check_digit").live("keydown",function(event){
				//if(event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 37 || event.keyCode == 39 || event.keyCode == 9 || event.keyCode == 32) {
												
		     	//} else if((event.keyCode < 48 || event.keyCode > 57) && (event.keyCode < 96 || event.keyCode > 105 )) {return false;}
			
			  //});
			
		//})
		//attach button functionality
		this_doc_base = 'http://localhost:10088/bulkentry'
		
		//delete row functionality
		
		$(document).find("img#delete_btn").live("mousedown",function(d) {
				$parent_tr = $(this).parent().parent()
				
				//verify that this is not an attached transaction
				if($parent_tr.find("input#attached_transaction_id").val() == "") {
					delete_row = this_doc_base + '/remove_row/' + $parent_tr.find("input#id").val() +'/batch_transactions'
				} else {
					delete_row = this_doc_base + '/remove_row/' + $parent_tr.find("input#id").val() +'/batch_transactions_attached'
				}
				
				$.ajax({
					type:'POST',
					url:delete_row,
					success:function(data) {
						$parent_tr.remove()
					}
				})
			
			})
			
			//this is the auto shift with addition key functionality
			$(":input").live("keydown",function(event) {
				this_index = $(this).index("input#" + $(this).attr("id"))
				
				if(event.shiftKey == 1 && event.keyCode == 61) {
					event.preventDefault()
					if($(this).attr("id") == 'entry_type') {
						addTableRow('new_row',1)
						row_amount = $(document).find("input#account_id").size()
						$(this).parents("tr").next("tr").find("#account_id").focus().select()
					} else {
						$(this).parents("td").next("td").find(":input").focus().select()
					}
				}
				 //}		
			})
		
		//attach the blur function to each input
		$(document).find(":input").live("blur",function(a) {
			
			
			//define all of the variables that we are getting from each input
			var input_id = a.currentTarget.id
			var input_value = $(this).attr("value")
			var this_index = $(this).index("input#" + input_id)
			var this_transaction_id = $(this).parents("tr").find("input#id").attr("value");
			var this_batch_id =  $(this).parents("tr").find("input#batch_id").attr("value");
			var $this_parent = $(this).parent().parent().parent()
			var check_digit;
			var attached_record = $this_parent.find("input#attached_transaction_id").val()
			//if the input is the company, do this
			//if the input is the account number then do this
			if(input_id == 'account_id') {
				this_url = 'http://localhost:10088/bulkentry/bulkentry/ajax_customer/' + input_value;
				$sibling_label = $this_parent.find("#customerName")
				$sibling_hidden_check_digit = $this_parent.find("#hidden_check_digit")
				$sibling_label_payment_amount = $this_parent.find("input#payment_amount")
				$sibling_default_payment_amount = $this_parent.find("input#default_payment_amount")
				
				$.ajax({
    				url: this_url,
    				dataType:'json',
   					 success: function(data) {
       					$sibling_label.html(data['customerName'])
						if(parseFloat(data['minimumAmountDue'])) {
							var new_val = parseFloat(data['minimumAmountDue'])
						} else {
							var new_val = '0.00'
						}
						
						$sibling_label_payment_amount.val(new_val)
						$sibling_default_payment_amount.val(new_val)
						$sibling_hidden_check_digit.val(data['checkDigit'])
						
					}
				});
			}

			//if they are entering the check digit number
		
			if(input_id == 'check_digit') {
				check_digit_val = $(this).parents("tr").find("#hidden_check_digit").val()
				if(input_value != check_digit_val) {
					$(this).css("background","red")
					setTimeout(function(){
            			$("input#check_digit").eq(this_index).focus().select()
           			 }, 1);
				} else {
					$(this).css("background","white")
				}
			}
			
			//if they are entering the payment amount 
			if(input_id == 'payment_amount') {
				minimum_amount_due = parseFloat($this_parent.find("input#default_payment_amount").val())
				this_amount = parseFloat(input_value)
				
				if(this_amount > minimum_amount_due) {
					$(this).attr("class",'over')
				} else if(this_amount == minimum_amount_due) {
					$(this).attr("class","no_error")
				} else {
					$(this).attr("class","error")
				}
			}
			
			
			//if the customer is paying more than the payment amount (they are going to pay on another account also
			if(input_id == 'tender_amount') {
				tender_amount = parseFloat(input_value);
				payment_amount = parseFloat($this_parent.find("input#payment_amount").val())
				if(tender_amount > payment_amount) {
					next_row = $this_parent.index("#batch_transactions_table tr");
					addTableRow('attached_row',next_row)
					
				}
			}
			
			
		
			if(attached_record == "") {
				auto_save_url = this_doc_base + "/bulkentry/auto_save_data/" + this_transaction_id +"/batch_transactions/" + input_id + "/" + input_value;
			} else {
				auto_save_url = this_doc_base + "/bulkentry/auto_save_data/" + this_transaction_id +"/batch_transactions_attached/" + input_id + "/" + input_value;
			}

			$.ajax({
				type:'POST',
				url:auto_save_url
			})
	
		
	})
	
	
	})
	
	//new row function 
	function addTableRow(type,index) {
		var table = $("#batch_transactions_table")
		
		var $tr = $(table).find("tbody tr").eq(index).clone();
		//add the table row to the bottom of the table
		batch_id = $(table).find("tbody tr").eq(1).find("input#batch_id").val()

		if(type == 'new_row') {
			$(table).find("tbody tr:last").after($tr)
			$tr.find("#account_id,#id,#check_number,#check_digit,#tender_amount,#payment_amount").val("")
			new_row_url = this_doc_base + '/add_new_row/' + batch_id + "/batch_transactions";


		} else {
			$(table).find("tbody tr").eq(index).after($tr)
			$tr.find("select, input#tender_amount, input#check_number").css("display",'none')
			tender_amount = parseFloat($tr.find("input#tender_amount").val())
			payment_amount = parseFloat($tr.find("input#payment_amount").val())
			remaining_amount = parseFloat(tender_amount - payment_amount);
			attachment_id = $tr.find("input#id").val()
			$tr.find("#account_id,#check_digit,#tender_amount").val("")
			$tr.find("#tender_amount").val(remaining_amount)
			$tr.find("input#attached_transaction_id").val(attachment_id)
			new_row_url = this_doc_base + '/add_new_row/' + batch_id + '/batch_transactions_attached/' + attachment_id + "/" + remaining_amount;
			
		
		}
		
		$tr.find("#customerName").text("")
					$.ajax({
    					url: new_row_url,
    					dataType:'json',
   					 	success: function(data) {
							$tr.find("input#batch_id").val(batch_id)
							$tr.find("input#id").val(data);
							
							
					
						}
					 });
		
	}
	


</script>
 <style>
 .modal {
  width: 900px;
 zIndex:-100;
 height:500px;
 }

#payment_indicator {
	background-image: '../../../../../themes/comporium/images/buttons/small_green_triangle.jpg';
	background-position:left;
}

#batch_transactions {
	width:100%;
}
#batch_transactions td {
	font-size:13px;
}
  </style>
  
  
		<div class="modal-header">
        	<h1>Edit Batch</h1>
        </div>
        <div class="modal-body">
        	<table class="tablesorter" id="batch_transactions_table">
            	<tbody>
                    <tr>
                    	<th>&nbsp;</th>
                        <th>Company</th>
                        <th>Acct #</th>
                        <th>Customer Name</th>
                        <th>Payment Amt</th>
                        <th>Tender Type</th>
                        <th>Tender Amt</th>
                        <th>Check #</th>
                        <th>Payment Type</th>
                        <th>&nbsp;</th>
                    </tr>
                    
                    <?php 
						$company = $this->entry->get_companies();
						$tenders = $this->entry->get_tender_types();
						$entry_types = $this->entry->get_entry_types();

							foreach ($batch as $transaction) {
								$account_info=$this->entry->account_lookup($transaction['account_id']);
								
								$attached_accounts = $this->entry->get_attached_transactions($transaction['id'],'attached_transaction_id');

					?>
                    <tr nowrap>
                    	<form id="batch_transactions">
                    
                    	<td><input type="hidden" id="id" value="<?= $transaction['id']?>"/>
                        	<input type="hidden" id="batch_id" value="<?= $transaction['batch_id']?>" />
                            <input type="hidden" id="hidden_check_digit"/>
                            <input type="hidden" id="attached_transaction_id"/>
                            </td>
                        <td>
               
                        	<div class="input-container">
                            <select id="lib_name">
                            	<?php
									$length = count($company);
									for($i=0;$i<$length;$i++) {
										if($transaction['lib_name'] == $company[$i]['lib_name']) {
										echo('<option selected="selected">'.$company[$i]['lib_name'].'</option>');
										} else {
											echo('<option>'.$company[$i]['lib_name'].'</option>');
										}

									}
								?>
                            </select>
                            </div>
                       </td>
                        <td>
                        	<div class="input-container">
                            	<?php
                            		echo('<input type="text" maxlength="8" id="account_id" value="'.$transaction['account_id'].'" size="12" /><input type="text" id="check_digit" value="'.$account_info['checkDigit'].'" size="1" />');
								?>
                             </div>
                        </td>
                        <td>
                        	<div class="input-container">
          						<label id="customerName"><?php echo $account_info['customerName'] ?></label>
                            </div>
                        </td>
                        <td>
                        	<div class="input-container">
                      		<?php echo('<input type="text" id="payment_amount" value="'.$transaction['payment_amount'].'" size="12" style="text-align:right;"/>'); ?>
                            <input type="hidden" value="" id="default_payment_amount"/>
                           </div>
                        </td>
                        <td>
                        	<div class="input-container">
                            <select id="tender_type">
                        	<?php 
								$length = count($tenders);
								
								for($y=0; $y < $length; $y++) {
									if($tenders[$y]['id'] == $transaction['tender_type']) {
										echo ('<option selected="selected" value="'.$tenders[$y]['id'].'">'.$tenders[$y]['value'].'</option>');
									} else {
										echo ('<option value="'.$tenders[$y]['id'].'">'.$tenders[$y]['value'].'</option>');
									}

								}
							?>
                            </select>
                            </div>
                       </td>
                     
                        <td>
                        	<div class="input-container">
                           <?php echo('<input type="text" size="10" id="tender_amount" value="'.$transaction['tender_amount'].'"/>'); ?>
                           </div>
                       </td>
                          <td>
                        	<div class="input-container">
                            <input type="text" size="4" id="check_number" value="<?php echo($transaction['check_number']); ?>"/>
                           </div>
                        </td>
                        <td>
                        	<div class="input-container">
                            	<select id="entry_type">
                        			<?php 
										$length = count($entry_types);
										
										for($e = 0; $e < $length; $e++) {
											if($entry_types[$e]['id'] == $transaction['entry_type']) {
												echo('<option selected="selected" value="'.$entry_types[$e]['id'].'">'.$entry_types[$e]['value'].'</option>');
											} else {
												echo('<option value="'.$entry_types[$e]['id'].'">'.$entry_types[$e]['value'].'</option>');
											}

										}
									?>
                            </select>
                            </div>
                         </td>
                         <td><img id="delete_btn" src="../../../../../themes/comporium/images/buttons/cancel-icon.png" /></td>
 </form>
                    </tr>
                
					<?php
					if(count($attached_accounts) > 0) {
						foreach($attached_accounts as $this_account ) {
							$attached_customer_info = $this->entry->account_lookup($this_account['account_id']);

					?>
                    	<tr nowrap>
                        	<form id="batch_transactions">
                    
                    		<td><input type="hidden" id="id" value="<?= $this_account['id']?>"/>
                            <input type="hidden" id="attached_transaction_id" value="<?= $this_account['attached_transaction_id'] ?>"/>
                           <input type="hidden" id="hidden_check_digit"/>

                            </td>
                        	<td>&nbsp;</td>
                            <td>
                        	<div class="input-container">
                            	<?php
                            		echo('<input type="text" maxlength="8" id="account_id" value="'.$this_account['account_id'].'" size="12" /><input type="text" id="check_digit" value="'.$account_info['checkDigit'].'" size="1" />');
								?>
                             </div>
                        </td>
                        <td>
                        	<div class="input-container">
          						<label id="customerName"><?php echo $attached_customer_info['customerName'] ?></label>
                            </div>
                        </td>
                        <td>
                        	<div class="input-container">
                      		<?php echo('<input type="text" id="payment_amount" value="'.$this_account['tender_amount'].'" size="12" style="text-align:right;"/>'); ?>
                            <input type="hidden" value="<?=$this_account['tender_amount']?>" id="default_payment_amount"/>
                           </div>
                        </td>
                        <td>
                        </td>

                            </form>
                        </tr>
                        
                    <?php
						}
					}
						}
					?>
                   
                  </tbody>               
           </table>
           
           <table>
           	<tr>
                  	<td style="width:330px; text-align:left;"><a style="white-space:nowrap;" id="new_table_row" onclick="addTableRow('new_row',1)" class="btn">Insert</a></td>
                  	<td style="width:300px; text-align:center;"><a  class="btn" rel="modal:close">Close</a></td>
                  	<td style="width:330px; text-align:right;"><a id="new" class="btn">Submit Batch</a></td>
            </tr>
        </table>
	</div>
</div>

