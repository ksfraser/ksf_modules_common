<?php

require_once( 'class.fa_origin.php' );

class ksf_ui_class extends fa_origin
{
	protected $section_title;
	protected $instruction1;
	protected $instruction2;
	protected $instruction3;
	protected $instruction4;
	protected $instruction5;
	protected $instruction6;
	protected $instruction7;
	protected $instruction8;
	protected $instruction9;
	protected $hidden1;
	protected $hidden2;
	protected $hidden3;
	protected $hidden4;
	protected $hidden5;
	protected $hidden6;
	function __construct() {}
	function instructions_table()
	{
		start_table(TABLESTYLE);
		table_section_title(_( $this->section_title ),2);
		if( isset( $this->instruction1 ) )
			label_row(_( $this->instruction1 ), "",'colspan=1');
		if( isset( $this->instruction2 ) )
			label_row(_( $this->instruction2 ), "",'colspan=1');
		if( isset( $this->instruction3 ) )
			label_row(_( $this->instruction3 ), "",'colspan=1');
		if( isset( $this->instruction4 ) )
			label_row(_( $this->instruction4 ), "",'colspan=1');
		if( isset( $this->instruction5 ) )
			label_row(_( $this->instruction5 ), "",'colspan=1');
		if( isset( $this->instruction6 ) )
			label_row(_( $this->instruction6 ), "",'colspan=1');
		if( isset( $this->instruction7 ) )
			label_row(_( $this->instruction7 ), "",'colspan=1');
		if( isset( $this->instruction8 ) )
			label_row(_( $this->instruction8 ), "",'colspan=1');
		if( isset( $this->instruction9 ) )
			label_row(_( $this->instruction9 ), "",'colspan=1');
		if( isset( $this->hidden1 ) )
			hidden( $this->hidden1['name'], $this->hidden1['value'] );
		if( isset( $this->hidden2 ) )
			hidden( $this->hidden2['name'], $this->hidden2['value'] );
		if( isset( $this->hidden3 ) )
			hidden( $this->hidden3['name'], $this->hidden3['value'] );
		if( isset( $this->hidden4 ) )
			hidden( $this->hidden4['name'], $this->hidden4['value'] );
		if( isset( $this->hidden5 ) )
			hidden( $this->hidden5['name'], $this->hidden5['value'] );
		if( isset( $this->hidden6 ) )
			hidden( $this->hidden6['name'], $this->hidden6['value'] );
		end_table(1);
	}
	/*******************************//**
	 * Start a form.
	 *
	 * used params:
	 * b_multi - is this a multipart encrytp
	 * dummy - 
	 * action - can be NULL which means use the same page (target)
	 * name - form name
	 *
	 * FA creates a POST form.
	 * *********************************/
	function form_start( $b_multi = false, $b_dummy = false, $action = "", $name = "" )
	{
		start_form( $b_multi, $b_dummy, $action, $name );
	}
	function form_end()
	{
		end_form();
	}
	function div_start( $div_name )
	{
		div_start( $div_name );
	}
	function div_end()
	{
		div_end();
	}
	function table_start( $style = null, $extra="", $cellpadding='2', $cellspacing='0' )
	{	
		//$class=false, $extra="", $padding='2', $spacing='0'
		start_table( $style, $extra, $cellpadding, $cellspacing );
	}
	function table_end( $linebreaks = 0 )
	{
		end_table( $linebreaks );
	}
	function table_header( $labels_array, $params=null )
	{
		table_header( $labels_array, $params );
	}
}
?>
