<?php

require_once( 'class.fa_origin.php' );

class fa_gl extends fa_origin
{
	protected $startdate;	//!<date
	protected $enddate;	//!<date
	protected $account;	//!<string
	protected $dimension1;	//!<string
	protected $dimension2;	//!<string
	protected $filter;	//!<
	protected $min_dollar;	//!<float
	protected $max_dollar;	//!<fload
	protected $person_id;	//!<int FK
	protected $matching_gl_entries;	//!<array
	protected $days_spread;	//!<int
	protected $arr_arr;	//!<array
	protected $max_arrs;	//!<int
	protected $accountName;	//!<string accountName from the Transaction we are comparing from Bank Import
	protected $amount;	//!<float
	protected $transactionDC;	//!<char D or C or B.  From Bank Import
	protected $transactionCode;	//!<string	Transaction REF from bank.  Not guaranteed to be GUID.

	function __construct()
	{
		parent::__construct();
		$this->init_vars();
	}
	function set( $field, $value = null, $enforce = true )
	{
		switch( $field )
		{
			case "startdate":
			case "enddate":
				$value = sql2date( $value );
			break;
			case "max_arrs":
				$value = (int) $value;
			break;
		}
		return parent::set( $field, $value, $enforce  );
	}
	function init_vars()
	{
		$this->dimension1 = 0;
		$this->dimension2 = 0;
        	$this->account = null;    //Person is cust/supplier
		$this->person_id = null;    //Person is cust/supplier
		$this->filter = null;    //Person is cust/supplier
        	$this->min_dollar = 0;
        	$this->max_dollar = 0;
		$this->max_arrs = 128;
		$this->init_arr_arr();
		$this->matchscores['startdate'] = 2;
		$this->matchscores['enddate'] = 3;
		$this->matchscores['account'] = 32;
		$this->matchscores['amount'] = 4;
		$this->matchscores['accountName'] = 32;
		$this->matchscores['transactionCode'] = 64;
	}
	function init_arr_arr()
	{
		for( $x = 0; $x <= $this->max_arrs; $x++ )
		{
		        $this->arr_arr[$x] = "";
		}

	}
	/**//********************************************************************
	* Look for existing GL transactions and score their level of match
	*
	* @param none
	* @returns array matching transactions	
	*************************************************************************/
	function find_matching_transactions()
	{
		$this->get_gl_transactions();
		$this->init_arr_arr();

		while($arr = db_fetch($this->matching_gl_entries) )
        	{
                	//If there is only 1 matching row, then the transaction was a split transaction ( 1 or more payments, 1 or more expenses)
                	//      with this Bank Account /CC being part.
                	//If there are 2 matchs, but with +/- Amount, then the Vendor Name should match one entry, and the ACCOUNT should match the other.
                	//If there are more than 2 matching, then we need to (manually) choose the correct match.

                	//var_dump( $arr );

                	$score = 0;
                	$is_invoice = false;

			$score_date = $this->score_matches( "startdate", sql2date( $arr['tran_date'] ) );
			if( $score_date > 0 )
			{
				$score += $score_date;
			}
			else
			{
				$score += $this->score_matches( "enddate", sql2date( $arr['tran_date'] ) );
			}
			$score_acc = $this->score_matches( "Account", $arr['account'] );
			if( $score_acc > 0 ) 
			{
				$score += $score_acc;
			}
			else
			{
				/*	If this was a quick entry import from GnuCash, we have : as chunk dividers.
				 *      One of the chunks is the Merchant.
				 *	      trans_type 0, trans_no, person_id is blank.  account_name set.  memo_ set.
				 *
				 *      HOWEVER, it will also match FA generated entries.
				 *	      Supplier Invoice => trans_type 20, type_no is trans number, person_id == supplier
				 */
				if( isset( $gl_vendor ) )
				{
					unset( $gl_vendor );
				}
				switch( $arr['type'] )
				{
					//TODO: Replace these numbers with DEFINED values
					case 0:
					case ST_JOURNAL:
						//On previously imported GnuCash entries, we had xx:VENDOR:...:...:...:... in the MEMO field
						$exp = explode( ":", $arr['memo_'] );
						if( isset( $exp[1] ) )
							$gl_vendor = $exp[1];
						break;
					case 20:
					case ST_SUPPINVOICE:
						$is_invoice = true;
						$score -= 8;
					//case ST_SUPPPAYMENT:
					case ST_SUPPAYMENT:
					case ST_SUPPCREDIT:
						$supplier = new fa_suppliers();
						$supplier->set( "supplier_id", $arr['person_id'] );
						$ret = $supplier->getById();
						$gl_vendor = $supplier->get( "supp_name" );     //should it be short name?  supp_ref?
						$arr['supp_name'] = $gl_vendor;
						//display_notification( __LINE__ . print_r( $supplier, true ) );
						break;
					case ST_SALESINVOICE:
						$is_invoice = true;
						$score -= 8;
						break;
					case ST_CUSTPAYMENT:
						$score += 8;
						break;
				}	//SWITCH
				if( isset( $gl_vendor ) )
				{
					$score += $this->match_vendor_tokens( $gl_vendor );
				}
			}
			if( isset( $this->transactionDC ) )
			{
				switch( $this->transactionDC )
				{
					case 'B':
					case 'C':
						$scoreamount = 1 * $arr['amount'];
					break;
					case 'D':
						$scoreamount = -1 * $arr['amount'];
					break;
				}
				$score += $this->score_matches( "amount", $scoreamount  );
			}
			if( isset( $this->transactionCode ) )
			{
				//Odds are slim of this matching since we seldom set the transactionCode in older imports
				//      //Transaction Code __might__ match depending on what was imported into Gnu way back when...
				//      //References are not guaranteed Unique between FIs.  However, if the rest here matches....
				$score += $this->score_matches( "transactionCode", $arr['reference']  );
			}
			$arr['score'] = $score;
			$arr['is_invoice'] = $is_invoice;
			
			//Insert the results by score.
		 	$ind = 128 - abs($score);
			while( is_array( $this->arr_arr[$ind] ) )
			{
				$ind--;
			}
			$this->arr_arr[$ind] = $arr;
		}	//WHILE
		//Take the scored array and throw away the empty ones.
		$new_arr = array();
		foreach( $this->arr_arr as $ar )
		{
			if( isset( $ar['tran_date'] ) )
			{
				$new_arr[] = $ar;
			}
		}
		return $new_arr;
	}
	/**//*********************************************************
	*
	* This was put into origin.  Why is it not found?
	*
	*************************************************************/
	function match_tokens( $arr1, $arr2 )
	{
       		$result = array_intersect( $arr1, $arr2 );
       		return count( $result );
	}
	/**//*************************************************
	* Take the Vendor name, and account vendor, and compare
	*
	* 	Compare token by token and calculate percentage amtch
	*
	* @param string the account name to match
	* @returns float percentage match times score weight
	******************************************************/
	function match_vendor_tokens( $gl_vendor )
	{
               $gl_vendor_tokens = explode( " ", $gl_vendor );
                $trz_vendor_tokens = explode( " ", $this->accountName );
                $count_gl = count( $gl_vendor_tokens );
                $count_trz = count( $trz_vendor_tokens );
                //For percentage Name Match, base upon SMALLER number of chunks.
                if( $count_trz <= $count_gl )
                {
                        //var_dump( __LINE__ );
                        if( $count_trz > 0 )
                        {
                                //var_dump( __LINE__ );
                                $percent = 100 / $count_trz;
                        }
                        else
                        {
                                //var_dump( __LINE__ );
                                $percent = 0;
                        }
                }
                else
                {
                        //var_dump( __LINE__ );
                        if( $count_gl > 0 )
                        {
                                //var_dump( __LINE__ );
                                $percent = 100 / $count_gl;
                        }
                        else
                        {
                                //var_dump( __LINE__ );
                                $percent = 0;
                        }
                }
                $matched = $this->match_tokens( $gl_vendor_tokens, $trz_vendor_tokens );
		$weight = $this->matchscores['accountName'];
                $score = round( $matched * $percent * $weight / 100, 0, PHP_ROUND_HALF_EVEN );
//		$score += round( $matched * $percent * $weight / 100, 0, PHP_ROUND_HALF_EVEN );
		return $score;
	}
	function get_gl_transactions()
	{
		$this->matching_gl_entries = get_gl_transactions(
                                        add_days(       $this->startdate, -1 * $this->days_spread ),
                                        add_days(       $this->enddate, 1 * $this->days_spread ),
                                        -1,
                                        $this->account,
                                        $this->dimension1,
                                        $this->dimension2, $this->filter, $this->min_dollar, $this->max_dollar, $this->person_id );
		
	}
	/**//**********************************************************
	* Accept the Bank Import transaction and convert to this class
	*
	* This is used to prep this class to do a search 
	*	Customer E-transfers usually get recorded the day after the "payment date" when recurring invoice, or recorded paid on Quick Invoice
	*
	* @param array Bank Import transaction
	* @param int days lee-way for searching
	* @return none
	****************************************************************/
	function transaction2me( $trz, $spread = 2 )
	{
                $this->set( "min_dollar", $trz['transactionAmount'] );
                $this->set( "max_dollar", $trz['transactionAmount'] );
                $this->set( "amount", $trz['transactionAmount'] );
                $this->set( "transactionDC", $trz['transactionDC'] );
                $this->set( "days_spread", $spread );
                $this->set( "startdate", $trz['valueTimestamp'] );     //Set converts using sql2date
                $this->set( "enddate", $trz['entryTimestamp'] );       //Set converts using sql2date
                $this->set( "accountName", $trz['accountName'] );
                $this->set( "transactionCode", $trz['transactionCode'] );
	}
}	//CLASS
