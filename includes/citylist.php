<?php
/***************************************
 * 	Title:		List suburbs/cities
 *
 * 	Description:
 *		This will be called via AJAX for a jQuery UI Autocomplete field. Data returned is JSON
 *
 *
 *	Author:		Pete Heywood
 *	Email:		peteheywood@me.com
 *
 *	Created:	10/09/2012
 *	Updated:	10/09/2012
 *
 *	Copyright Ⓒ  2012
 *
 ***************************************/
require_once('functions.php'); 

$searchTerm = $_GET['term'];

$dbh = dbConnect();

$query = "SELECT id, CONCAT(locality, ', ', state, ' ', pcode) label, LOWER(locality) value, state, pcode postcode FROM aus_postcodes WHERE category = 'Delivery Area' AND locality LIKE '" . addslashes("$searchTerm%") . "' ORDER BY locality"; 

$resultset = $dbh->query($query);

$row_set = array();

while ($row = $resultset->fetch(PDO::FETCH_ASSOC)) {
    $row['value'] = strtoupper($row['value']);
		$row_set[] = $row; //build an array
}

echo json_encode($row_set); //format the array into json data

?>