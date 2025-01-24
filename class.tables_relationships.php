<?php

require_once( 'class.origin.php' );

class tables_relationships
{
	protected $name;
 	protected $lhs_module;	// => 'Accounts',
	protected $lhs_table;	// => 'accounts',
	protected $lhs_key;	// => 'id',
	protected $rhs_module;	// => 'Notes',
	protected $rhs_table;	// => 'notes',
	protected $rhs_key;	// => 'parent_id',
	protected $relationship_type;	// => 'one-to-many',
	protected $relationship_role_column;	// => 'parent_type',
	protected $relationship_role_column_value;	// => 'Accounts'
}
