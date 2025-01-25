<?php
$path_to_root = "../..";
include_once($path_to_root . "/includes/ui/allocation_cart.inc");
//include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/banking.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");
require_once( 'class.origin.php' );

/************************************************************************
When we run a Direct Sales Invoice and use Cash as a payment,
the system automatically logs the amount against the invoice.
	sales/sales_order_entry.php?AddedDI=793&Type=0

Trying to setup a class/functions to do that for our other uses.

Otherwise we are prompted for the payment screen
	sales/customer_payments.php?SInvoice=".$invoice)


***********************************************************************/

class fa_customer_payment extends origin
{
	protected $customer_id;
	protected $alloc;	//!<class allocation
	protected $SInvoice;		//Sales Invoice
	protected $dflt_act;
	protected $bank_account;
	protected $inv;		//!<Invoice details
	protected $BranchID;
	protected $DateBanked;
	protected $amount;
	protected $charge;
	protected $rate;
	protected $bank_amount;
	protected $customer_name;	//!<
	protected $ref;			//!<
	protected $discount;		//!<
	protected $memo_;		//!<
	protected $HoldAccount;		//!<bool
	protected $pymt_discount;	//!<
	protected $trans_type;
	protected $trans_date;		//!<date
	protected $trans_no;		//!<int
	protected $payment_id;

	function __construct( $customer_id = null )
	{
		display_notification( __FILE__ . "::" . __LINE__ );
		if( null !== $customer_id )
		{
			$this->set( "customer_id", $customer_id );
		}
		else if (isset($_GET['customer_id']))
			$this->set( "customer_id", $_GET['customer_id'] );
		else if (isset($_POST['customer_id']))
			$this->set( "customer_id", $_POST['customer_id'] );
		//display_notification( __FILE__ . "::" . __LINE__ );
		$this->alloc = new allocation( ST_CUSTPAYMENT, 0, $this->customer_id );
		//$this->alloc = new allocation( ST_CUSTPAYMENT, 0, $this->customer_id );
		//display_notification( __FILE__ . "::" . __LINE__ );
		if (isset($_GET['SInvoice'])) 
			$this->set( "SInvoice", $_GET['SInvoice'] );
		else if (isset($_POST['SInvoice'])) 
			$this->set( "SInvoice", $_POST['SInvoice'] );
		//display_notification( __FILE__ . "::" . __LINE__ );
		$this->set( "rate", 0 );
		$this->set( "charge", 0 );
		$this->set( "bank_amount", 0 );
		display_notification( __FILE__ . "::" . __LINE__ . " Constructor Exit" );
	}
	function get_default_bank_account()
	{
		$this->inv = get_customer_trans( $this->SInvoice, ST_SALESINVOICE );
		$this->dflt_act = get_default_bank_account( $this->inv['curr_code'] );
		$this->bank_account = $this->dflt_act['id'];
	}
	function parse_inv()
	{
		if($this->inv) {
			$this->alloc->person_id = $this->customer_id = $this->inv['debtor_no'];
			$this->alloc->read();
			$this->BranchID = $this->inv['branch_code'];
			$this->DateBanked = sql2date( $this->inv['tran_date'] );
			foreach($this->alloc->allocs as $line => $trans) 
			{
				if ( $trans->type == ST_SALESINVOICE && $trans->type_no == $this->SInvoice ) 
				{
					$un_allocated = $trans->amount - $trans->amount_allocated;
					if ($un_allocated)
					{
						$this->alloc->allocs[$line]->current_allocated = $un_allocated;
						$this->amount = price_format($un_allocated);
						//$this->amount = $this->amount'.$line] = price_format($un_allocated);
					}
					break;
				}
			}
			//unset($this->inv);
		} else
			display_error(_("Invalid sales invoice number."));
	}
	/**//***********************************************************************
	*
	****************************************************************************/
	function setX( $field, $value = null, $enforce = true )
	{
		display_notification( __FILE__ . "::" . __LINE__ );
		switch( $field )
		{
			case 'DateBanked':
				if( !is_date( $this->DateBanked ) )
					throw new Exception(_("The entered date is invalid. Please enter a valid date for the payment."));
				break;
			case 'charge':
				break;
			case 'customer_id':
				if( null !== $value )
				{
					if( ! isset( $this->alloc ) )
					{
						var_dump( $this->alloc );
					}
					else
					{
						$this->alloc->person_id = $value;
        					$this->alloc->read();
					}
				}
				break;
			case 'payment_id':
				if( null !== $value )
				{
					if( ! isset( $this->alloc ) )
					{
						var_dump( $this->alloc );
					}
					else
					{
						$this->alloc->trans_no = $value;
        					$this->alloc->read();
					}
				}
				break;
			case 'trans_type':
				if( null !== $value )
				{
					if( ! isset( $this->alloc ) )
					{
						var_dump( $this->alloc );
					}
					else
					{
						$this->alloc->trans_type = $value;
					}
				}
				break;
		}
		parent::set( $field, $value, $enforce );
		//On FA code, if the person ID is set on an alloc, the alloc is then ->read.
		//If we are setting (changing) a person id, we should fix the alloc too
		display_notification( __FILE__ . "::" . __LINE__ );
	}
	/**//******************************************************
	* Set the DateBanked
	*
	* @param NONE
	* @returns none
	***********************************************************/
	function newBankedDate()
	{
		$this->DateBanked = new_doc_date();
		if (!is_date_in_fiscalyear($this->DateBanked)) 
		{
			$this->DateBanked = end_fiscalyear();
		}
	}
	/**//*************************************
	* Can we process the payment
	*
	* Need to doublecheck the $this->get etc fcns for what they do
	*	They may bypass variables using globals/POST/GET
	*
	* @param NONE
	* @return bool
	**********************************************/
	function can_process()
	{
		global $Refs;

		if( !isset( $this->trans_no ) )
		{
			throw new Exception(_("The Transaction Number isn't set."));
			return false;
		}
	
		if ( !isset ( $this->DateBanked ) ) {
			throw new Exception(_("The entered date is invalid. Please enter a valid date for the payment."));
			return false;
		} elseif ( ! is_date_in_fiscalyear( $this->DateBanked ) ) 
		{
			throw new Exception(_("The entered date is out of fiscal year or is closed for further data entry."));
			return false;
		}
	
		if ( ! check_reference( $this->ref, ST_CUSTPAYMENT, @$this->trans_no) ) 
		{
			return false;
		}
	
		if ( ! check_num('amount', 0) ) 
		{
			throw new Exception(_("The entered amount is invalid or negative and cannot be processed."));
			return false;
		}
	
		if (isset($this->charge) && !check_num('charge', 0)) {
			throw new Exception(_("The entered amount is invalid or negative and cannot be processed."));
			return false;
		}
		if (isset($this->charge) && $this->get('charge') > 0) {
			$charge_acct = get_bank_charge_account( $this->bank_account );
			if (get_gl_account($charge_acct) == false) {
				throw new Exception(_("The Bank Charge Account has not been set in System and General GL Setup."));
				return false;
			}	
		}
	
		if (@$this->discount == "") 
		{
			$this->discount = 0;
		}
	
		if ( ! check_num('discount') ) 
		{
			throw new Exception(_("The entered discount is not a valid number."));
			return false;
		}
	
		if ($this->get('amount') <= 0) {
			throw new Exception(_("The balance of the amount and discount is zero or negative. Please enter valid amounts."));
			return false;
		}
	
		if (isset($this->bank_amount) && $this->get('bank_amount')<=0)
		{
			throw new Exception(_("The entered payment amount is zero or negative."));
			return false;
		}
	
		if (!db_has_currency_rates(get_customer_currency($this->customer_id), $this->DateBanked, true))
			return false;
	
		$this->alloc->amount = $this->get('amount');
	
		if (isset($_POST["TotalNumberOfAllocs"]))
			return check_allocations();
		else
			return true;
	}
	function get_customer_trans()
	{
		return $this->get_customer_payment_trans();
	}
	/**//*****************************************************************************
	* Get a Customer Payment transaction details including allocations.
	*
	* @param none uses internal variables
	* @return none sets internal variables
	**********************************************************************************/
	function get_customer_payment_trans()
	{
		if ( isset( $this->trans_no ) && $this->trans_no > 0 )
		{
			$new = 0;
			$myrow = get_customer_trans( $this->trans_no, ST_CUSTPAYMENT );
			$this->customer_id = $myrow["debtor_no"];
			$this->customer_name = $myrow["DebtorName"];
			$this->BranchID = $myrow["branch_code"];
			$this->bank_account = $myrow["bank_act"];
			$this->ref =  $myrow["reference"];
			$charge = get_cust_bank_charge(ST_CUSTPAYMENT, $this->trans_no);
			$this->charge =  price_format($charge);
			$this->DateBanked =  sql2date($myrow['tran_date']);
			$this->amount = price_format( $myrow['Total'] - $myrow['ov_discount'] );
			$this->bank_amount = price_format( $myrow['bank_amount'] + $charge );
			$this->discount = price_format( $myrow['ov_discount'] );
			$this->memo_ = get_comments_string(ST_CUSTPAYMENT,$this->trans_no);
		
			//Prepare allocation cart 
			$this->alloc = new allocation(ST_CUSTPAYMENT,$this->trans_no);
		}
	}
	/**//*******************************************************************
	*
	*	Based on read_customer_data()  from sales/customer_payments.php  
	* @since 20240826
	*
	*****************************************************************************************/
	function read_customer_data()
	{
		global $Refs;

		$myrow = get_customer_habit($this->customer_id);

		$this->HoldAccount = $myrow["dissallow_invoices"];
		$this->pymt_discount = $myrow["pymt_discount"];
		// To support Edit feature
		// If page is called first time and New entry fetch the nex reference number
		if (!$this->alloc->trans_no && !isset($this->charge)) 
		{
			$this->ref = $Refs->get_next( 	ST_CUSTPAYMENT, 
							null, 
							array(
								'customer' => $this->get('customer_id'), 
								'date' => $this->get('DateBanked')
							)
						);
		}
	}

	/**//*******************************************************************
	*
	*	Based on  INLINE  from sales/customer_payments.php  
	* @since 20240826
	*
	*****************************************************************************************/
	function addPaymentItem()
	{
		if (get_post('AddPaymentItem') && can_process()) {
		
		        new_doc_date($_POST['DateBanked']);
		
		        $new_pmt = !$_SESSION['alloc']->trans_no;
		        //Chaitanya : 13-OCT-2011 - To support Edit feature
		        $this->payment_id = write_customer_payment($this->alloc->trans_no, $_POST['customer_id'], $_POST['BranchID'],
		                $_POST['bank_account'], $_POST['DateBanked'], $_POST['ref'],
		                input_num('amount'), input_num('discount'), $_POST['memo_'], 0, input_num('charge'), input_num('bank_amount', input_num('amount')));
		
			$this->write_allocation();
		
		        meta_forward($_SERVER['PHP_SELF'], $new_pmt ? "AddedID=$this->payment_id" : "UpdatedID=$this->payment_id");
		}
	}
	/**//**
	* Write the allocation
	*
	* @param none
	* @return none
	******/
	function write_allocation()
	{
		$this->alloc->trans_no = $this->payment_id;
		$this->alloc->write();
	}
	/**//**********************************************************
	* Display allocatable invoices for a customer in a table
	*
	*	Modified from similar in:
	*	includes/ui/allocation_cart.inc
	*
	* @since 20240826
	* @param none	depends on ->allocs
	* @return none
	******************************************************************/
	function show_allocatable()
	{
		$cart = $this->alloc;
	        if (count($cart->allocs))
	        {
	                //display_heading(sprintf(_("Amounts in %s:"), $cart->person_curr));
	                start_table(TABLESTYLE, "width='80%'");
	                $th = array(
					_("#"), 
					_("Date"), 
					_("Invoice Amount"),
	                        	_("Other Payments"), 
					_("Outstanding Balance") 
			);
	
	                table_header($th);
	
			//id is just an index of the allocs array
	                foreach ($cart->allocs as $id => $alloc_item)
	                {
	                    if (floatcmp( abs( $alloc_item->amount ), $alloc_item->amount_allocated ) )
	                    {
	                                alt_table_row_color($k);
	                                label_cell(get_trans_view_str($alloc_item->type, $alloc_item->type_no));
	                        	label_cell($alloc_item->date_, "align=right");
	                        	amount_cell(abs($alloc_item->amount));
	                                amount_cell($alloc_item->amount_allocated);
	
	                        	//$_POST['amount' . $id] = price_format($alloc_item->current_allocated);
	                        	$un_allocated = round((abs($alloc_item->amount) - $alloc_item->amount_allocated), 6);
	                        	amount_cell($un_allocated);
	                        	//amount_cell($un_allocated, false,'', 'maxval'.$id);
	                               end_row();
	                        }
	                }
	                end_table(1);
	                //end_table();
	        }
	        //hidden('TotalNumberOfAllocs', count($cart->allocs));

	}
	/**//************************************************************************************
	* Get allocatable indexes.  Instead of displaying them, return list of indexes.
	*
	* @returns array aloocatable invoices with details
	****************************************************************************************/
	function get_alloc_details()
	{
		$cart = $this->alloc;
		$res = array();
	        if (count($cart->allocs))
	        {
			//id is just an index of the allocs array
	                foreach ($cart->allocs as $id => $alloc_item)
	                {
	                    if (floatcmp( abs( $alloc_item->amount ), $alloc_item->amount_allocated ) )
	                    {
					$trans_no = $alloc_item->type;
					$type_no = $alloc_item->type_no;
					$trans_date = $alloc_item->date_;
					$invoice_amount = abs($alloc_item->amount);
					$payments = $alloc_item->amount_allocated;
	                        	//	//$_POST['amount' . $id] = price_format($alloc_item->current_allocated);
	                        	$unallocated = round((abs($alloc_item->amount) - $alloc_item->amount_allocated), 6);

					$res[ $trans_no ]['trans_no'] = $trans_no;
					$res[ $trans_no ]['type_no'] = $type_no;
					$res[ $trans_no ]['trans_date'] = $trans_date;
					$res[ $trans_no ]['invoice_amount'] = $invoice_amount;
					$res[ $trans_no ]['payments'] = $payments;
					$res[ $trans_no ]['unallocated'] = $unallocated;
	                        }
	                }
	        }
		return $res;
	}

	/**//************************************************************************************
	* Get allocatable indexes.  Instead of displaying them, return list of indexes.
	*
	* @returns array aloocatable invoices with details
	****************************************************************************************/
	function get_alloc_list()
	{

	        $ret = combo_input($name, $selected_id, $sql, 'branch_code', 'branch_ref',
	        array(
	                'where' => $where,
	                'order' => array('branch_ref'),
	                'spec_option' => $spec_option === true ? _('All branches') : $spec_option,
	                'spec_id' => ALL_TEXT,
	                'select_submit'=> $submit_on_change,
	                'sel_hint' => _('Select customer branch'),
	                'editlink' => $editkey ? add_edit_combo('branch') : false
	        ), "branch" );
	        return $ret;
	}

	/**//***************************************************
	* Take care of adding a customer payment
	*
	*       sales/customer_payment.php does a whole bunch of things:
	*               runs can_process (data checks)
	*               makes sure the allocaiton doesn't have a transaction number (new payment)
	*               runs write_customer_payment
	*               updates the allocation->trans_no with the resulting payment number
	*               Writes the allocation
	*               Unsets the allocation
	*
	*       FA:     write_customer_payment($_SESSION['alloc']->trans_no, $_POST['customer_id'], $_POST['BranchID'],
	*                       $_POST['bank_account'], $_POST['DateBanked'], $_POST['ref'],
	*                       input_num('amount'), input_num('discount'), $_POST['memo_'], 0, input_num('charge'), input_num('bank_amount', input_num('amount')));
	*
	*       The function below was identical to the FA write_customer_payment (sales/includes/db/payment_db.inc)
	*		Now it's been converted to use CLASS variables.
	*
	*       Bank Deposit does NOT have an allocation table on the edit screen.
	*               Bank Deposits can be allocated on the Allocation screen.
	*       Customer Payment DOES have the allocation table on the edit screen.
	*
	* @returns int payment number (for allocations)
	**************************************************************/
	function write_customer_payment()
	{
	        global $Refs;

		if( ! isset( $this->trans_no ) )
		{
			throw new Excpetion( "Transaction number not set.  Can't write customer payment! ", KSF_FIELD_NOT_SET );
		}
		if( ! isset( $this->customer_id ) )
		{
			throw new Excpetion( "customer_id not set.  Can't write customer payment! ", KSF_FIELD_NOT_SET );
		}
		if( ! isset( $this->branch_id ) )
		{
			throw new Excpetion( "branch_id not set.  Can't write customer payment! ", KSF_FIELD_NOT_SET );
		}
		if( ! isset( $this->bank_account ) )
		{
			throw new Excpetion( "bank_account not set.  Can't write customer payment! ", KSF_FIELD_NOT_SET );
		}
		if( ! isset( $this->date ) )
		{
			throw new Excpetion( "date not set.  Can't write customer payment! ", KSF_FIELD_NOT_SET );
		}
		if( ! isset( $this->ref ) )
		{
			throw new Excpetion( "ref not set.  Can't write customer payment! ", KSF_FIELD_NOT_SET );
		}
		if( ! isset( $this->amount ) )
		{
			throw new Excpetion( "amount not set.  Can't write customer payment! ", KSF_FIELD_NOT_SET );
		}
		if( ! isset( $this->discount ) )
		{
			throw new Excpetion( "discount not set.  Can't write customer payment! ", KSF_FIELD_NOT_SET );
		}
		if( ! isset( $this->memo_ ) )
		{
			throw new Excpetion( "memo_ not set.  Can't write customer payment! ", KSF_FIELD_NOT_SET );
		}
		if( ! isset( $this->rate ) )
		{
			throw new Excpetion( "rate not set.  Can't write customer payment! ", KSF_FIELD_NOT_SET );
		}
		if( ! isset( $this->charge ) )
		{
			throw new Excpetion( "charge not set.  Can't write customer payment! ", KSF_FIELD_NOT_SET );
		}
		if( ! isset( $this->bank_amount ) )
		{
			throw new Excpetion( "bank_amount not set.  Can't write customer payment! ", KSF_FIELD_NOT_SET );
		}
		if( ! isset( $this->trans_type ) )
		{
			throw new Excpetion( "trans_type not set.  Can't write customer payment! ", KSF_FIELD_NOT_SET );
		}
	
	
	        begin_transaction();
			/** No longer possible as we don't pass in args
	                *$args = func_get_args(); while (count($args) < 12) $args[] = 0;
	                *$args = (object)array_combine(array('$this->trans_no', 'customer_id', 'branch_id', 'bank_account',
	                *        'date_', 'ref', 'amount', 'discount', 'memo_','rate','charge', 'bank_amount'), $args);
	                *hook_db_prewrite($args, $this->trans_type);
			**/
	                hook_db_prewrite($this, $this->trans_type);
	
	                $company_record = get_company_prefs();
	
	                if ($this->trans_no != 0) {
	                  delete_comments($this->trans_type, $this->trans_no);
	                  void_bank_trans($this->trans_type, $this->trans_no, true);
/**		void bank trans calls voif_gl_trans and void_cust_allocations, among others
		SEE Mantis 3108 for code from function
	                  void_gl_trans($this->trans_type, $this->trans_no, true);
	                  void_cust_allocations($this->trans_type, $this->trans_no, $this->trans_date);
*/
	                }
	
	                $bank = get_bank_account($this->bank_account);
	
	                if ( ! $this->bank_amount)      // backward compatibility workaround
	                {
	                        if( ! $this->rate)
	                                $this->rate = get_exchange_rate_from_to(get_customer_currency($this->customer_id), $bank['bank_curr_code'], $this->trans_date );
	                        $this->bank_amount = $this->amount/$this->rate;
	                }
	                $this->payment_id = $payment_no = write_customer_trans($this->trans_type, $this->trans_no, $this->customer_id, $this->branch_id, $this->trans_date, $this->ref, $this->amount, $this->discount);
	
	                $bank_gl_account = get_bank_gl_account($this->bank_account);
	
	                $total = 0;
	
	                /* Bank account entry first */
	                $total += add_gl_trans($this->trans_type, $this->payment_id, $this->trans_date, $bank_gl_account, 0, 0, '', ($this->bank_amount - $this->charge),  $bank['bank_curr_code'], PT_CUSTOMER, $this->customer_id);
	
	                if ($this->branch_id != ANY_NUMERIC) {
	                        $branch_data = get_branch_accounts($this->branch_id);
	                        $debtors_account = $branch_data["receivables_account"];
	                        $this->discount_account = $branch_data["payment_discount_account"];
	                } else {
	                        $debtors_account = $company_record["debtors_act"];
	                        $this->discount_account = $company_record["default_prompt_payment_act"];
	                }
	
	                if (($this->discount + $this->amount) != 0) {
	                /* Now Credit Debtors account with receipts + discounts */
	                $total += add_gl_trans_customer($this->trans_type, $this->payment_id, $this->trans_date,
	                        $debtors_account, 0, 0, -($this->discount + $this->amount), $this->customer_id,
	                        "Cannot insert a GL transaction for the debtors account credit");
	                }
	
	                if ($this->discount != 0)     {
	                        /* Now Debit this->discount account with discounts allowed*/
	                        $total += add_gl_trans_customer($this->trans_type, $this->payment_id, $this->trans_date,
	                                $this->discount_account, 0, 0, $this->discount, $this->customer_id,
	                                "Cannot insert a GL transaction for the payment discount debit");
	                }
	
	                if ($this->charge != 0)       {
	                        /* Now Debit bank charge account with charges */
	                        $charge_act = get_company_pref('bank_charge_act');
	                        $total += add_gl_trans($this->trans_type, $this->payment_id, $this->trans_date, $charge_act, 0, 0, '',
	                                $this->charge, $bank['bank_curr_code'], PT_CUSTOMER,  $this->customer_id);
	                }
	
	
	                /*Post a balance post if $total != 0 due to variance in AR and bank posted values*/
	                if ($total != 0)
	                {
	                        $variance_act = get_company_pref('exchange_diff_act');
	                        add_gl_trans($this->trans_type, $this->payment_id, $this->trans_date,   $variance_act, 0, 0, '',
	                                -$total, null, PT_CUSTOMER,  $this->customer_id);
	                }
	
	                /*now enter the bank_trans entry */
	                add_bank_trans($this->trans_type, $this->payment_id, $this->bank_account, $this->ref,
	                        $this->trans_date, $this->bank_amount - $this->charge, PT_CUSTOMER, $this->customer_id);
	
	                add_comments($this->trans_type, $this->payment_id, $this->trans_date, $this->memo_);
	
	                //SC: that would be the change!!!
	                $Refs->save($this->trans_type, $this->payment_id, $this->ref);

/**
Log transaction code
$cart = new items_cart($trans_type);
$cart->add_gl_item( '0000', 0, 0, 0.01, 'TransRef::'.$trz['transactionCode'], "Trans Ref");
$cart->add_gl_item( '0000', 0, 0, -0.01, 'TransRef::'.$trz['transactionCode'], "Trans Ref");
*/
	                hook_db_postwrite($this, $this->trans_type);
	        commit_transaction();
		display_notification( __FILE__ . "::" . __LINE__ . "::" . "Deposit ID: " . $this->payment_id );
	        return $this->payment_id;
	}

	/**//******************************************************************************
	* From sales/db/sales_invoice_db.inc
	*
	* Write the invoice and payments
	*
	* @param object CART CLASS (sales)
	***********************************************************************************/
	function write_sales_invoice( &$invoice )
	{
		throw new Exception( "This function is for coding other chunks not for use!" );
		 $invoice_no = write_customer_trans(ST_SALESINVOICE, $trans_no, $invoice->customer_id,
	                $invoice->Branch, $date_, $invoice->reference, $items_total, 0,
	                $items_added_tax, $invoice->freight_cost, $freight_added_tax,
	                $invoice->sales_type, $sales_order, $invoice->ship_via,
	                $invoice->due_date, 0, 0, $invoice->dimension_id,
	                $invoice->dimension2_id, $invoice->payment, $invoice->tax_included, $invoice->prep_amount);
		//Then for each line item, get various prices and calls write_customer_trans_detail_item(...)
		//Then gets gl accounts and dimensions for the item
    		$total += add_gl_trans_customer(ST_SALESINVOICE, $invoice_no, $date_, $sales_account, $dim, $dim2,
                                        -$line_taxfree_price*$prepaid_factor,
                                        $invoice->customer_id, "The sales price GL posting could not be inserted");
		if ($invoice_line->discount_percent != 0) 
		{
                	$total += add_gl_trans_customer(ST_SALESINVOICE, $invoice_no, $date_,
                                                $branch_data["sales_discount_account"], $dim, $dim2,
                                                ($line_taxfree_price * $invoice_line->discount_percent)*$prepaid_factor,
                                                $invoice->customer_id, "The sales discount GL posting could not be inserted");
               } /*end of if discount !=0 */
		 if (($items_total + $charge_shipping) != 0) {
                $total += add_gl_trans_customer(ST_SALESINVOICE, $invoice_no, $date_, $branch_data["receivables_account"], 0, 0,
                        ($items_total + $charge_shipping + $items_added_tax + $freight_added_tax)*$prepaid_factor,
                        $invoice->customer_id, "The total debtor GL posting could not be inserted");
		}
		$to_allocate = ($items_total + $charge_shipping + $items_added_tax + $freight_added_tax);
	      if ($charge_shipping != 0) {
			$total += add_gl_trans_customer(ST_SALESINVOICE, $invoice_no, $date_, $company_data["freight_act"], 0, 0,
				-$invoice->get_tax_free_shipping()*$prepaid_factor, $invoice->customer_id,
				"The freight GL posting could not be inserted");
		}
		// post all taxes
		foreach ($taxes as $taxitem) {
			if ($taxitem['Net'] != 0) {
				$ex_rate = get_exchange_rate_from_home_currency(get_customer_currency($invoice->customer_id), $date_);
				add_trans_tax_details(ST_SALESINVOICE, $invoice_no, $taxitem['tax_type_id'],
					$taxitem['rate'], $invoice->tax_included, $prepaid_factor*$taxitem['Value'],
					 $taxitem['Net'], $ex_rate, $date_, $invoice->reference, TR_OUTPUT);
				if (isset($taxitem['sales_gl_code']))
					$total += add_gl_trans_customer(ST_SALESINVOICE, $invoice_no, $date_, $taxitem['sales_gl_code'], 0, 0,
						(-$taxitem['Value'])*$prepaid_factor, $invoice->customer_id,
						"A tax GL posting could not be inserted");
			}
		}
		/*Post a balance post if $total != 0 */
		add_gl_balance(ST_SALESINVOICE, $invoice_no, $date_, -$total, PT_CUSTOMER, $invoice->customer_id);
	
		add_comments(ST_SALESINVOICE, $invoice_no, $date_, $invoice->Comments);
	
		if ($trans_no == 0) {
			$Refs->save(ST_SALESINVOICE, $invoice_no, $invoice->reference, null, $invoice->fixed_asset);
			if ($invoice->payment_terms['cash_sale'] && $invoice->pos['pos_account']) {
				$amount = $items_total + $items_added_tax + $invoice->freight_cost
					+ $freight_added_tax;
	
				// to use debtors.pmt_discount on cash sale:
				// extend invoice entry page with final amount after discount
				// and change line below.
				$discount = 0; // $invoice->cash_discount*$amount;
				$pmtno = write_customer_payment(0, $invoice->customer_id,
					$invoice->Branch, $invoice->pos['pos_account'], $date_,
					$Refs->get_next(ST_CUSTPAYMENT, null, array('customer' => $invoice->customer_id,
						'branch' => $invoice->Branch, 'date' => $date_)),
					$amount-$discount, $discount,
					_('Cash invoice').' '.$invoice_no);
				add_cust_allocation($amount, ST_CUSTPAYMENT, $pmtno, ST_SALESINVOICE, $invoice_no, $invoice->customer_id, $date_);
	
				update_debtor_trans_allocation(ST_SALESINVOICE, $invoice_no, $invoice->customer_id);
				update_debtor_trans_allocation(ST_CUSTPAYMENT, $pmtno, $invoice->customer_id);
			}
		}
		reallocate_payments($invoice_no, ST_SALESINVOICE, $date_, $to_allocate, $allocs);
		hook_db_postwrite($invoice, ST_SALESINVOICE);
	
		commit_transaction();
	
	}

	function write_customer_payment2()
	{
		try
		{
			if( $this->can_process() )
			{
				new_doc_date( $this->DateBanked );
		
				$payment_no = write_customer_payment( 	$this->trans_no, 
									$this->customer_id, 
									$this->BranchID,
									$this->bank_account, 
									$this->DateBanked, 
									$this->ref,
									$this->get('amount'), 	
									$this->get('discount'), 
									$this->memo_, 
									0, 
									$this->get('charge'), 
									$this->get('bank_amount'), 
									$this->get('amount')
								);
				$this->alloc->trans_no = $payment_no;
				$this->alloc->write();
			}
			else
			{
				throw new Exception( "Couldn't process payment" );
			}
		}
		catch( Exception $e )
		{
			throw $e;
		}
	}
	/**//*******************************************************************
	*
	*	Based on  get_allocatable_from_cust_transactions  from includes/db/custalloc_db.inc
	* @since 20240826
	*
	*****************************************************************************************/
	function get_allocatable_from_cust_transactions($customer_id, $trans_no=null, $type=null)
	{
	
	        $sql = "SELECT
	                trans.type,
	                trans.trans_no,
	                trans.reference,
	                trans.tran_date,
	                debtor.name AS DebtorName,
	                debtor.curr_code,
	                ov_amount+ov_gst+ov_freight+ov_freight_tax+ov_discount AS Total,
	                trans.alloc,
	                trans.due_date,
	                debtor.address,
	                trans.version,
	                amt,
	                trans.debtor_no
	         FROM  ".TB_PREF."debtor_trans as trans,"
	                        .TB_PREF."debtors_master as debtor,"
	                        .TB_PREF."cust_allocations as alloc
	         WHERE trans.debtor_no=debtor.debtor_no
	                        AND trans.trans_no = alloc.trans_no_from
	                        AND trans.type = alloc.trans_type_from
	                        AND trans.debtor_no = alloc.person_id";
	
	        if ($trans_no != null and $type != null)
	        {
	                $sql .= " AND alloc.trans_no_to=".db_escape($trans_no)."
	                                  AND alloc.trans_type_to=".db_escape($type);
	        }
	        else
	        {
	                $sql .= " AND round(ov_amount+ov_gst+ov_freight+ov_freight_tax+ov_discount-alloc,6) > 0
	                        AND trans.type NOT IN (".implode(',',array(ST_CUSTPAYMENT,ST_BANKDEPOSIT,ST_CUSTCREDIT,ST_CUSTDELIVERY)).")";
	                $sql .= " GROUP BY type, trans_no";
	        }
	
	        if($customer_id)
	                $sql .= " AND trans.debtor_no=".db_escape($customer_id);

		if( isset( $this->trans_date ) )
		{
	                $sql .= " AND trans.tran_date<=".db_escape($this->trans_date);
		}
	
	        return db_query($sql." ORDER BY trans_no", "Cannot retreive alloc to transactions");
	}

	/**//*******************************************************************
	*
	*	Based on get_sql_for_customer_allocation_inquiry  from includes/db/custalloc_db.inc
	* @since 20240826
	*
	*****************************************************************************************/
	function get_allocatable()
	{
	        $data_after = date2sql($from);
	        $date_to = date2sql($to);
	
	        $sql = "SELECT
		                trans.type,
		                trans.trans_no,
		                trans.reference,
		                trans.order_,
		                trans.tran_date,
		                trans.due_date,
		                debtor.name,
		                debtor.curr_code,
		        	(trans.ov_amount + trans.ov_gst + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount)     AS TotalAmount,
		                trans.alloc AS Allocated,
		                ((trans.type = ".ST_SALESINVOICE.") AND trans.due_date < '" . date2sql(Today()) . "') AS OverDue,
		                trans.debtor_no
	        FROM "
	                        .TB_PREF."debtor_trans as trans, "
	                        .TB_PREF."debtors_master as debtor
	        WHERE 		
				debtor.debtor_no = trans.debtor_no
	                        AND (trans.ov_amount + trans.ov_gst + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount != 0)";
		if( isset( $date_after ) )
		{
			$sql .= " AND trans.tran_date >= '$data_after'";
		}
		if( isset( $date_to ) )
		{
	               $sql .= " AND trans.tran_date <= '$date_to'";
		}
	
	        if ($customer != ALL_TEXT)
	                $sql .= " AND trans.debtor_no = ".db_escape($customer);
	
	        if (isset($filterType) && $filterType != ALL_TEXT)
	        {
	                if ($filterType == '1' || $filterType == '2')
	                {
	                        $sql .= " AND trans.type = ".ST_SALESINVOICE." ";
	                }
	                elseif ($filterType == '3')
	                {
	                        $sql .= " AND trans.type = " . ST_CUSTPAYMENT;
	                }
	                elseif ($filterType == '4')
	                {
	                        $sql .= " AND trans.type = ".ST_CUSTCREDIT." ";
	                }
	
	        if ($filterType == '2')
	        {
	                $today =  date2sql(Today());
	                $sql .= " AND trans.due_date < '$today'
	                                AND (round(abs(trans.ov_amount + "
	                                ."trans.ov_gst + trans.ov_freight + "
	                                ."trans.ov_freight_tax + trans.ov_discount) - trans.alloc,6) > 0) ";
	        }
	        }
	        else
	        {
	            $sql .= " AND trans.type <> ".ST_CUSTDELIVERY." ";
	        }
	
	
	        if (!$settled)
	        {
	                $sql .= " AND (round(IF(trans.prep_amount,trans.prep_amount, abs(trans.ov_amount + trans.ov_gst + "
	                ."trans.ov_freight + trans.ov_freight_tax + "
	                ."trans.ov_discount)) - trans.alloc,6) != 0) ";
	        }
	        return $sql;

	}
}
//----------------------------------------------------------------------------------------

/*
	if (isset($_GET['SInvoice'])) {
		//  get date and supplier
		$cp = new fa_customer_payment();
		$cp->get_default_bank_account();
		$cp->parse_inv();
	}

	if (!isset($_POST['DateBanked'])) {
		$cp->newDateBanked();
	}


	if (get_post('AddPaymentItem') ) {
	
		$new_pmt = !$_SESSION['alloc']->trans_no;
		$payment_no = $cp->write_customer_payment();
		unset($_SESSION['alloc']);
		meta_forward($_SERVER['PHP_SELF'], $new_pmt ? "AddedID=$payment_no" : "UpdatedID=$payment_no");
	}


	// To support Edit feature
	if (isset($_GET['trans_no']) && $_GET['trans_no'] > 0 )
	{
		$cp = new fa_customer_payment();
		$cp->set( "trans_no", $_GET['trans_no'] );
		$cp->get_customer_trans();
		$new = 0;
	}

	//bank_accounts_list_row(_("Into Bank Account:"), 'bank_account', null, true);
	
	//if ($new)
	//	customer_list_row(_("From Customer:"), 'customer_id', null, false, true);
	//else {
	//	label_cells(_("From Customer:"), $_SESSION['alloc']->person_name, "class='label'");
	//}
	
	
	//Part of the form showing customer transactions etc.
	$cp->read_customer_data();
	
	//$display_discount_percent = percent_format($_POST['pymt_discount']*100) . "%";
	
	
	//date_row(_("Date of Deposit:"), 'DateBanked', '', true, 0, 0, 0, null, true);
	
	//ref_row(_("Reference:"), 'ref','' , null, '', ST_CUSTPAYMENT);
	
	
	//$comp_currency = get_company_currency();
	//$cust_currency = $_SESSION['alloc']->set_person($_POST['customer_id'], PT_CUSTOMER);
	//if (!$cust_currency)
	//	$cust_currency = $comp_currency;
	//$_SESSION['alloc']->currency = $bank_currency = get_bank_account_currency($_POST['bank_account']);
	
	//if ($cust_currency != $bank_currency)
	//{
	//	amount_row(_("Payment Amount:"), 'bank_amount', null, '', $bank_currency);
	//}
	
	//amount_row(_("Bank Charge:"), 'charge', null, '', $bank_currency);
	
	
	//show_allocatable(false);
	
	
	//label_row(_("Customer prompt payment discount :"), $display_discount_percent);
	//amount_row(_("Amount of Discount:"), 'discount', null, '', $cust_currency);
	//amount_row(_("Amount:"), 'amount', null, '', $cust_currency);

*/
