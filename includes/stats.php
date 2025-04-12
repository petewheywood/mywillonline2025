<?php
/***************************************
 * 	Title:		Display statistics
 *
 * 	Description:
 *		This will be called via AJAX
 *
 *
 *	Author:		Pete Heywood
 *	Email:		peteheywood@me.com
 *
 *	Copyright Ⓒ  2013
 *
 ***************************************/
error_reporting(E_ERROR);
ini_set('display_errors', 1);
//ini_set('max_execution_time', 120);

//session_start();
require_once('functions.php'); 
/*
if (!isset($_SESSION['userid']) || !$_SESSION['admin']) {
	echo "Invalid";
	exit;
}

*/


$stattype = $_GET['stattype'];


switch ($stattype) {
	case 'browsers':
	  $msg = "<table><tbody><tr><th>Month</th><th>Browser</th><th>Count</th></tr>";
		$bdata = array();
		$query = "SELECT year, month, browser, SUM(count) as count FROM browsers WHERE year = YEAR(NOW()) GROUP BY 1,2,3 ORDER BY year desc, month desc;";
		$dbh = dbConnect();
		$resultset = $dbh->query($query);
		$lastmonth = '';
		while ($row = $resultset->fetch(PDO::FETCH_ASSOC)) {
		    $year = $row['year'];
		    $month = $row['month'] < 10 ? '0' . $row['month']: $row['month'];
		    $date = $year . "-" . $month;
				$browser = $row['browser'];
				$count = $row['count'];
				$bdata[$year . "-" . $month][$browser] = $count;
				$msg .= $lastmonth == $date ? "<tr><td style='padding: 0 5px;'></td><td style='padding: 0 5px;'>$browser</td><td style='padding: 0 5px;'>$count</td></tr>" : "<tr><td class='topborder' style='padding: 0 5px;'>$date</td><td class='topborder' style='padding: 0 5px;'>$browser</td><td class='topborder' style='padding: 0 5px;'>$count</td></tr>";
				$lastmonth = $date;
		}
		$msg .= "</tbody></table>";
		break;
	case 'visits':
	  $msg = "<table><tbody><tr><th>Month</th><th>New Users</th><th>AdwordVisits</th><th>AllVisits</th><th>Orders</th><th>Revenue</th><th>Orders By User</th><th>Orders By Adword Hit</th><th>Orders By All Hits</th></tr>";
		$bdata = array();
		$query = "SELECT * FROM monthlystats;";
		$dbh = dbConnect();
		$resultset = $dbh->query($query);
		while ($row = $resultset->fetch(PDO::FETCH_ASSOC)) {
				$msg .= "<tr><td>{$row['month']}</td><td>{$row['newusers']}</td><td>{$row['adwordvisits']}</td><td>{$row['allvisits']}</td><td>{$row['orders']}</td><td>{$row['totalamount']}</td><td>{$row['orders_by_userregos']}</td><td>{$row['orders_by_adwordvisits']}</td><td>{$row['orders_by_allvisits']}</td></tr>";
		}
		$msg .= "</tbody></table>";
		break;
	default:
		$msg = 'Invalid option selected';
}

echo $msg;

?>