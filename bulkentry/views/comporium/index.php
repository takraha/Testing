<?php $this->load->view("partial/header"); ?>
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
		<script src="../../../../../themes/comporium/js/jquery.modal.min.js"></script>
        
        <style>
			select,input {
				font-size:80%;
			}
		</style>

<script>
	$(document).ready(function() {
		$("#delete_batch").live("click",function(event) {
			event.preventDefault()
			batch_id = $(this).attr("data-id")
			$.ajax({
				type:'POST',
				url:"http://localhost:10088/bulkentry/delete_batch/" + batch_id
			});
			$(this).parents("tr").remove()
		})
		
		$("#new_batch_btn").live("click",function(event) {
			event.preventDefault()
			default_company = $("#default_company").val()
			default_entry_type = $("#default_entry_type").val()
			default_tender_type = $("#default_tender_type").val()
			this_cashier = $("#cashier_id").val()
			
			$.ajax({
				type:'POST',
				url:'http://localhost:10088/bulkentry/new_batch/'+default_company + '/' + default_tender_type + '/' + default_entry_type + '/' + this_cashier
			})
			
			$.modal.close()
			//document.location.reload(true)
			
			
		});
	})
</script>
<?php echo "this is my username: $my_user"; ?>
	<div class="page-content">
    	<div class="column column4of4">
        	<section class="panel inset">
            	<header class="panel-header panel-header-dark taL">
                	 <?=form_open("multilocation/$controller_name/search",array('id'=>'search_form'))?>
                    <?= form_label(lang('locations_search_locations'), 'item', array('class'=>'visuallyhidden')) ?>
                    <?= form_input(array('name' => 'search', 'id' => 'batch-search', 'placeholder' => 'Search Batch Entries', 'accesskey' => ''));?>
                        <a title="new_batch" rel="modal:open" class="new btn" href="#new_batch" id="new"><span>New Batch</span></a>
                <?=form_close()?>
                </header>


				<div class="panel-content">
                    	<table class="tablesorter" id="locations">
                        	<tbody>
                        		<tr>
                            		<th>Batch Name</th>
                            		<th>Owner</th>
                            		<th>Modified Date</th>
                                    <th>Created Date</th>
                                    <th>Payments</th>
                                    <th>Total</th>
                                    <th>Status</th>
                            		<th title="<?= lang('common_edit') ?>"><span class="visuallyhidden"><?= lang('common_edit') ?></span></th>
                            		<th title="<?= lang('common_delete') ?>"><span class="visuallyhidden"><?= lang('common_delete') ?></span></th>
                        		</tr>
                                
                              
                    			
								
								
								<?php 
									foreach($batch as $entry) {
										$date_created = new DateTime($entry['date_created']);
										$dateModified = new DateTime($entry['last_modified']);
										
										$dateModified = $dateModified->format('m-d-Y g:i a');
										$batch_name = $entry['username'].'_'.$date_created->format('d_m_Y');
										$date_created=$date_created->format('m-d-Y g:i a');
										$items = $this->entry->get_transactions($entry['id']);
										$get_attached = $this->entry->get_attached_transactions($entry['id'],'batch_id');
										$totals = $this->entry->get_totals($entry['id']);
										if($totals[0]['tender_amount'] == '') {
											$total_payments = '0.00';
										} else {
											$total_payments = $totals[0]['tender_amount'];
										}
								?>
										<tr>
                                        	<td><?php echo $batch_name ?></td>
                                        	<td><?php echo $entry['username'] ?></td>
                                            <td><?php echo $dateModified ?></td>
                                            <td><?php echo $date_created ?></td>
                                            <td><?php echo(count($items) + count($get_attached)); ?></td>
                                            <td><?php echo '$'.$total_payments; ?></td>
                                            <td><?php echo $entry['value'] ?></td>
                                            <?php
												$url="bulkentry/".$controller_name."/view_transactions/".$entry['id'];
												echo ('<td><a class="thickbox edit" href="http://localhost:10088/'.$url.'" id="edit" rel="modal:open"> </a></td>');
											
											?>
                                            <td><a id="delete_batch" class="thickbox delete" title="Delete Batch" data-id="<?=$entry['id']?>"> </a></td>
                                        </tr>
                                      
                                <?php
									}
								?>
                              
                                
                              </tbody>
							</table>
                            </div>
                            
                            
            

            </section>
        </div>
   </div>

<style>
	.modal table {
		width:300px;
		margin-left:30px;
		display:block;
	}
	
	.modal table tr td select {
		background:white;
		border:#999 1px solid;
		width:180px;
		padding:2px;
		height:26px;
		font-size:13px;
		padding-top:4px;
	}
	
	.modal table tr td {
		height:20px;
		font-size:13px;
	}
	
	.modal-footer {
		border-top:1px solid #ccc;
		margin-top:5px;
		height:10px;
		background:#efefef;
	}
	
	.modal-footer .btn {
		padding:.4em 2.5em;
	}
	
</style>
<?php 
		$get_companies = $this->entry->get_companies();
		$tenders = $this->entry->get_tender_types();
		$entry_types = $this->entry->get_entry_types();

?>

<div class="modal" style="display:none; width:400px;" id="new_batch">
    <div class="modal-header">
                <h1>New Batch</h1>
            </div>
            <div class="modal-body">
                <form id="new_batch_form">
                	<input type="hidden" id="cashier_id" value="<?= $user_info->person_id ?>" />
                    <table>
                        <tr>
                            <td>Default Company</td>
                        </tr>
                        <tr>
                            <td>
                            	<select id="default_company">
                            	<?php
                                	foreach($get_companies as $this_company) {
										echo("<option>".$this_company['lib_name']."</option>");
									}
								?>
                               </select>
    					</td>
                        </tr>
                        <tr>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Default Payment Type</td>
                        </tr>
                        <tr>
                            <td><select id="default_tender_type">
                            	<?php 
									foreach($tenders as $this_tender) {
										echo('<option value="'.$this_tender['id'].'">'.$this_tender['value'].'</option>');
									}
								?>
                             </select></td>
                        </tr>
                        <tr>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Default Entry Type</td>
                        </tr>
                        <tr>
                            <td>
                            <select id="default_entry_type">
                            	<?php
                                	foreach($entry_types as $this_entry) {
										echo('<option value="'.$this_entry['id'].'">'.$this_entry['value'].'</option>');
									}
								?>
                              </select>
                           </td>
                        </tr>
                        
                        

                   </table>
                </form>
                
                <div class="modal-footer">
                	<a id="close" class="btn" rel="modal:close">Cancel</a>
                	<a id="new_batch_btn" class="btn">Create Batch</a>
               </div>
            </div>
 </div>
   
                        
        


   
