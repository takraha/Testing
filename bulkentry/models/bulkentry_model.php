<?php
require_once(APPPATH.'../lib/ComporiumAPI.php');


class Bulkentry_model extends CI_Model {

  public function __construct() {
        parent::__construct();
    }


	public function get_entry_types() {
		
		$query = $this->db->get('entry_types');
		$response = $query->result_array();
		return $response;
	}
		
	public function get_tender_types() {
		
		$query = $this->db->get('tender_types');
		$response = $query->result_array();
		return $response;
	}
		
   public function account_lookup($accountId) {
            $response= ComporiumAPI::lookupAccountWithBilling($accountId);
			$this_response=new ArrayObject($response);
			return $this_response;
    }


	public function get_companies() {
		
		$this->db->select('*');
		$this->db->from('companies');
		$this->db->where('is_enabled','1');
		
		$query = $this->db->get();
		$response = $query->result_array();
		return $response;
	}
		
	
	    /**
     * Get all location records
     *
     * @param  integer $limit  The maximum number of locations to fetch
     * @param  integer $offset The offset to begin fetching at
     * @return CI_DB_result
     */
	 public function get_totals($batch_id) {
		 $this->db->select_sum('tender_amount');
		 $this->db->where('batch_id',$batch_id);
		 $this->db->from('batch_transactions');
		 $query=$this->db->get();
		 $result = $query->result_array();
		 return $result;
	 }
		 
	
    public function get_transactions($batch_id) {
		//get the amount of transactions	
		

       // Format data to match the return of the SearchByAccountInfo call
		
		$this->db->select('*');
		$this->db->where('batch_id',$batch_id);
		$this->db->from('batch_transactions');
		$query=$this->db->get();
		$result=$query->result_array();
		
		return $result;
		
	}
	
	public function get_all($limit = 10000, $offset=0) {
	

		$query = $this->db->query("SELECT A.*,B.value,C.* FROM [CashierStationDev].[dbo].[phppos_batch] A JOIN [CashierStationDev].[dbo].[phppos_batch_status] B ON A.status = B.id INNER JOIN [CashierStationDev].[dbo].[phppos_employees] C ON A.cashier_id = C.person_id");
	
	error_reporting(E_ALL);
	ini_set('display_errors','1');
		$result = $query->result_array();		
		return $result;
		
    }
	
	/** 
	* deletes an entire batch to include items in the batch_transactions and batch_transactions_attached tables
	*@param int batch_id
	*/
	public function delete_batch($batch_id) {
		$this->db->where('batch_id',$batch_id);
		$this->db->delete('batch_transactions_attached');
		
		$this->db->where('batch_id',$batch_id);
		$this->db->delete('batch_transactions');
		
		$this->db->where('id',$batch_id);
		$this->db->delete('batch');
		
	}
	
	/** 
	* removes a row from the database table batch_transactions
	* @param $batch_transaction_id
	*/
	public function delete_row($batch_transaction_id,$table_name) {
		if($table_name == 'batch_transactions') {
			$this->db->where('attached_transaction_id',$batch_transaction_id);
			$this->db->delete("batch_transactions_attached");
		}
		$this->db->where('id',$batch_transaction_id);
		$this->db->delete($table_name);
	}
	
	
	/**
	* adds a new row into the batch_transactions_table & returns the newly inserted id
	* @param $batch_id
	**/
	public function new_transaction_row($batch_id,$table_name,$attached_transaction_id = NULL,$remaining_amount = NULL) {
	
		if($table_name == 'batch_transactions') {
			$new_row = array('account_id'=>'0','batch_id'=>$batch_id,'check_digit'=>'1','lib_name'=>"BREV",'payment_amount'=>'0.00','tender_amount'=>'0.00');
		} else {
			$new_row = array('account_id'=>'0','check_digit'=>'1','payment_amount'=>$remaining_amount,'tender_amount'=>$remaining_amount,'attached_transaction_id'=>$attached_transaction_id,'batch_id'=>$batch_id);
		}

		
		$new_string = $this->db->insert($table_name,$new_row);
		$last_record = $this->db->insert_id();
		return $last_record;
	}
	
	public function new_batch($company,$tender_type,$entry_type,$user) {
		error_reporting(E_ALL);
		ini_set('display_errors','1');
		$now = date("Y-m-d H:i:s");
		$new_batch = array("cashier_id" =>$user,"default_company"=>$company,"default_entry_type"=>$entry_type,"default_tender_type"=>$tender_type,"date_created"=>$now,"status"=>1);
		$this->db->insert('batch',$new_batch);
	}
	
	
	
	/** 
	* this function saves the regular batch transactions 
	*@param field_id varchar
	*@param $value varchar
	*@param $id int
	*/
	public function auto_save_transaction($id,$table_name,$field_id,$value) {
		$data = array($field_id=>$value);
		$this->db->where('id',$id);
		$this->db->update($table_name,$data);
		
	}
	
	/** 
	* this function saves the attached batch transactions 
	*@param field_id varchar
	*@param $value varchar
	*@param $id int
	*/
	public function auto_save_transaction_attached($id,$field_id,$value) {
		$data = array($field_id=>$value);
		$this->db->where('id',$id);
		$this->db->update('batch_transactions_attached',$data);
	}
	
	public function get_attached_transactions($id,$col_name) {
		$this->db->where($col_name,$id);
		$query = $this->db->get('batch_transactions_attached');
		$result = $query->result_array();
		
		return $result;
		
	}
	
	

}
?>