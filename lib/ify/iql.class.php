<?php

// Model to keep in mind:
// smartQuery($search, $columns, $group, $limit, $order);

class ifyIQL {


	// Internal Syntaxes (Loco array, array)
	private $_syntax_base;
	private $_syntax_query;


	// Internal input arguments (IQL query, string)
	private $_in_select;
	private $_in_where;
	private $_in_group;
	private $_in_limit;
	private $_in_order;


	// Internal output arguments (MySQL query, string)
	private $_out_select;
	private $_out_where;
	private $_out_group;
	private $_out_limit;
	private $_out_order;






	// This function initialize internal input variables
	public function initArgs(){

		$this->_in_select="tagTitle";
		$this->_in_where="";
		$this->_in_group="artist";
		$this->_in_limit="50";
		$this->_in_order="";
		
	}


	// This function set internal input variables
	public function updateArgs(){

		// Order: $select, $where, $group, $limit, $order

		// Get all arguments
		$args=func_get_args();
		$number=count($args);

		// Fill arguments
		switch ($number) {
			case 5:
				//order
				$this->_in_order=$args[4];
			case 4:
				// limit
				$this->_in_limit=$args[3];
			case 3:
				// group
				$this->_in_group=$args[2];
			case 2:
				// where
				$this->_in_where=$args[1];
			case 1:
				// select
				$this->_in_select=$args[0];
		}
	}





	// This function init the object and store input arguments if any
	public function __construct () {

		// Initialise arguments
		$this->initArgs();


		////////////////////////////////
		// BASIC MYSQL SYNTAX DEFINITION
		////////////////////////////////
		$this->_syntax_base = array(


		// Base patterns

			// List of MySQL fields
			"c_field"	=> new LazyAltParser(
				array(
					new StringParser("artist", function() {return "tagArtist";}),
					new StringParser("album", function() {return "tagAlbum";}),
					new StringParser("title", function() {return "tagTitle";}),
					new StringParser("genre", function() {return "tagGenre";}),
					new StringParser("year", function() {return "tagYear";}),
					new StringParser("lenght", function() {return "lenghtLenght";}),
					new StringParser("track", function() {return "tagTrack";}),

					new StringParser("a", function() {return "tagArtist";}),
					new StringParser("b", function() {return "tagAlbum";}),
					new StringParser("t", function() {return "tagTitle";}),
					new StringParser("g", function() {return "tagGenre";}),
					new StringParser("y", function() {return "tagYear";}),
					new StringParser("l", function() {return "fileLenght";}),
					new StringParser("n", function() {return "tagTrack";})
				)
			),


			// Basic function integration for fields
			"c_field_extended"	=>	new LazyAltParser(
				array(
					"c_field",
					new ConcParser(
						array(
							new StringParser("c_", function() {return "COUNT(";}),
							"c_field",
							new EmptyParser(function() {return ")";})
						)
					)
				),
				function($args){
					//$args=func_get_args();

					if (is_array($args)) {
						$args=implode($args);
					}
					return $args;
				}
			),

			
			// Get the catch-all
			"all"		=> new RegexParser(
				"/^(c_)?(all|\*)\s*/",
				function($count, $all) {
					if (!empty($count)) {
						return "COUNT(*)";
					} else {
						return "*";
					}
				}
			),


			// Separator definition
			"c_sep"		=> new RegexParser(
				"/^\s+/",
				function() {
					return ", ";
				}
			),


	// Associations
			"multi"			=> new ConcParser(
				array(
					"c_field",
					new GreedyMultiParser(
						new ConcParser(
							array(
								"c_sep",
								"c_field"
							)
						),
						0,
						null
					)
				),
				function($first) {
					// Test if there are more than one expression
					$other=func_get_args();
					if (empty($other)) {
						return $first;
					}
					else {
						$first=array($first);
						$other=$other[1];

						foreach ($other as $value) {
							#echo "tata";
							#var_dump($value); 
							array_push($first, implode($value));
						}

						$first=implode($first);
						return $first;
					}
				}
			),
			"multi_extended"		=> new ConcParser(
				array(
					"c_field_extended",
					new GreedyMultiParser(
						new ConcParser(
							array(
								"c_sep",
								"c_field_extended"
							)
						),
						0,
						null
					)
				),
				function($first) {
					// Test if there are more than one expression
					$other=func_get_args();
					if (empty($other)) {
						return $first;
					}
					else {
						$first=array($first);
						$other=$other[1];

						foreach ($other as $value) {
							#echo "tata";
							#var_dump($value); 
							array_push($first, implode($value));
						}

						$first=implode($first);
						return $first;
					}
				}
			),




	// Root rules
			"syntax_select"	=> new LazyAltParser(
				array(
					"all",
					"multi_extended",
					new EmptyParser(function() {return "*";})
				)
			),
			"syntax_group"	=> new LazyAltParser(
				array(
					"multi"
				)
			),
			"syntax_limit"	=>	new RegexParser(
				"/^(\s*(\d+)\s*,)?\s*(\d+)\s*/",
				function($matches, $useless, $offset, $limit) {
					if (empty($offset)) {
						$offset= "0";
					}
					return " LIMIT $offset, $limit";
				}	
			),
			"syntax_order"	=> new LazyAltParser(
				array(
					"multi",
					new EmptyParser(function() {return null;})
				),
				function($arg) {
					if (is_null($arg)) {
						return "";
					} else {
						return " ORDER BY " . $arg . " ASC";
					}
				}
			)
		);



		//////////////////////////////
		// SQL WHERE syntax definition
		//////////////////////////////
		$this->_syntax_query = array(

				#
				# Language basics
				#
				

				// Fields
				"f_string"	=> new LazyAltParser(
					array(
						new StringParser("artist", function() {return "tagArtist"; }) ,
						new StringParser("album", function() {return "tagAlbum"; }) ,
						new StringParser("title", function() {return "tagTitle"; }) ,
						new StringParser("genre", function() {return "tagGenre"; }) ,
						new StringParser("a", function() {return "tagArtist"; }) ,
						new StringParser("b", function() {return "tagAlbum"; }) ,
						new StringParser("t", function() {return "tagTitle"; }) ,
						new StringParser("g", function() {return "tagGenre"; })
					)
				),
				"f_num"		=> new LazyAltParser(
					array(
						new StringParser("year", function() {return "tagYear"; }) ,
						new StringParser("lenght", function() {return "lenghtLenght"; }) ,
						new StringParser("track", function() {return "tagTrack"; }) ,
						new StringParser("y", function() {return "tagYear"; }) ,
						new StringParser("l", function() {return "fileLenght"; }) ,
						new StringParser("n", function() {return "tagTrack"; })
					)
				),


				// Operators
				"o_string"	=> new LazyAltParser(
					array(
						new StringParser("=", function() {return " = ";}) ,
						new StringParser(":", function() {return " LIKE ";}) ,
						new StringParser("!=", function() {return " != ";}) ,
						new StringParser("!:", function() {return " NOT LIKE ";})
					)
				),
				"o_num"	=> new LazyAltParser(
					array(
						new StringParser("=", function() {return " = ";}) ,
						new StringParser(":", function() {return " LIKE ";}) ,
						new StringParser("!=", function() {return " != ";}) ,
						new StringParser("!:", function() {return " NOT LIKE ";}) ,
						new StringParser("<=", function() {return " <= ";}) ,
						new StringParser(">=", function() {return " >= ";}) ,
						new StringParser("<", function() {return " < ";}) ,
						new StringParser(">", function() {return " > ";})
					)
				),
				"o_2num"	=> new LazyAltParser(
					array(
						new StringParser("]", function() {return " BETWEEN ";}) ,
						new StringParser("[", function() {return " NOT BETWEEN ";})
					)
				),


				// Values
				"v_string"	=> new LazyAltParser(
					array(
						new RegexParser("/^[\w_%]+/") ,
						new RegexParser("/^'([^']+)'/", function($match0, $match1) { return $match1; }) ,
						new RegexParser("/^\"([^\"]+)\"/", function($match0, $match1) { return $match1; })
					),
					function($value) {return "'" . $value . "'";}
				),
				"v_num"		=> new  RegexParser("/^([+-]?\d+)/", function($match0, $match1) { return $match1; }),
				"v_2num"	=> new ConcParser(
					array(
						"v_num",
						new StringParser(":", function() {return " AND ";}),
						"v_num"
					),
					function ($val1, $op, $val2) {return $val1.$op.$val2;}
				),


				// Logical
				"l_ao"		=> new LazyAltParser(
					array(
						new RegexParser("/^and/i", function() { return " AND "; }),
						new RegexParser("/^or/i", function() { return " OR "; }),
						new EmptyParser(function() { return " AND ";})
					)
				),
				"l_not"		=> new LazyAltParser(
					array(
						new RegexParser("/^not/i", function() { return "NOT "; }),
						new EmptyParser()
					)
				),


				// Misc
				"m_sep"		=> new RegexParser("/^\s*/",function() { return null; }) ,
				"m_meta"	=> new ConcParser(
					array(
						"v_string"
					),
					function($value) {
						$value = substr($value, 1, -1); 
						return "( tagArtist LIKE '%".$value."%' OR tagAlbum LIKE '%".$value."%' OR tagTitle LIKE '%".$value."%' )";
					}
				),
				

				#
				# Language expressions
				#

				"expression"	=> new LazyAltParser(
					array(
						// String
						new ConcParser(
							array(
								"m_sep",
								"l_not",
								"m_sep",
								"f_string",
								"m_sep",
								"o_string",
								"m_sep",
								"v_string",
								"m_sep"
							)
						),
						// Numerical
						new ConcParser(
							array(
								"m_sep",
								"l_not",
								"m_sep",
								"f_num",
								"m_sep",
								"o_num",
								"m_sep",
								"v_num",
								"m_sep"
							)
						),
						// Numerical (2 numbers)
						new ConcParser(
							array(
								"m_sep",
								"l_not",
								"m_sep",
								"f_num",
								"m_sep",
								"o_2num",
								"m_sep",
								"v_2num",
								"m_sep"
							)
						),
						// Meta search (default)
						new ConcParser(
							array(
								"m_sep",
								"l_not",
								"m_sep",
								"m_meta",
								"m_sep"
							)
						)
					),
					//function($match) {var_dump($match); return implode("", $match);}
					function($match) { return implode("", $match);}
				),
				"logical"		=>	new LazyAltParser(
					array(
						new ConcParser(
							array(
								"expression",
								"l_ao",
								"expression",
								"l_ao",
								"expression",
								"l_ao",
								"expression"
							)
						),
						new ConcParser(
							array(
								"expression",
								"l_ao",
								"expression",
								"l_ao",
								"expression"
							)
						),
						new ConcParser(
							array(
								"expression",
								"l_ao",
								"expression"
							)
						),
						new ConcParser(
							array(
								"expression"
							)
						)
					),
					//function($match) {var_dump($match); return implode("", $match);}
					function($match) { return implode("", $match);}
				),
				"syntax_where"		=>	new ConcParser(
					array(
						"expression",
						new GreedyMultiParser(
							new ConcParser(
								array(
									"m_sep",
									"l_ao",
									"m_sep",
									"expression",
									"m_sep"
								)
							),
							0,
							null
						)
					),
					function($first) {
						// Test if there are more than one expression
						$other=func_get_args();
						if (empty($other)) {
							return $first;
						}
						else {
							$first=array($first);
							$other=$other[1];

							foreach ($other as $value) {
								#echo "tata";
								#var_dump($value); 
								array_push($first, implode($value));
							}

							$first=implode($first);
							return $first;
						}
					}
				)
			);

	}

	public function buildSQL() {
		// Create SQL request



		// Processes: order
		if (empty($this->_in_order)) {
			$order="";
		} else {
			$sql_order=new Grammar(
				"syntax_order",
				$this->_syntax_base
			);
			$order=$sql_order->parse($this->_in_order);
		}


		// Processes: limit
		if (empty($this->_in_limit)) {
			$limit="";
		} else {
			$sql_limit=new Grammar(
				"syntax_limit",
				$this->_syntax_base
			);
			$limit=$sql_limit->parse($this->_in_limit);
		}


		// Processes: group
		if (empty($this->_in_group)) {
			$group="";
		} else {
			$sql_group=new Grammar(
				"syntax_group",
				$this->_syntax_base
			);
			$group=" GROUP BY " . $sql_group->parse($this->_in_group);
		}


		// Processes: select
		$sql_select=new Grammar(
			"syntax_select",
			$this->_syntax_base
		);
		$select=$sql_select->parse($this->_in_select);


		// Processes: where
		$sql_where=new Grammar(
			"syntax_where",
			$this->_syntax_query
		);
		$where=$sql_where->parse($this->_in_where);



		$result="SELECT " . $select . " FROM files WHERE " . $where . $group . $order . $limit;

		return $result;

	}


}

?>
