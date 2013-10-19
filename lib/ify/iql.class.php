<?php

// Model to keep in mind:
// smartQuery($search, $columns, $group, $limit, $order);

class ifyIQL {


	// Internal Syntaxes (Loco array, array)
	private $_syntax_select;
	private $_syntax_where;
	private $_syntax_group;
	private $_syntax_limit;
	private $_syntax_order;



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
		$this->_in_group="";
		$this->_in_limit=50;
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


		// SQL WHERE syntax definition
		$this->_syntax_where = array(

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
					function($value) { $value = substr($value, 1, -1); return "( tagArtist LIKE '%".$value."%' OR tagAlbum LIKE '%".$value."%' OR tagTitle LIKE '%".$value."%' )"; }
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

	public function parse() {
		// Create SQL request


		// Concat strings
		$sql_where=new Grammar(
			"syntax_where",
			$this->_syntax_where
		);
		$result=$sql_where->parse($this->_in_where);

		// Return string
		var_dump($result);
		echo "good result";
		var_dump($result);



		return $result;

	}


}

?>
