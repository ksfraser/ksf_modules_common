<?php

/**//********************************************************************
* CLass to handle the construction of application variables kinda like SuiteCRM
*
*/

require_once( 'class.origin.php' );

/**//**********************************
* Creates an array:

        'parent_name' => array(
            'name' => 'parent_name',
            'rname' => 'name',
            'id_name' => 'parent_id',
            'vname' => 'LBL_MEMBER_OF',
            'type' => 'relate',
            'isnull' => 'true',
            'module' => 'Accounts',
            'table' => 'accounts',
            'massupdate' => false,
            'source' => 'non-db',
            'len' => 36,
            'link' => 'member_of',
            'unified_search' => true,
            'importable' => 'true',
        ),

*/
class vardefs extends origin
{
            protected $name;	// => 'parent_name',
            protected $rname;	// => 'name',
            protected $id_name;	// => 'parent_id',
            protected $vname;	// => 'LBL_MEMBER_OF',
            protected $type;	// => 'relate',
            protected $dbtype;	// => 'relate',
            protected $isnull;	// => 'true',
            protected $module;	// => 'Accounts',
            protected $table;	// => 'accounts',
            protected $massupdate;	// => false,
            protected $source;	// => 'non-db',
            protected $len;	// => 36,
            protected $link;	// => 'member_of',
            protected $unified_search;	// => true,
            protected $importable;	// => 'true',
	protected $group;
	protected $required;
	protected $reportable;
	protected $audited;
	protected $comment;
	protected $bean_name;
	protected $relationship;
	protected $link_type;
	protected $side;
	protected $studio;	//!<bool |  array('visible' => false, 'searchview' => true),  array( 'editview' => false, 'detailview' => false, 'quickcreate' => false, 'basic_search' => false, 'advanced_search' => false,),
	protected $query_type;	// => 'default'
	protected $operator;	// subquery
	protected $subquery;	//SELECT statement
	protected $unified_search;	// ' => true, '
	protected $rel_fields;	//' => array('primary_address' => array('type' => 'bool')),
	protected $duplicate_merge;	//' => 'disabled',
	protected $merge_filter;
          
}
