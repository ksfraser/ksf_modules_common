<?php

$path_to_root="../..";

require_once( 'class.origin.php' );

class eventloop extends origin
{

	function __construct( $moduledir )
	{
		parent::__construct();
	}
			function FieldValidation($field, $value, $spec)
		{
			//field can't be an array
			if( is_array( $value ))
			{
				$this->LogError( "$field can't be an array, must be a single variable" );
				return NULL;
			}
			$field = trim($field);
			$this->LogMsg( "FieldValidation: $field, $value", PEAR_LOG_INFO );
			//ENUM
			//BOOL
			
			if (strlen($value) == 0)
			{
				$this->LogMsg( "Strlen check == 0 for $field", PEAR_LOG_WARNING );
				//echo "strlen is 0?";
				if (strtoupper($spec['mandatory_p']) == 'Y')
				{
					$this->LogError( "WARNING $field can't be blank if query autogenned", PEAR_LOG_ERR );
				}
				//Set date to end of time - makes calcs easier
				/*
				if ($spec['type'] == 'date')
				{
					$this->LogMsg( "Setting 0 length date to 9999-12-31 for $field", PEAR_LOG_WARNING );
					$value = '9999-12-31';
				}
				*/
				$this->LogError( "$field strlen 0", PEAR_LOG_WARNING );
				$returnvalue = NULL;
			}
			//Booleans are stored as tinyint(1) in MySQL
			//Convert y/Y or T/t to 1 and n/N or f/F to 0
			if ( strncasecmp( $spec['db_data_type'], "BOOL", 4 ) == 0 )
			{
				$this->LogMsg( "db_data_type = BOOL for  $field", PEAR_LOG_NOTICE );
				if ( strncasecmp( $value, 'Y', 1 ) == 0 )
				{
					$value = 1;
				}
				else
				if ( strncasecmp( $value, 'T', 1 ) == 0 )
				{
					$value = 1;
				}
				else
				if ( $value > 0 )
				{
					$value = 1;
				}
				else
				if ( strncasecmp( $value, 'F', 1 ) == 0 )
				{
					$value = 0;
				}
				else
				if ( strncasecmp( $value, 'N', 1 ) == 0 )
				{
					$value = 0;
				}
				else
				if ( $value == 0 )
				{
					$value = 0;
				}
				else
				{
					$value = 0;
					$this->LogError( "$field BOOL didn't match T/F/Y/N/1/0 so set to  0", PEAR_LOG_WARNING );
				}
				$returnvalue = $value;
			}
			if (	   strtoupper($spec['abstract_data_type']) == 'INTEGER' 
				OR strtoupper($spec['abstract_data_type']) == 'SMALLINT' 
				OR strtoupper($spec['abstract_data_type']) == 'BIGINT' 
				OR strtoupper($spec['abstract_data_type']) == 'TINYINT' 
				OR strtoupper($spec['abstract_data_type']) == 'INT' 
				OR strtoupper($spec['abstract_data_type']) == 'MEDIUMINT' 
				OR strtoupper($spec['abstract_data_type']) == 'DECIMAL' 
				OR strtoupper($spec['abstract_data_type']) == 'DOUBLE' 
				OR strtoupper($spec['abstract_data_type']) == 'NUMBER' 
				OR strtoupper($spec['abstract_data_type']) == 'REAL' 
				OR strtoupper($spec['abstract_data_type']) == 'FLOAT' 
				OR strtoupper($spec['abstract_data_type']) == 'PERCENT' 
				OR strtoupper($spec['abstract_data_type']) == 'CURRENCY' 
				OR strtoupper($spec['abstract_data_type']) == 'DOLLAR' )
			{
				//echo "FieldValidation ifint Value: ::$value::<br />";
				$this->LogMsg( "abstract_data_type is numeric for  $field: Sending to ValidateInteger", PEAR_LOG_INFO );
				$value = $this->ValidateInteger($field, $value, $spec);
				$returnvalue = $value;
			}
			else if (strtoupper($spec['abstract_data_type']) == 'BOOLEAN' OR strtoupper($spec['abstract_data_type']) == 'BOOL')
			{
				$this->LogMsg( "abstract_data_type = BOOL for  $field", PEAR_LOG_INFO );
				if ( 	   strncasecmp( $value, "Y", 1 ) == 0 
					OR strncasecmp( $value, "T", 1 ) == 0 )
				{
					$value = 1;
				}
				else
				{
					$value = 0;
				}
				if ($value > 0) $value = 1;
				$returnvalue = $value;
			}
			else if (
					(strtoupper($spec['abstract_data_type']) == 'DATETIME')
				OR	(strtoupper($spec['abstract_data_type']) == 'DATE')
				OR	(strtoupper($spec['abstract_data_type']) == 'TIMESTAMP')
				OR	(strtoupper($spec['abstract_data_type']) == 'TIME')
				OR	(strtoupper($spec['abstract_data_type']) == 'YEAR')
				)
			{
				//20080513 TIME values were not making it in.
				$this->LogMsg( "abstract_data_type = date/time/... for  $field", PEAR_LOG_INFO );
				$returnvalue = $value;
			}
			else if (
					(strtoupper($spec['abstract_data_type']) == 'BINARY')
				OR	(strtoupper($spec['abstract_data_type']) == 'SET')
				OR	(strtoupper($spec['abstract_data_type']) == 'ENUM')
				OR	(strtoupper($spec['abstract_data_type']) == 'BLOB')
				OR	(strtoupper($spec['abstract_data_type']) == 'LONGBLOB')
				OR	(strtoupper($spec['abstract_data_type']) == 'MEDIUMBLOB')
				OR	(strtoupper($spec['abstract_data_type']) == 'TINYBLOB')
				OR	(strtoupper($spec['abstract_data_type']) == 'TEXT')
				OR	(strtoupper($spec['abstract_data_type']) == 'LONGTEXT')
				OR	(strtoupper($spec['abstract_data_type']) == 'MEDIUMTEXT')
				OR	(strtoupper($spec['abstract_data_type']) == 'TINYTEXT')
				OR	(strtoupper($spec['abstract_data_type']) == 'VARBINARY')
				)
			{
				$this->LogMsg( "abstract_data_type = blob/text/... for  $field", PEAR_LOG_INFO );
				$returnvalue = $value;
			}
			else
			{
				//20080513 Separate Int from String processing
				if (strlen($value) > $spec['c_size'])
				{
					$this->LogMsg( "$field length greater than spec'd.  Trimming from " . strlen($value) . " to " . $spec['c_size'], PEAR_LOG_NOTICE );
				//Trim the length to fit in the table
				$value = substr($value, 0, $spec['c_size']);
					//	$lastdot = strrpos($value, ".");
					//	$lastspace = strrpos($value, " ");
					//	$value = substr($value, 0, ($lastdot > $lastspace? $lastdot : $lastspace));
				}
				if( strncmp( strtoupper($spec['field_toupper']), 'Y', 1) == 0)
				{
					$this->LogMsg( "$field spec'd as must be upper: Setting alpha's upper", PEAR_LOG_NOTICE );
					$value = strtoupper($value);
				}
				if( strncasecmp( $spec['abstract_data_type'], 'email', 5 ) == 0 )
				{
					$email_pattern = '/^[^@\s<&>]+@([-a-z0-9]+\.)+[a-z]{2,}$/i';
					if (preg_match($email_pattern, $value)) 
					{ 
						//email address is valid
						$this->LogMsg( "$field spec'd as email.  Pattern valid", PEAR_LOG_INFO );
					}
					else
					{
						$value = NULL;
						$this->LogMsg( "$field spec'd as email.  Pattern not valid", PEAR_LOG_WARNING );
						$this->LogError( "$value not seen as a valid email address" );
					}
				}
				if (       strtoupper($spec['abstract_data_type']) == 'STRING' 
					OR strtoupper($spec['abstract_data_type']) == 'VARCHAR' 
					OR strtoupper($spec['abstract_data_type']) == 'CHAR' 
					OR strtoupper($spec['abstract_data_type']) == 'TEXT')
				{
					$this->LogMsg( "$field spec'd as string/char.  adding slashes to $value", PEAR_LOG_INFO );
					$value = addslashes($value);
					$this->LogMsg( "after adding slashes - $value", PEAR_LOG_DEBUG );
				}
				if (strtoupper($spec['abstract_data_type']) == 'PASSWORD')
				{
					$this->LogMsg( "$field spec'd as password.  Hashing", PEAR_LOG_INFO );
					$value = md5($value);
				}
				$returnvalue = $value;
			}
			//echo "Returning ::$returnvalue::<br />";
			return $returnvalue;
		}

		function ValidateInteger($field, $value, $spec)
		{
			$pattern = '(int1|tinyint|int2|smallint|int3|mediumint|int4|integer|float|real|int8|bigint|int)';
   			if (preg_match($pattern, strtolower($spec['db_data_type']), $match)) 
			{
				//20120118 KF Yahoo Finance returns market cap as 1.234B or 2.345M.
				$vlen = strlen( $value );
				if( $vlen > 0 )
				{
					if( substr_compare( $value, 'B', $vlen -1, 1, false ) == 0 )
						$value = $value * 1000000000;
					else if( substr_compare( $value, 'M', $vlen -1, 1, false ) == 0 )
						$value = $value * 1000000;
				}		
				// test that input contains a valid value for an integer field
      				$integer = (int)$value;
      				if ((string)$value <> (string)$integer) 
				{
         				$this->LogError( "Field $field Value is not an integer.  Value: $value, converted " . (string)$integer . ", value as string: " . (string)$value . "<br />", PEAR_LOG_NOTICE );
         			//	return NULL;
      				} // if
      				// set min/max values depending of size of field
      				switch ($match[0])
				{	
					case 'int1':
         				case 'tinyint': 
            					$minvalue = -128;
            					$maxvalue =  127;
            					break;
         			case 'int2':
         				case 'smallint': 
            					$minvalue = -32768;
            					$maxvalue =  32767;
            					break;
         				case 'int3';
         				case 'mediumint':
            					$minvalue = -8388608;
            					$maxvalue =  8388607;
            					break;
         				case 'int':
         				case 'int4':
         				case 'integer':
					case 'float':
					case 'real':
            					$minvalue = -2147483648;
            					$maxvalue =  2147483647;
            					break;
         				case 'int8':
         				case 'bigint':
            					$minvalue = -9223372036854775808;
            					$maxvalue =  9223372036854775807;
            					break;
         				case 'boolean':
            					$minvalue = 0;
            					$maxvalue = 1;
            					break;
         				case 'percent':
            					$minvalue = 0;
            					$maxvalue = 100;
            					break;
         				default: 
            					$this->LogError( "Field $field Unknown integer type ($match)", PEAR_LOG_WARNING );
            					return (int)$value;
      				} // switch
      
      				// adjust min/max values if integer is unsigned
      				if ($spec['c_unsigned'] == 'Y') 
				{
         				$minvalue = 0;
         				$maxvalue = ($maxvalue * 2) +1;
      				} // if
      				if (isset($spec['minvalue'])) 	
				{
         				// override with value provided in $fieldspec
         				$minvalue = (int)$spec['minvalue'];
      				} // if
      				if ($integer < $minvalue) 
				{
         				$this->LogError( "Field $field Value is below minimum value ($minvalue)", PEAR_LOG_NOTICE );
					$value = $minvalue;
      				} // if
      				if (isset($spec['maxvalue'])) 
				{
         				// override with value provided in $fieldspec
         				$maxvalue = (int)$spec['maxvalue'];
      				} // if
      				if ($integer > $maxvalue) 
				{
         				$this->LogError ( "Field $field Value is above maximum value ($maxvalue)", PEAR_LOG_NOTICE );
					$value = $maxvalue;
      				} // if
            			if (isset($spec['zerofill'])) {
         				while (strlen($value) < $spec['size'])
					{
            					$value = '0' .$value;
					} // while
				} // if   
			} // if
   
   		return $value;
		}

}
?>
