<?php

/*******************************//**
 * A datastore for use in SOAP transactions
 *
 * There is probably a STDCLASS somewhere
 * but I don't know what off the top of my head.
 * ********************************/

require_once( '../ksf_modules_common/defines.inc.php' );
class name_value_list
{
	var $nvl;
	var $hash;	//from asteriskLogger (yaii)
	function __construct()
	{
		$this->nvl = array();
	}
	function add_nvl( $name, $value )
	{
		$this->nvl[] = array( 'name' => $name, 'value' => $value); 
	}
	function get_nvl() : array
	{
		return $this->nvl;
	}
	function get_value( $index ) :string
	{
		if( isset( $this->nvl[$index] ) )
			return $this->nvl[$index]['value'];
		else
			throw new Exception( "Index not set", KSF_VALUE_NOT_SET );
	}
	/**********************************//**
	 * Search the array for a named value
	 *
	 * @param name string of the param name
	 * @return int which array value. Throws KSF_VALUE_NOT_SET on NOT FOUND
	 * ************************************/
	function search_nvl( $name )
	{
		$count = 0;
		foreach( $this->nvl as $row )
		{
			if( $name == $row['name'] )
			{
				return $count;
			}
			$count++;
		}
		throw new Exception( "Index not set", KSF_VALUE_NOT_SET );
	}
	function get_named_value( $name )
	{
		try
		{
			$index = $this->search_nvl( $name );
			$value = $this->get_value( $index );
			return $value;
		}
		catch( Exception $e )
		{
			throw $e;
		}
	}
	/*****************************************//**
	 * Repack a Name-Value list into an assoc array (hash)
	 *
	 * Inspired by asteriskLogger
	 * @param nvl array to hash
	 * @returns array
	 * *********************************************/
	function hash_nvl( $nvl = null )
	{
		if (null == $nvl AND isset( $this->nvl ) )
		{
			$nvl = $this->nvl;
		}
		else if( is_array( $nvl ) )
		{
			//Should be able to proceed
		}
		else
		{
			throw new Exception( "No NVL to hash" );
		}
		$result = array();
		if (is_array($nvl) && count($nvl) > 0)
		{
			foreach ($nvl as $nvlEntry)
			{
				if( is_array( $nvlEntry ) )
				{
					var_dump( $nvlEntry );
					$key = $nvlEntry['name'];
					$val = $nvlEntry['value'];
					$result[$key] = $val;
				}
				else if( is_object( $nvlEntry ) )
				{
					var_dump( $nvlEntry );
					$key = $nvlEntry->name;
					$val = $nvlEntry->value;
					$result[$key] = $val;
				}
			}
		}
		$this->hash = $result;
		return $result;
	}
}

?>
