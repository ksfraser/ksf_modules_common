<?php
//$page_security = 'SA_BANKTRANSFER';
$path_to_root = "../..";

//include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");

include_once($path_to_root . "/gl/includes/gl_db.inc");
include_once($path_to_root . "/gl/includes/gl_ui.inc");

require_once( '../ksf_modules_common/class.fa_origin.php' );	//This needs to be replaced with composer namespace ksfraser/origin/...

/*
*	$js = "";
*	if ($SysPrefs->use_popup_windows)
*	        $js .= get_js_open_window(800, 500);
*	if (user_use_date_picker())
*	        $js .= get_js_date_picker();
*	
*	if (isset($_GET['ModifyTransfer'])) {
*	        $_SESSION['page_title'] = _($help_context = "Modify Bank Account Transfer");
*	} else {
*	        $_SESSION['page_title'] = _($help_context = "Bank Account Transfer Entry");
*	}
*	
*	page($_SESSION['page_title'], false, false, "", $js);
*	
*	check_db_has_bank_accounts(_("There are no bank accounts defined in the system."));
*/

/**//**************************************************************************
* Class for creating Bank Transfer records
*
* Intra-bank transfers can take a few days due to clearing.  
* So if we are trying to match a transfer between 2 different banks
*	(e.g. Bank to CC) then we need to add/subtract date ranges
*
*******************************************************************************/
class fa_bank_transfer extends fa_origin
{
	//protected $trans_no;
	//protected $transfer_type;	//Inherited as trans_type
	//protected $ref;		//Inherited as reference
	//protected $memo_;		//Inherited
	protected $FromBankAccount;
	protected $ToBankAccount;
	protected $charge;
	protected $amount;
	protected $target_amount;
	//protected $trans_date;	//Inherited

	function __construct()
	{
		//	display_notification( __FILE__ . ":" . __LINE__  );

		parent::__construct();
		//	display_notification( __FILE__ . ":" . __LINE__  );
		$this->set( "memo_", '' );
		//	display_notification( __FILE__ . ":" . __LINE__  );
		$this->set( "FromBankAccount", 0 );
		//	display_notification( __FILE__ . ":" . __LINE__  );
		$this->set( "ToBankAccount", 0 );
		//	display_notification( __FILE__ . ":" . __LINE__  );
		$this->set( "amount", 0 );
		//	display_notification( __FILE__ . ":" . __LINE__  );
		$this->set( "target_amount", 0 );
		//	display_notification( __FILE__ . ":" . __LINE__  );
		$this->set( "charge", 0 );
		$this->set( "trans_type", ST_BANKTRANSFER );
		//	display_notification( __FILE__ . ":" . __LINE__  );
	}
	function set( $field, $value = NULL, $enforce_only_native_vars = true )
	{
			display_notification( __FILE__ . ":" . __LINE__  );
		switch( $field )
		{
			case "charge":
			case "amount":
			case "target_amount":
				$oldvalue = $value;
			//	$value = price_format( $value );
				//	display_notification( __FILE__ . ":" . __LINE__ . " Value $oldvalue converted to $value " );
			break;
			case "trans_date":
			break;
		}
		//	display_notification( __FILE__ . ":" . __LINE__  );
		try {
			parent::set( $field, $value, $enforce_only_native_vars );
		} catch (Exception $e )
		{
/**
			if( KSF_FIELD_NOT_CLASS_VAR == $e->getCode() )
			{
				display_notification( __FILE__ . ":" . __LINE__ . ":" . "Error: " . $e->getMessage() );
				display_notification( __FILE__ . ":" . __LINE__ . ":" . print_r( $this, true )  );
				//Try again.
				$this->object_var_names();
				return parent::set( $field, $value, $enforce_only_native_vars );
			}
**/
			display_notification( __FILE__ . ":" . __LINE__ . ":" . "Error: " . $e->getMessage() );
		}
		//	display_notification( __FILE__ . ":" . __LINE__  );
	}
	/**//*************************************************************
	* Ensure the minimum fields are set.
	*
	* @param none
	* @returns bool
	******************************************************************/
	function can_process()
	{
		if( ! isset( $this->FromBankAccount ) )
		{
			throw new Exception( "Bank Transfer requires a FROM bank account" );
		}
		if( ! isset( $this->ToBankAccount ) )
		{
			throw new Exception( "Bank Transfer requires a TO bank account" );
		}
		if( ! isset( $this->trans_date ) )
		{
			throw new Exception( "Bank Transfer requires a Date Paid" );
		}
		if( ! isset( $this->amount ) )
		{
			throw new Exception( "Bank Transfer requires an amount" );
		}
		//ref, memo, charge and target_amount are probably optional
		return true;
	}
	/**//**************************************************************
	* Add a Bank Transfer
	*
	* How does this differ from 
	*	add_bank_trans($trans_type_to_use, $payment_no, $bank_account, $ref,
        *			$date_, $bank_amount - $charge, PT_CUSTOMER, $customer_id);
	* Is that a Bank Payment?
	*
	* gl/includes/db/gl_db_banking.inc
	* function add_bank_transfer($from_account, $to_account, $date_, $amount, $ref, $memo_, $charge=0, $target_amount=0)
	*
	* @param none
	* @returns int the Bank Transfer transaction number
	******************************************************************/
	function add_bank_transfer()
	{
		if( ! $this->can_process() )
		{
			//Should have been some exceptions by now
			throw new Exception( "Can't process Bank Transfer" );
		}
		//Target amount can be 0.  If so, then AMOUNT is used.
		//If Target amount is set, and the diff between it + charge is different than amount
		//	then a FX charge is included to balance.
		$trans_no = add_bank_transfer(	
						$this->FromBankAccount,
						$this->ToBankAccount, 
						$this->trans_date,
						$this->amount, 
						$this->reference, 
						$this->memo_, 
						$this->charge, 
						$this->target_amount
			);
		$this->set( "trans_no", $trans_no );
		return $trans_no;
	}
	/**//**************************************************************
	* Update a Bank Transfer
	*
	* @param none
	* @returns int the Bank Transfer transaction number
	******************************************************************/
	function update_bank_transfer()
	{
		if( ! $this->can_process() )
		{
			//Should have been some exceptions by now
			throw new Exception( "Can't process Bank Transfer" );
		}
		$trans_no = update_bank_transfer(	$this->trans_no, 
						$this->FromBankAccount,
						$this->ToBankAccount, 
						$this->trans_date,
						$this->amount, 
						$this->reference, 
						$this->memo_, 
						$this->charge, 
						$this->target_amount
			);
		$this->set( "trans_no", $trans_no );
		return $trans_no;
	}
}

class fa_bank_accounts_MODEL
{
	protected $bank_account_name;
	protected $bank_curr_code;
	protected $inactive;

	function __construct()
	{
	}
	/**//***************************************************************
	*
	*
	*	Replacing from includes/ui/ui_lists.inc
	*
	* @param
	* @return
	*******************************************************************/
	function  bank_accounts_list_sql()
	{
		return "SELECT id, bank_account_name, bank_curr_code, inactive FROM ".TB_PREF."bank_accounts";
	}
	/**//***************************************************************
	*
	*
	*	Replacing from includes/ui/ui_lists.inc
	*
	* @param
	* @return
	*******************************************************************/
	function  cash_accounts_list_sql()
	{
		return "SELECT id, bank_account_name, bank_curr_code, inactive FROM ".TB_PREF."bank_accounts WHERE account_ype=".BT_CASH;
	}
}

require_once( '../ksf_modules_common/class.VIEW.php' );

/**//**************************************************************
*
* 	USED in header_table.php (bank_imports)
*
*******************************************************************/
class fa_bank_accounts_VIEW extends HTML_VIEW
{
	protected $name;
	protected $label;
	protected $selected_id;
	protected $submit_on_change;
	protected $spec_option;
	protected $spec_id;
	protected $MODEL;	//!< fa_bank_accounts_MODEL
	protected $all_option;	//!<bool
	protected $sql;
	protected $format;
	protected $async; 	//!<bool
	protected $combo_valfield;
	protected $combo_namefield;
	protected $b_showNoneAll;

	function __construct( $MODEL )
	{
		//display_notification( __FILE__ . "::" . __LINE__ );
		$this->set( "MODEL", $MODEL );
		$this->set( "b_showNoneAll", false );
		//display_notification( __FILE__ . "::" . __LINE__ );
	}
	/**//***************************************************************
	*
	*
	*	Replacing from includes/ui/ui_lists.inc
	*
	* @param
	* @return
	*******************************************************************/
	function bank_accounts_list($name, $selected_id=null, $submit_on_change=false, $spec_option=false)
	{
		display_notification( __FILE__ . "::" . __LINE__ );
		$sql = $this->MODEL->bank_accounts_list_sql(); 
		$this->set( "name", $name );
		$this->set( "selected_id", $selected_id );
		$this->set( "submit_on_change", $submit_on_change );
		$this->set( "spec_option", $spec_option );

		$this->set( "spec_id", '' );
		$this->set( 'format', '_format_add_curr' );
		$this->set( "async", false );
		$this->set( "combo_valfield", 'id' );
		$this->set( "combo_namefield", 'bank_account_name' );
		$sql = $this->MODEL->cash_accounts_list_sql(); 
		$this->set( "sql", $sql );
		$this->combo_input();
		display_notification( __FILE__ . "::" . __LINE__ );
	}
	/**//***************************************************************
	*
	*
	*	Replacing from includes/ui/ui_lists.inc
	*
	* @param NONE expect fields to be set.  Label, Name, selected_id, submit_on_change.
	* @return
	*******************************************************************/

	function bank_accounts_list_cells()
	{
		display_notification( __FILE__ . "::" . __LINE__ );
		if( null !== $this->label )
		{
			$this->label_cell();
		}
		$this->td();
        	echo bank_accounts_list($name, $selected_id, $submit_on_change);
		$this->close_td();
		$this->newline();
		display_notification( __FILE__ . "::" . __LINE__ );
	}
	/**//***************************************************************
	*
	*
	*	Replacing from includes/ui/ui_lists.inc
	*
	* @param
	* @return
	*******************************************************************/

	function bank_accounts_list_row($label, $name, $selected_id=null, $submit_on_change=false)
	{
		display_notification( __FILE__ . "::" . __LINE__ );
		$this->set( "label", $label );
		$this->set( "name", $name );
		display_notification( __FILE__ . "::" . __LINE__ );
		//$this->set( "selected_id", $selected_id );
		display_notification( __FILE__ . "::" . __LINE__ );
		$this->set( "submit_on_change", $submit_on_change );
		display_notification( __FILE__ . "::" . __LINE__ );
		$this->row_label();
		display_notification( __FILE__ . "::" . __LINE__ );
		//$this->set( "label", null );
		display_notification( __FILE__ . "::" . __LINE__ );
        	$this->bank_accounts_list_cells();
		display_notification( __FILE__ . "::" . __LINE__ );
		$this->close_tr();
		display_notification( __FILE__ . "::" . __LINE__ );
		$this->newline();
		display_notification( __FILE__ . "::" . __LINE__ );
	}
	/**//***************************************************************
	*
	*
	* @param NONE
	* @return echo'd string
	*******************************************************************/
	function combo_input()
	{
		display_notification( __FILE__ . "::" . __LINE__ );
        	echo combo_input(	$this->name, 
					$this->selected_id, 
					$this->sql, 
					$this->combo_valfield,
					$this->combo_namefield,
                			array(
                        			'spec_option' => $this->spec_option,
                        			'spec_id' => $this->spec_id,
                        			'format' => $tihs->format,
                        			'select_submit'=> $this->submit_on_change,
                        			'async' => $this->async
                			) 
		);
		display_notification( __FILE__ . "::" . __LINE__ );
	}
	/**//***************************************************************
	*
	*
	*	Replacing from includes/ui/ui_lists.inc
	*
	* @param
	* @return
	*******************************************************************/
	function cash_accounts_list_row($label, $name, $selected_id=null, $submit_on_change=false, $all_option=false)
	{
		display_notification( __FILE__ . "::" . __LINE__ );
		$this->set( "label", $label );
		$this->set( "name", $name );
		$this->set( "selected_id", $selected_id );
		$this->set( "submit_on_change", $submit_on_change );
		$this->set( "spec_option", $all_option );
		$this->set( "spec_id", ALL_TEXT );
		$this->set( 'format', '_format_add_curr' );
		$this->set( "async", true );
		$this->set( "combo_valfield", 'id' );
		$this->set( "combo_namefield", 'bank_account_name' );

		$this->row_label();
		$this->td();
		$sql = $this->MODEL->cash_accounts_list_sql(); 
		$this->set( "sql", $sql );

		$this->combo_input();

		$this->close_td();
		$this->close_tr();
		$this->newline();
		display_notification( __FILE__ . "::" . __LINE__ );
	}

}


class view_bank_transfer
{
	protected $MODEL;

	function __construct( $MODEL )
	{
		$this->set( "MODEL", $MODEL );
	}
	function new_transfer_form()
	{
		start_form(); 
		start_outer_table(TABLESTYLE2); 
		table_section(1);
		//bank_accounts_list_row($label, $name, $selected_id=null, $submit_on_change=false)
		bank_accounts_list_row(_("From Account:"), 'FromBankAccount', null, true); 
		bank_balance_row( $this->MODEL->get( 'FromBankAccount') ); 
		bank_accounts_list_row(_("To Account:"), 'ToBankAccount', null, true); 

		$DatePaid = $this->MODEL->get( 'DatePaid');
		if ( !isset( $DatePaid ) OR ("" == $DatePaid ) )
		{ // init page
                	$this->MODEL->set( 'DatePaid', new_doc_date() );
			$DatePaid = $this->MODEL->get( 'DatePaid');
        	}
		$TransferType = ST_BANKTRANSFER;
		//$TransferType = $this->MODEL->get( 'transfer_type' );
		date_row(_("Transfer Date:"), 'DatePaid', '', true, 0, 0, 0, null, true);
		ref_row(	_("Reference:"), 
				'ref', 
				'', 
				$Refs->get_next(
					$TransferType, 
					null, 
					$DatePaid
				), 
				false, 
				$TransferType, 
				array(	'date' => $DatePaid )
		); 
		table_section(2); 
		$from_currency = get_bank_account_currency($this->MODEL->get( 'FromBankAccount' ) ); 
		$to_currency = get_bank_account_currency($this->MODEL->get( 'ToBankAccount' ) );
		//Is there a reason to NOT show currency indicators on amount/charge?  
		if ($from_currency != "" && $to_currency != "" && $from_currency != $to_currency )
		{
                	amount_row(_("Amount:"), 'amount', null, null, $from_currency);
                	amount_row(_("Bank Charge:"), 'charge', null, null, $from_currency);
                	amount_row(_("Incoming Amount:"), 'target_amount', null, '', $to_currency, 2); 
		} else 
		{
                	amount_row(_("Amount:"), 'amount');
                	amount_row(_("Bank Charge:"), 'charge'); 
		} 
		textarea_row(_("Memo:"), 'memo_', null, 40,4); 
		end_outer_table(1); // outer table 
		if ($trans_no) 
		{ 
			hidden('_trans_no', $trans_no); 
			submit_center('submit', _("Modify Transfer"), true, '', 'default'); 
		} else {
                	submit_center('submit', _("Enter Transfer"), true, '', 'default'); 
		} 
		end_form();
	}
}


?>
