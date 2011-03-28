<?php
$phpVersion = phpversion();
$today = date('Y-m-d');
$agent = $_SERVER['HTTP_USER_AGENT'];

$pg = "default";
if (isset($_GET['pg'])) {
  $pg = (get_magic_quotes_gpc()) ? $_GET['pg'] : addslashes($_GET['pg']);
}

function check_setup() {
	include(CONFIG.'config.php');	
	mysql_select_db($database, $brewing);
	$query_setup = "SELECT COUNT(*) as 'count' FROM users WHERE NOT id='0'";
	$setup = mysql_query($query_setup, $brewing);
	$row_setup = mysql_fetch_assoc($setup);
	$totalRows_setup = $row_setup['count'];

	$query_setup1 = "SELECT COUNT(*) as 'count' FROM contest_info";
	$setup1 = mysql_query($query_setup1, $brewing);
	$row_setup1 = mysql_fetch_assoc($setup1);
	$totalRows_setup1 = $row_setup1['count'];

	$query_setup2 = "SELECT COUNT(*) as 'count' FROM preferences";
	$setup2 = mysql_query($query_setup2, $brewing);
	$row_setup2 = mysql_fetch_assoc($setup2);
	$totalRows_setup2 = $row_setup2['count'];

	if (($totalRows_setup == 0) && ($totalRows_setup1 == 0) && ($totalRows_setup2 == 0)) return true;
	else return false;
}

function relocate($referer,$page) {
	// determine if referrer has any msg=X variables attached
	if (strstr($referer,"&msg")) { 
	$pattern = array("/[0-9]/", "/&msg=/");
	$referer = preg_replace($pattern, "", $referer);
	$pattern = array("/[0-9]/", "/&id=/");
	$referer = preg_replace($pattern, "", $referer);
	if ($page != "default") { 
		$pattern = array("/[0-9]/", "/&pg=/"); 
		$referer = preg_replace($pattern, "", $referer); 
		$referer .= "&pg=".$page; 
		}
	}
	return $referer;
}

function judging_date_return() {
	include(CONFIG.'config.php');
	mysql_select_db($database, $brewing);
	$query_check = "SELECT judgingDate FROM judging_locations";
	$check = mysql_query($query_check, $brewing) or die(mysql_error());
	$row_check = mysql_fetch_assoc($check);
	do {
 		if ($row_check['judgingDate'] > $today) $newDate[] = 1; 
 		else $newDate[] = 0;
	} while ($row_check = mysql_fetch_assoc($check));
	if (in_array(1, $newDate)) return true; 
	else return false;
}


function greaterDate($start_date,$end_date)
{
  $start = new Datetime($start_date);
  $end = new Datetime($end_date);
  if ($start > $end)
   return 1;
  else
   return 0;
}

function lesserDate($start_date,$end_date)
{
  $start = new Datetime($start_date);
  $end = new Datetime($end_date);
  if ($start < $end)
   return 1;
  else
   return 0;
}

$color = "#eeeeee";
$color1 = "#e0e0e0";
$color2 = "#eeeeee";


// ---------------------------- Temperature, Weight, and Volume Conversion ----------------------------------

function tempconvert($temp,$t) { // $t = desired output, defined at function call
if ($t == "F") { // Celsius to F if source is C
	$tcon = (($temp - 32) / 1.8); 
    return round ($tcon, 1);
	}
	
if ($t == "C") { // F to Celsius
	$tcon = (($temp - 32) * (5/9)); 
    return round ($tcon, 1);
	}
}

function weightconvert($weight,$w) { // $w = desired output, defined at function call
if ($w == "pounds") { // kilograms to pounds
	$wcon = ($weight * 2.2046);
	return round ($wcon, 2);
	}
	
if ($w == "ounces") { // grams to ounces
	$wcon = ($weight * 0.03527);
	return round ($wcon, 2);
	}	
	
if ($w == "grams") { // ounces to grams
	$wcon = ($weight * 28.3495);
	return round ($wcon, 2);
	}
	
if ($w == "kilograms") { // pounds to kilograms
	$wcon = ($weight * 0.4535);
	return round ($wcon, 2);
	}
}

function volumeconvert($volume,$v) {  // $v = desired output, defined at function call
if ($v == "gallons") { // liters to gallons
	$vcon = ($volume * 0.2641);
	return round ($vcon, 2);
	}
	
if ($v == "ounces") { // milliliters to ounces
	$vcon = ($volume * 29.573);
	return round ($vcon, 2);
	}	

if ($v == "liters") { // gallons to liters
	$vcon = ($volume * 3.7854);
	return round ($vcon, 2);
	}
	
if ($v == "milliliters") { // fluid ounces to milliliters
	$vcon = ($volume * 29.5735) ;
	return round ($vcon, 2);
	}	
	
}

// ---------------------------- Date Conversion -----------------------------------------
// http://www.phpbuilder.com/annotate/message.php3?id=1031006
function dateconvert($date,$func) {
if ($func == 1)	{ //insert conversion
list($day, $month, $year) = split('[/.-]', $date); 
$date = "$year-$month-$day"; 
return $date;
	}
if ($func == 2)	{ //output conversion
list($year, $month, $day) = explode("-", $date);
if ($month == "01" ) { $month = "January "; }
if ($month == "02" ) { $month = "February "; }
if ($month == "03" ) { $month = "March "; }
if ($month == "04" ) { $month = "April "; }
if ($month == "05" ) { $month = "May "; }
if ($month == "06" ) { $month = "June "; }
if ($month == "07" ) { $month = "July "; }
if ($month == "08" ) { $month = "August "; }
if ($month == "09" ) { $month = "September "; }
if ($month == "10" ) { $month = "October "; }
if ($month == "11" ) { $month = "November "; }
if ($month == "12" ) { $month = "December "; }
$day = ltrim($day, "0");
/* 
Future release: add logic to check if user preferences 
dictate "American" English date formats vs. "British" 
English date formats
*/
$date = "$month $day, $year";
return $date;
	}
	
if ($func == 3)	{ //output conversion
list($year, $month, $day) = explode("-", $date);
if ($month == "01" ) { $month = "Jan"; }
if ($month == "02" ) { $month = "Feb"; }
if ($month == "03" ) { $month = "Mar"; }
if ($month == "04" ) { $month = "Apr"; }
if ($month == "05" ) { $month = "May"; }
if ($month == "06" ) { $month = "Jun"; }
if ($month == "07" ) { $month = "Jul"; }
if ($month == "08" ) { $month = "Aug"; }
if ($month == "09" ) { $month = "Sep"; }
if ($month == "10" ) { $month = "Oct"; }
if ($month == "11" ) { $month = "Nov"; }
if ($month == "12" ) { $month = "Dec"; }
$day = ltrim($day, "0");
/* 
Future release: add logic to check if user preferences 
dictate "American" English date formats vs. "British" 
English date formats
*/
$date = "$month $day, $year";
return $date;
	}
}

function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  $theValue = (!get_magic_quotes_gpc()) ? addslashes($theValue) : $theValue;

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}

# Pagination

function paginate($display, $pg, $total) {
  /* make sure pagination doesn't interfere with other query string variables */
  if(isset($_SERVER['QUERY_STRING']) && trim(
    $_SERVER['QUERY_STRING']) != '') {
    	if(stristr($_SERVER['QUERY_STRING'], 'pg='))
      	$query_str = '?'.preg_replace('/pg=\d+/', 'pg=', 
        $_SERVER['QUERY_STRING']);
    	else
      	$query_str = '?'.$_SERVER['QUERY_STRING'].'&pg=';
  		} 	
	else
    $query_str = '?pg=';
    
  /* find out how many pages we have */
  $pages = ($total <= $display) ? 1 : ceil($total / $display);
    
  /* create the links */
  $first = '<span id="sortable_first" class="first paginate_button"><a href="'.$_SERVER['PHP_SELF'].$query_str.'1">First</a></span>';
  $prev =  '<span id="sortable_previous" class="previous paginate_button"><a href="'.$_SERVER['PHP_SELF'].$query_str.($pg - 1).'">Previous</a></span>';
  $next =  '<span id="sortable_next" class="next paginate_button"><a href="'.$_SERVER['PHP_SELF'].$query_str.($pg + 1).'">Next</a></span>';
  $last =  '<span id="sortable_last" class="last paginate_button"><a href="'.$_SERVER['PHP_SELF'].$query_str.$pages.'">Last</a></span>';
   
  /* display opening navigation */
  echo '<div id="sortable_paginate" class="dataTables_paginate paging_full_numbers">';
  echo ($pg > 1) ? "$first$prev" : '<span id="sortable_first" class="first paginate_button">First</span><span id="sortable_previous" class="previous paginate_button">Previous</span>';
  
  // limit the number of page links displayed 
  $begin = $pg - 8;
  while($begin < 1)
    $begin++;
  $end = $pg + 8;
  while($end > $pages)
    $end--;
  for($i=$begin; $i<=$end; $i++)
    echo ($i == $pg) ? ' <span class="paginate_active">'.$i.'</span> ' : '<span class="paginate_button"><a href="'.
      $_SERVER['PHP_SELF'].$query_str.$i.'">'.$i.'</a></span>';
    
  /* display ending navigation */
  echo ($pg < $pages) ? "$next$last" : '<span id="sortable_next" class="next paginate_button">Next</span><span id="sortable_last" class="last paginate_button">Last</span>';
  echo '</div>';
}
	
	function total_fees($bid, $entry_fee, $entry_fee_discount, $discount, $entry_discount_number, $cap_no, $filter) {
		include(CONFIG.'config.php');
		
		if (($bid == "default") && ($filter == "default")) {
			mysql_select_db($database, $brewing);
			$query_users = "SELECT id,user_name FROM users";
			$users = mysql_query($query_users, $brewing) or die(mysql_error());
			$row_users = mysql_fetch_assoc($users);
			$totalRows_users = mysql_num_rows($users);
	
			do { $d[] = $row_users['id']; } while ($row_users = mysql_fetch_assoc($users));
			sort($d);
			
			foreach (array_unique($d) as $value) {
			
			// Get each entrant's number of entries
			mysql_select_db($database, $brewing);
			$query_entries = sprintf("SELECT COUNT(*) as 'count' FROM brewing WHERE brewBrewerID='%s'",$value);
			$entries = mysql_query($query_entries, $brewing) or die(mysql_error());
			$row_entries = mysql_fetch_array($entries);
			$totalRows_entries = $row_entries['count'];
			mysql_free_result($entries);
			
			//echo $cap_total;
			//echo "Query: ".$query_entries."<br>";
			//echo "Total Entries: ".$totalRows_entries."<br>";
			
			// Calculate the total entry fees taking into account any discounts after prescribed number of entries
			if ($totalRows_entries > 0) {
				if ($discount == "Y") {
				 	$a = $entry_discount_number * $entry_fee;
				 	$b = ($totalRows_entries - $entry_discount_number) * $entry_fee_discount;
					$c = $a + $b;
				 	$d = $totalRows_entries * $entry_fee;
				 	if ($totalRows_entries <= $entry_discount_number) $total = $d;
				 	if ($totalRows_entries > $entry_discount_number) $total = $c;
				 }
				else $total = $totalRows_entries * $entry_fee;
				
				if ($cap_no > 0) {
					if ($total < $cap_no) $total_calc = $total;
					if ($total >= $cap_no) $total_calc = $cap_no;
					}
				else $total_calc = $total;
				
				} 
				else $total_calc = 0;
			//print_r($total_array);
			$total_array[] = $total_calc;
			}
		}
		if (($bid != "default") && ($filter == "default")) {		
			$query_entries = sprintf("SELECT COUNT(*) as 'count' FROM brewing WHERE brewBrewerID='%s'",$bid);
			$entries = mysql_query($query_entries, $brewing) or die(mysql_error());
			$row_entries = mysql_fetch_array($entries);
			$totalRows_entries = $row_entries['count'];
			mysql_free_result($entries);
			
			if ($totalRows_entries > 0) {
				if ($discount == "Y") {
				 	$a = $entry_discount_number * $entry_fee;
				 	$b = ($totalRows_entries - $entry_discount_number) * $entry_fee_discount;
					$c = $a + $b;
				 	$d = $totalRows_entries * $entry_fee;
				 	if ($totalRows_entries <= $entry_discount_number) $total = $d;
				 	if ($totalRows_entries > $entry_discount_number) $total = $c;
				 }
				else $total = $totalRows_entries * $entry_fee;
				
				if ($cap_no > 0) {
					if ($total < $cap_no) $total_calc = $total;
					if ($total >= $cap_no) $total_calc = $cap_no;
					}
				else $total_calc = $total;
			
			} else $total_calc = 0;
			$total_array[] = $total_calc;
		}
		
		if (($bid == "default") && ($filter != "default")) {
		
		mysql_select_db($database, $brewing);
			$query_users = "SELECT id,user_name FROM users";
			$users = mysql_query($query_users, $brewing) or die(mysql_error());
			$row_users = mysql_fetch_assoc($users);
			$totalRows_users = mysql_num_rows($users);
	
			do { $d[] = $row_users['id']; } while ($row_users = mysql_fetch_assoc($users));
			sort($d);
			
			foreach (array_unique($d) as $value) {
			
			// Get each entrant's number of entries
			mysql_select_db($database, $brewing);
			$query_entries = sprintf("SELECT COUNT(*) as 'count' FROM brewing WHERE brewBrewerID='%s' AND brewCategorySort='%s'",$value, $filter);
			$entries = mysql_query($query_entries, $brewing) or die(mysql_error());
			$row_entries = mysql_fetch_array($entries);
			$totalRows_entries = $row_entries['count'];
			mysql_free_result($entries);
			
			//echo $cap_total;
			//echo "Query: ".$query_entries."<br>";
			//echo "Total Entries: ".$totalRows_entries."<br>";
			
			// Calculate the total entry fees taking into account any discounts after prescribed number of entries
			if ($totalRows_entries > 0) {
				if ($discount == "Y") {
				 	$a = $entry_discount_number * $entry_fee;
				 	$b = ($totalRows_entries - $entry_discount_number) * $entry_fee_discount;
					$c = $a + $b;
				 	$d = $totalRows_entries * $entry_fee;
				 	if ($totalRows_entries <= $entry_discount_number) $total = $d;
				 	if ($totalRows_entries > $entry_discount_number) $total = $c;
				 }
				else $total = $totalRows_entries * $entry_fee;
				
				if ($cap_no > 0) {
					if ($total < $cap_no) $total_calc = $total;
					if ($total >= $cap_no) $total_calc = $cap_no;
					}
				else $total_calc = $total;
				
				} 
				else $total_calc = 0;
			//print_r($total_array);
			$total_array[] = $total_calc;
			}	
		}
		
   		//print_r($total_array);
		$total_fees = array_sum($total_array);
   		return $total_fees;
	} // end function
		

	function total_fees_paid($bid, $entry_fee, $entry_fee_discount, $discount, $entry_discount_number, $cap_no, $filter) {
		include(CONFIG.'config.php');
		if (($bid == "default") && ($filter == "default")) {
			
			mysql_select_db($database, $brewing);
			$query_users = "SELECT id,user_name FROM users";
			$users = mysql_query($query_users, $brewing) or die(mysql_error());
			$row_users = mysql_fetch_assoc($users);
			$totalRows_users = mysql_num_rows($users);
	
			do { $d[] = $row_users['id']; } while ($row_users = mysql_fetch_assoc($users));
			sort($d);
			
			foreach (array_unique($d) as $value) {
			// Get each entrant's number of entries
			mysql_select_db($database, $brewing);
			$query_entries = sprintf("SELECT COUNT(*) as 'count' FROM brewing WHERE brewBrewerID='%s'",$value);
			$entries = mysql_query($query_entries, $brewing) or die(mysql_error());
			$row_entries = mysql_fetch_array($entries);
			$totalRows_entries = $row_entries['count'];
			mysql_free_result($entries);
			
			$query_not_paid = sprintf("SELECT COUNT(*) as 'count' FROM brewing WHERE brewBrewerID='%s' AND NOT brewPaid='Y'",$value);
			$entries_not_paid = mysql_query($query_not_paid, $brewing) or die(mysql_error());
			$row_entries_not_paid = mysql_fetch_array($entries_not_paid);
			$totalRows_entries_not_paid = $row_entries_not_paid['count'];
			mysql_free_result($entries_not_paid);
			
			$query_paid = sprintf("SELECT COUNT(*) as 'count' FROM brewing WHERE brewBrewerID='%s' AND brewPaid='Y'",$value);
			$paid = mysql_query($query_paid, $brewing) or die(mysql_error());
			$row_paid = mysql_fetch_array($paid);
			$totalRows_paid = $row_paid['count'];
			mysql_free_result($paid);
				
			//echo "Query: ".$query_entries."<br>";
			//echo "Total Entries: ".$totalRows_entries."<br>";
			//echo $cap."<br>";
	
			// Calculate the total entry fees taking into account any discounts after prescribed number of entries
			if ($totalRows_entries > 0) {
				if ($discount == "Y") {
				 	$a = ($entry_discount_number - $totalRows_paid) * $entry_fee;
				 	$b = ($totalRows_not_paid - $entry_discount_number) * $entry_fee_discount;
				 	$c = $a + $b;
				 	$d = ($entry_discount_number * $entry_fee);
				 	$e = (($totalRows_paid - $entry_discount_number) * $entry_fee_discount);
				 	$f = $d + $e;
				 	if (($totalRows_paid < $entry_discount_number) && ($totalRows_entries > $entry_discount_number)) $total = $c;
				 	if ($totalRows_paid < $entry_discount_number) $total = $totalRows_paid * $entry_fee;
				 	if ($totalRows_paid == $entry_discount_number) $total = $entry_discount_number * $entry_fee;
				 	if ($totalRows_paid > $entry_discount_number) $total = $f ;
				 	}
				else $total = $totalRows_paid * $entry_fee;
				
				if ($cap_no > 0) {
					if ($total < $cap_no) $total_calc = $total;
					if ($total >= $cap_no) $total_calc = $cap_no;
					}
				else $total_calc = $total;
				
			}
			else $total_calc = 0;
			$total_array[] = $total_calc;
			}
		}
		if (($bid != "default") && ($filter == "default")) {
			$query_entries = sprintf("SELECT COUNT(*) as 'count' FROM brewing WHERE brewBrewerID='%s'",$bid);
			$entries = mysql_query($query_entries, $brewing) or die(mysql_error());
			$row_entries = mysql_fetch_array($entries);
			$totalRows_entries = $row_entries['count'];
			mysql_free_result($entries);
			
			$query_not_paid = sprintf("SELECT COUNT(*) as 'count' FROM brewing WHERE brewBrewerID='%s' AND NOT brewPaid='Y'",$bid);
			$entries_not_paid = mysql_query($query_not_paid, $brewing) or die(mysql_error());
			$row_entries_not_paid = mysql_fetch_array($entries_not_paid);
			$totalRows_entries_not_paid = $row_entries_not_paid['count'];
			mysql_free_result($entries_not_paid);
			
			$query_paid = sprintf("SELECT COUNT(*) as 'count' FROM brewing WHERE brewBrewerID='%s' AND brewPaid='Y'",$bid);
			$paid = mysql_query($query_paid, $brewing) or die(mysql_error());
			$row_paid = mysql_fetch_array($paid);
			$totalRows_paid = $row_paid['count'];
			mysql_free_result($paid);
			
			// Calculate the total entry fees taking into account any discounts after prescribed number of entries
			if ($totalRows_entries > 0) {
				if ($discount == "Y") {
				 	$a = ($entry_discount_number - $totalRows_paid) * $entry_fee;
				 	$b = ($totalRows_not_paid - $entry_discount_number) * $entry_fee_discount;
				 	$c = $a + $b;
				 	$d = ($entry_discount_number * $entry_fee);
				 	$e = (($totalRows_paid - $entry_discount_number) * $entry_fee_discount);
				 	$f = $d + $e;
				 	if (($totalRows_paid < $entry_discount_number) && ($totalRows_entries > $entry_discount_number)) $total = $c;
				 	if ($totalRows_paid < $entry_discount_number) $total = $totalRows_paid * $entry_fee;
				 	if ($totalRows_paid == $entry_discount_number) $total = $entry_discount_number * $entry_fee;
				 	if ($totalRows_paid > $entry_discount_number) $total = $f ;
				 }
				else $total = $totalRows_paid * $entry_fee;
				
				if ($cap_no > 0) {
					if ($total < $cap_no) $total_calc = $total;
					if ($total >= $cap_no) $total_calc = $cap_no;
					}
				else $total_calc = $total;
			}
			else $total_calc = 0;
			$total_array[] = $total_calc;
		}
		
		if (($bid == "default") && ($filter != "default")) {
			
			mysql_select_db($database, $brewing);
			$query_users = "SELECT id,user_name FROM users";
			$users = mysql_query($query_users, $brewing) or die(mysql_error());
			$row_users = mysql_fetch_assoc($users);
			$totalRows_users = mysql_num_rows($users);
	
			do { $d[] = $row_users['id']; } while ($row_users = mysql_fetch_assoc($users));
			sort($d);
			
			foreach (array_unique($d) as $value) {
			// Get each entrant's number of entries
			mysql_select_db($database, $brewing);
			$query_entries = sprintf("SELECT brewBrewerID FROM brewing WHERE brewBrewerID='%s' AND brewCategorySort='%s'",$value, $filter);
			$entries = mysql_query($query_entries, $brewing) or die(mysql_error());
			$row_entries = mysql_fetch_assoc($entries);
			$totalRows_entries = mysql_num_rows($entries);
			
			$query_not_paid = sprintf("SELECT brewBrewerID FROM brewing WHERE brewBrewerID='%s' AND brewCategorySort='%s' AND NOT brewPaid='Y'",$value, $filter);
			$entries_not_paid = mysql_query($query_not_paid, $brewing) or die(mysql_error());
			$row_entries_not_paid = mysql_fetch_assoc($entries_not_paid);
			$totalRows_entries_not_paid = mysql_num_rows($entries_not_paid);
			
			$query_paid = sprintf("SELECT brewBrewerID FROM brewing WHERE brewBrewerID='%s' AND brewCategorySort='%s' AND brewPaid='Y'",$value, $filter);
			$paid = mysql_query($query_paid, $brewing) or die(mysql_error());
			$row_paid = mysql_fetch_assoc($paid);
			$totalRows_paid = mysql_num_rows($paid);
				
			//echo "Query: ".$query_entries."<br>";
			//echo "Total Entries: ".$totalRows_entries."<br>";
			//echo $cap."<br>";
	
			// Calculate the total entry fees taking into account any discounts after prescribed number of entries
			if ($totalRows_entries > 0) {
				if ($discount == "Y") {
				 	$a = ($entry_discount_number - $totalRows_paid) * $entry_fee;
				 	$b = ($totalRows_not_paid - $entry_discount_number) * $entry_fee_discount;
				 	$c = $a + $b;
				 	$d = ($entry_discount_number * $entry_fee);
				 	$e = (($totalRows_paid - $entry_discount_number) * $entry_fee_discount);
				 	$f = $d + $e;
				 	if (($totalRows_paid < $entry_discount_number) && ($totalRows_entries > $entry_discount_number)) $total = $c;
				 	if ($totalRows_paid < $entry_discount_number) $total = $totalRows_paid * $entry_fee;
				 	if ($totalRows_paid == $entry_discount_number) $total = $entry_discount_number * $entry_fee;
				 	if ($totalRows_paid > $entry_discount_number) $total = $f ;
				 	}
				else $total = $totalRows_paid * $entry_fee;
				
				if ($cap_no > 0) {
					if ($total < $cap_no) $total_calc = $total;
					if ($total >= $cap_no) $total_calc = $cap_no;
					}
				else $total_calc = $total;
			}
			else $total_calc = 0;
			$total_array[] = $total_calc;
			}
		}
   		//print_r($total_array);
		$total_fees = array_sum($total_array);
   		return $total_fees;
		} // end function
		
	//$total_entry_fees = total_fees($bid, $entry_fee, $entry_fee_discount, $discount, $entry_discount_number, $cap_no, $filter); 
	//$total_paid_entry_fees = total_fees_paid($bid, $entry_fee, $entry_fee_discount, $discount, $entry_discount_number, $cap_no, $filter);
	//$total_to_pay = $total_entry_fees - $total_paid_entry_fees; 
	//total_fees_to_pay($bid, $entry_fee, $entry_fee_discount, $discount, $entry_discount_number, $cap_no); 

function unpaid_fees($total_not_paid, $discount_amt, $entry_fee, $entry_fee_disc, $cap) {
	switch($discount) {
		case "N": 
			$entry_total = $total_not_paid * $entry_fee;
		break;
		case "Y":
			if ($total_not_paid > $discount_amt) {
				$reg_fee = $discount_amt * $entry_fee; // 
				$disc_fee = ($total_not_paid - $discount_amt) * $entry_fee_disc;
				$entry_subtotal = $reg_fee + $disc_fee;
				}
			if ($total_not_paid <= $discount_amt) {
				if ($total_not_paid > 0) $entry_total = $total_not_paid * $entry_fee;
				else $entry_subtotal = "0";
				}
		break;			
		} // end switch
		
		if ($cap == "0") $entry_total = $entry_subtotal;
		else { 
			if ($entry_subtotal > $cap) $entry_total = $cap;
			else $entry_total = $entry_subtotal;
		}
		return $entry_total;
	
} // end function

function discount_display($total_not_paid, $discount_amt, $entry_fee, $entry_fee_disc, $cap) { 
	if ($total_not_paid > $discount_amt) {
		$disc_fee = (($total_not_paid - $discount_amt) * $entry_fee_disc);
		$reg_fee = ($discount_amt * $entry_fee);
		$total = $disc_fee + $reg_fee;
		$array["a"] = $total_not_paid - $discount_amt;
		$array["b"] = $reg_fee;
		$array["c"] = $disc_fee;
		if (($cap != "0") && ($total <= $cap)) {
			$array["d"] = $total;
			}
		elseif (($cap != "0") && ($total > $cap)) { 
			$array["d"] = $cap;
			}
		else {
			$array["d"] = $total;
			}
		
		}
	if ($total_not_paid <= $discount_amt) {
		if ($total_not_paid > 0) $array = $total_not_paid * $entry_fee;
		else $array = "0";
		}
	return $array;
} // end funtion

function total_not_paid_brewer($bid) { 
	include(CONFIG.'config.php');
	mysql_select_db($database, $brewing);

	$query_all = sprintf("SELECT COUNT(*) as 'count' FROM brewing WHERE brewBrewerID='%s'", $bid);
	$all = mysql_query($query_all, $brewing) or die(mysql_error());
	$row_all = mysql_fetch_assoc($all);
	$totalRows_all = $row_all['count'];

	$query_paid = sprintf("SELECT COUNT(*) as 'count' FROM brewing WHERE brewBrewerID='%s' AND brewPaid='Y'", $bid);
	$paid = mysql_query($query_paid, $brewing) or die(mysql_error());
	$row_paid = mysql_fetch_assoc($paid);
	$totalRows_paid = $row_paid['count'];

	$total_not_paid = ($totalRows_all - $totalRows_paid);
	return $total_not_paid;
}

function total_paid_received() {
	include(CONFIG.'config.php');
	mysql_select_db($database, $brewing);
	
	$query_entry_count = "SELECT COUNT(*) as 'count' FROM brewing";
	if ($go == "judging_scores") $query_entry_count .= " WHERE brewPaid='Y' AND brewReceived='Y'";
	$result = mysql_query($query_entry_count, $brewing) or die(mysql_error());
	$row = mysql_fetch_array($result);
	mysql_free_result($result);
	return $row['count'];
}

function style_convert($number,$type) {
	switch ($type) {
		case "1": 
		switch ($number) {
			case "01": $style_convert = "Light Lager"; break;
			case "02": $style_convert = "Pilsner"; break;
			case "03": $style_convert = "European Amber Lager"; break;
			case "04": $style_convert = "Dark Lager"; break;
			case "05": $style_convert = "Bock"; break;
			case "06": $style_convert = "Light Hybrid Beer"; break;
			case "07": $style_convert = "Amber Hybrid Beer"; break;
			case "08": $style_convert = "English Pale Ale"; break;
			case "09": $style_convert = "Scottish and Irish Ale"; break;
			case "10": $style_convert = "American Ale"; break;
			case "11": $style_convert = "English Brown Ale"; break;
			case "12": $style_convert = "Porter"; break;
			case "13": $style_convert = "Stout"; break;
			case "14": $style_convert = "India Pale Ale (IPA)"; break;
			case "15": $style_convert = "German Wheat and Rye Beer"; break;
			case "16": $style_convert = "Belgian and French Ale"; break;
			case "17": $style_convert = "Sour Ale"; break;
			case "18": $style_convert = "Belgian Strong Ale"; break;
			case "19": $style_convert = "Strong Ale"; break;
			case "20": $style_convert = "Fruit Beer"; break;
			case "21": $style_convert = "Spice/Herb/Vegatable Beer"; break;
			case "22": $style_convert = "Smoke-Flavored and Wood-Aged Beer"; break;
			case "23": $style_convert = "Specialty Beer"; break;
			case "24": $style_convert = "Traditional Mead"; break;
			case "25": $style_convert = "Melomel (Fruit Mead)"; break;
			case "26": $style_convert = "Other Mead"; break;
			case "27": $style_convert = "Standard Cider and Perry"; break;
			case "28": $style_convert = "Specialty Cider and Perry"; break;
			default: $style_convert = "Custom Style"; break;
		}
		break;
		case "2":
		switch ($number) {
			case "01": $style_convert = "1A,1B,1C,1D,1E"; break;
			case "02": $style_convert = "2A,2B,2C"; break;
			case "03": $style_convert = "3A,3B"; break;
			case "04": $style_convert = "4A,4B,4C"; break;
			case "05": $style_convert = "5A,5B,5C,5D"; break;
			case "06": $style_convert = "6A,6B,6C,6D"; break;
			case "07": $style_convert = "7A,7B,7C"; break;
			case "08": $style_convert = "8A,8B,8C"; break;
			case "09": $style_convert = "9A,9B,9C,9D,9E"; break;
			case "10": $style_convert = "10A,10B,10C"; break;
			case "11": $style_convert = "11A,11B,11C"; break;
			case "12": $style_convert = "12A,12B,12C"; break;
			case "13": $style_convert = "13A,13B,13C,13D,13E,13F"; break;
			case "14": $style_convert = "14A,14B,14C,"; break;
			case "15": $style_convert = "15A,15B,15C,15D,"; break;
			case "16": $style_convert = "16A,16B,16C,16D,16E,"; break;
			case "17": $style_convert = "17A,17B,17C,17D,17E,17F"; break;
			case "18": $style_convert = "18A,18B,18C,18D,18E,"; break;
			case "19": $style_convert = "19A,19B,19C,"; break;
			case "20": $style_convert = "20"; break;
			case "21": $style_convert = "21A,21B"; break;
			case "22": $style_convert = "22A,22B,22C"; break;
			case "23": $style_convert = "23"; break;
			case "24": $style_convert = "24A,24B,24C"; break;
			case "25": $style_convert = "25A,25B,25C"; break;
			case "26": $style_convert = "25A,25B,26C"; break;
			case "27": $style_convert = "27A,27B,27C,27D,27E"; break;
			case "28": $style_convert = "28A,28B,28C,28D"; break;
			default: $style_convert = "Custom Style"; break;
		}
		break;
		
		case "3":
		$n = ereg_replace('[^0-9]+', '', $number);
		if ($n > 23) $style_convert = TRUE;
		else {
		switch ($number) {
			case "6D": $style_convert = TRUE; break;
			case "21A": $style_convert = TRUE; break;
			case "22B": $style_convert = TRUE; break;
			case "22C": $style_convert = TRUE; break;
			case "23A": $style_convert = TRUE; break;
			case "25C": $style_convert = TRUE; break;
			case "26A": $style_convert = TRUE; break;
			case "26C": $style_convert = TRUE; break;
			case "27E": $style_convert = TRUE; break;
			case "28B": $style_convert = TRUE; break;
			default: $style_convert = FALSE; break;
		    }
		}
		break;
		
		case "4":
		$a = explode(",",$number);
		include(CONFIG.'config.php');
	    mysql_select_db($database, $brewing);
		foreach ($a as $value) {
			$query_style = "SELECT brewStyleGroup,brewStyleNum FROM styles WHERE id='$value'"; 
			$style = mysql_query($query_style, $brewing) or die(mysql_error());
			$row_style = mysql_fetch_assoc($style);
			$style_convert[] = ltrim($row_style['brewStyleGroup'],"0").$row_style['brewStyleNum'];
		}
		break;
	}
	return $style_convert;
}

function get_table_info($input,$method,$id) {	
	include(CONFIG.'config.php');
	mysql_select_db($database, $brewing);
	$query_table = "SELECT * FROM judging_tables";
	if ($id != "default") $query_table .= " WHERE id='$id'"; 
	$table = mysql_query($query_table, $brewing) or die(mysql_error());
	$row_table = mysql_fetch_assoc($table);
	
	if ($method == "basic") {
		$return = $row_table['tableNumber']."^".$row_table['tableName']."^".$row_table['tableLocation'];
		return $return;
	}
	
	if ($method == "location") { // used in output/assignments.php and output/pullsheets.php
		$query_judging_location = sprintf("SELECT * FROM judging_locations WHERE id='%s'", $input);
		$judging_location = mysql_query($query_judging_location, $brewing) or die(mysql_error());
		$row_judging_location = mysql_fetch_assoc($judging_location);
		
		$return = $row_judging_location['judgingDate']."^".$row_judging_location['judgingTime']."^".$row_judging_location['judgingLocName'];
		return $return;
	}
	
	if ($method == "unassigned") {
		$return = "";
		$query_styles = "SELECT id,brewStyle FROM styles";
		$styles = mysql_query($query_styles, $brewing) or die(mysql_error());
		$row_styles = mysql_fetch_assoc($styles);
		
		do { $a[] = $row_styles['id']; } while ($row_styles = mysql_fetch_assoc($styles));
		sort($a);
		//echo "<p>"; print_r($a); echo "</p>";
		foreach ($a as $value) { 
			//echo $input."<br>";
			$b = array(explode(",",$input));
			//echo "<p>".print_r($b)."</p>";
			//echo "<p>-".$value."-</p>";
			if (in_array($value,$b)) { 
				echo "Yes. The style ID is $value.<br>";
				//$query_styles1 = "SELECT brewStyle FROM styles WHERE id='$value'";
				//$styles1 = mysql_query($query_styles1, $brewing) or die(mysql_error());
				//$row_styles1 = mysql_fetch_assoc($styles1);
				//echo "<p>".$row_styles1['brewStyle']."</p>";
				}
			
				//else echo "No.<br>";
		}
	return $return;
	}
	
	if ($method == "styles") {
		do { 
			$a = explode(",", $row_table['tableStyles']);
			$b = $input;
			foreach ($a as $value) {
				if ($value == $input) return TRUE;
			}
		} while ($row_table = mysql_fetch_assoc($table));
	}
	
	if ($method == "assigned") {
		do { 
			$a = explode(",", $row_table['tableStyles']);
			$b = $input;
			foreach ($a as $value) {
				if ($value == $input) $c = "<br><em>Assigned to Table #".$row_table['tableNumber'].": <a href='index.php?section=admin&go=judging_tables&action=edit&id=".$row_table['id']."'>".$row_table['tableName']."</a></em>";
			}
		} while ($row_table = mysql_fetch_assoc($table));
	return $c;
  	}
	
	if ($method == "list") {
		$a = explode(",", $row_table['tableStyles']);
			foreach ($a as $value) {
				include(CONFIG.'config.php');
				mysql_select_db($database, $brewing);
				$query_styles = "SELECT * FROM styles WHERE id='$value'";
				$styles = mysql_query($query_styles, $brewing) or die(mysql_error());
				$row_styles = mysql_fetch_assoc($styles);
				
				$c[] = ltrim($row_styles['brewStyleGroup'].$row_styles['brewStyleNum'],"0").",&nbsp;";
			}
	$d = array($c);
	return $d;
  	}
	
	if ($method == "count_total") {
		$a = explode(",", $row_table['tableStyles']);
			foreach ($a as $value) {
				include(CONFIG.'config.php');
				mysql_select_db($database, $brewing);
				$query_styles = "SELECT brewStyle FROM styles WHERE id='$value'";
				$styles = mysql_query($query_styles, $brewing) or die(mysql_error());
				$row_styles = mysql_fetch_assoc($styles);
				
				$query_style_count = sprintf("SELECT COUNT(*) as count FROM brewing WHERE brewStyle='%s' AND brewPaid='Y' AND brewReceived='Y'", $row_styles['brewStyle']);
				$style_count = mysql_query($query_style_count, $brewing) or die(mysql_error());
				$row_style_count = mysql_fetch_assoc($style_count);
				$totalRows_style_count = $row_style_count['count'];
				
				$c[] = $totalRows_style_count ;
			}
	$d = array_sum($c);
	return $d; 
  	}
	
	if ($method == "count") {
	include(CONFIG.'config.php');
	mysql_select_db($database, $brewing);
	$query_style = "SELECT brewStyle FROM styles WHERE brewStyle='$input'";
	$style = mysql_query($query_style, $brewing) or die(mysql_error());
	$row_style = mysql_fetch_assoc($style);
	//echo $query_style."<br>";
	
	$query = sprintf("SELECT COUNT(*) FROM brewing WHERE brewStyle='%s' AND brewPaid='Y' AND brewReceived='Y'",$row_style['brewStyle']);
	$result = mysql_query($query, $brewing) or die(mysql_error());
	$num_rows = mysql_fetch_array($result);
	// echo $query;
	//$num_rows = mysql_num_rows($result);
	return $num_rows[0];
	}
	
	if ($method == "count_scores") {
		$a = explode(",", $row_table['tableStyles']);
			foreach ($a as $value) {
				include(CONFIG.'config.php');
				mysql_select_db($database, $brewing);
				$query_styles = "SELECT brewStyle FROM styles WHERE id='$value'";
				$styles = mysql_query($query_styles, $brewing) or die(mysql_error());
				$row_styles = mysql_fetch_assoc($styles);
				
				$query_style_count = sprintf("SELECT COUNT(*) as 'count' FROM brewing WHERE brewStyle='%s' AND brewPaid='Y' AND brewReceived='Y'", $row_styles['brewStyle']);
				$style_count = mysql_query($query_style_count, $brewing) or die(mysql_error());
				$row_style_count = mysql_fetch_assoc($style_count);
				$totalRows_style_count = $row_style_count['count'];
									
				$c[] = $totalRows_style_count;
				
			}
	$query_score_count = sprintf("SELECT COUNT(*) as 'count' FROM judging_scores WHERE scoreTable='%s'", $input);
	$score_count = mysql_query($query_score_count, $brewing) or die(mysql_error());
	$row_score_count = mysql_fetch_assoc($score_count);
	$totalRows_score_count = $row_score_count['count'];
	$e = array_sum($c);
	if ($e == $totalRows_score_count) return true;
  	}
}

function displayArrayContent($arrayname,$method) {
 	$a = "";
 	while(list($key, $value) = each($arrayname)) {
  		if (is_array($value)) {
   		$a .= displayArrayContent($value,'');
		
   		}
  	else $a .= "$value";
	if ($method == "2") $a .= ", ";
	if ($method == "1") $a .= "";
  	}
	$b = rtrim($a, ",&nbsp;");
 	return $b;
}

function bos_place($eid) { 
	include(CONFIG.'config.php');
	mysql_select_db($database, $brewing);
	$query_bos_place = "SELECT scorePlace,scoreEntry FROM judging_scores_bos WHERE eid='$eid'";
	$bos_place = mysql_query($query_bos_place, $brewing) or die(mysql_error());
	$row_bos_place = mysql_fetch_assoc($bos_place);
	$value = $row_bos_place['scorePlace']."-".$row_bos_place['scoreEntry'];
	return $value;
}

function style_type($type,$method,$source) { 
	if ($method == "1") { 
		switch($type) { 
			case "Mead": $type = "3";
			break;
			
			case "Cider": $type = "2";
			break;
			
			case "Mixed": $type = "1";
			break;
			
			case "Ale": $type = "1";
			break;
			
			case "Lager": $type = "1";
			break;
			
			default: $type = $type;
			break;
		}
	}
	
	if (($method == "2") && ($source == "bcoe")) { 
		switch($type) {
			case "3": $type = "Mead";
			break;
			
			case "2": $type = "Cider";
			break;
			
			case "1": $type = "Beer";
			break;
			
			case "Lager": $type = "Beer";
			break;
			
			case "Ale": $type = "Beer";
			break;
			
			case "Mixed": $type = "Beer";
			break;
			
			default: $type = $type;
			break;
		}
	}
	
	if (($method == "2") && ($source == "custom")) { 
		include(CONFIG.'config.php');
		mysql_select_db($database, $brewing);
		
		$query_style_type = "SELECT styleTypeName FROM style_types WHERE id='$type'"; 
		$style_type = mysql_query($query_style_type, $brewing) or die(mysql_error());
		$row_style_type = mysql_fetch_assoc($style_type);
		$type = $row_style_type['styleTypeName'];
	}
	return $type;
}

function check_bos_loc($id) { 
	include(CONFIG.'config.php');
	$query_judging = "SELECT judgingLocName,judgingDate FROM judging_locations WHERE id='$id'";
	$judging = mysql_query($query_judging, $brewing) or die(mysql_error());
	$row_judging = mysql_fetch_assoc($judging);
	$totalRows_judging = mysql_num_rows($judging);
	$bos_loc = $row_judging['judgingLocName']." (".dateconvert($row_judging['judgingDate'], 3).")";
	return $bos_loc;
}

function bos_method($value) {
	switch($value) {
		case "1": $bos_method = "1st place only";
		break;
		case "2": $bos_method = "1st and 2nd places only";
		break;
		case "3": $bos_method = "1st, 2nd, and 3rd places";
		break;
		case "4": $bos_method = "Defined by Admin";
		break;
	}
	return $bos_method;
}

function text_number($n) {
    # Array holding the teen numbers. If the last 2 numbers of $n are in this array, then we'll add 'th' to the end of $n
    $teen_array = array(11, 12, 13, 14, 15, 16, 17, 18, 19);
   
    # Array holding all the single digit numbers. If the last number of $n, or if $n itself, is a key in this array, then we'll add that key's value to the end of $n
    $single_array = array(1 => 'st', 2 => 'nd', 3 => 'rd', 4 => 'th', 5 => 'th', 6 => 'th', 7 => 'th', 8 => 'th', 9 => 'th', 0 => 'th');
   
    # Store the last 2 digits of $n in order to check if it's a teen number.
    $if_teen = substr($n, -2, 2);
   
    # Store the last digit of $n in order to check if it's a teen number. If $n is a single digit, $single will simply equal $n.
    $single = substr($n, -1, 1);
   
    # If $if_teen is in array $teen_array, store $n with 'th' concantenated onto the end of it into $new_n
    if (in_array($if_teen, $teen_array)) {
        $new_n = $n . 'th';
    	}
    # $n is not a teen, so concant the appropriate value of it's $single_array key onto the end of $n and save it into $new_n
    elseif ($single_array[$single])  {
        $new_n = $n . $single_array[$single];   
    	}
		
    # Return new
    return $new_n;
}

function style_choose($section,$go,$action,$filter) {
	include(CONFIG.'config.php');
	mysql_select_db($database, $brewing);
	
	$style_choose = '<select name="brewStyle" onchange="jumpMenu(\'self\',this,0)">';
	$style_choose .= '<option value="">Select Below</option>';
	for($i=1; $i<29; $i++) { 
		if ($i <= 9) $num = "0".$i; else $num = $i;
		$query_entry_count = "SELECT COUNT(*) as 'count' FROM brewing WHERE brewCategory='$i'";
		$result = mysql_query($query_entry_count, $brewing) or die(mysql_error());
		$row = mysql_fetch_array($result);
		if ($num == $filter) $selected = ' "selected"'; else $selected = '';
		if ($row['count'] > 0) { $style_choose .= '<option value="'.$_SERVER['SCRIPT_NAME'].'?section='.$section.'&go='.$go.'&action='.$action.'&filter='.$num.'"'.$selected.'>'.$num.' '.style_convert($i,"1").' ('.$row['count'].' entries)</option>'; }
	}
	
	$query_styles = "SELECT brewStyle,brewStyleGroup FROM styles WHERE brewStyleGroup >= 29";
	$styles = mysql_query($query_styles, $brewing) or die(mysql_error());
	$row_styles = mysql_fetch_assoc($styles);
	$totalRows_styles = mysql_num_rows($styles);
	
	do {  
		$query_entry_count = sprintf("SELECT COUNT(*) as 'count' FROM brewing WHERE brewCategorySort='%s'",$row_styles['brewStyleGroup']);
		$result = mysql_query($query_entry_count, $brewing) or die(mysql_error());
		$row = mysql_fetch_array($result);
		if ($row_styles['brewStyleGroup'] == $filter) $selected = ' "selected"'; else $selected = '';
		if ($row['count'] > 0) { $style_choose .= '<option value="'.$_SERVER['SCRIPT_NAME'].'?section='.$section.'&go='.$go.'&action='.$action.'&filter='.$row_styles['brewStyleGroup'].'"'.$selected.'>'.$row_styles['brewStyleGroup'].' '.$row_styles['brewStyle'].' ('.$row['count'].' entries)</option>'; } 
	} while ($row_styles = mysql_fetch_assoc($styles));
	
	$style_choose .= '</select>';
	return $style_choose;   			
}

function table_location($table_id) { 
	include(CONFIG.'config.php');
	mysql_select_db($database, $brewing);
	$query_table = sprintf("SELECT tableLocation FROM judging_tables WHERE id='%s'", $table_id);
	$table = mysql_query($query_table, $brewing) or die(mysql_error());
	$row_table = mysql_fetch_assoc($table);
	
	$query_location = sprintf("SELECT judgingLocName,judgingDate,judgingTime FROM judging_locations WHERE id='%s'", $row_table['tableLocation']);
	$location = mysql_query($query_location, $brewing) or die(mysql_error());
	$row_location = mysql_fetch_assoc($location);
	$totalRows_location = mysql_num_rows($location);
	
	if ($totalRows_location == 1) {
    $table_location = $row_location['judgingLocName'].", ".dateconvert($row_location['judgingDate'], 3)." - ".$row_location['judgingTime'];
	}
	else $table_location = ""; 
	return $table_location;
}

function flight_count($table_id,$method) {
	include(CONFIG.'config.php');
	mysql_select_db($database, $brewing);
	$query_flights = sprintf("SELECT COUNT(*) as 'count' FROM judging_flights WHERE flightTable='%s'", $table_id);
	$flights = mysql_query($query_flights, $brewing) or die(mysql_error());
	$row_flights = mysql_fetch_assoc($flights);
	
	switch($method) {
		case "1": if ($row_flights['count'] > 0) return true; else return false;
		break;
		
		case "2": return $row_flights['count'];
		break;
	}
	
}

function score_count($table_id,$method) {
	include(CONFIG.'config.php');
	mysql_select_db($database, $brewing);
	$query_scores = sprintf("SELECT COUNT(*) as 'count' FROM judging_scores WHERE scoreTable='%s'", $table_id);
	$scores = mysql_query($query_scores, $brewing) or die(mysql_error());
	$row_scores = mysql_fetch_assoc($scores);
	
	switch($method) {
		case "1": if ($row_scores['count'] > 0) return true; else return false;
		break;
		
		case "2": return $row_scores['count'];
		break;
	}
	
}

// function to generate random number
function random_generator($digits,$method){
srand ((double) microtime() * 10000000);

//Array of alphabet
if ($method == "1") $input = array ("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
if ($method == "2") $input = array ("1","2","3","4","5","6","7","8","9");

$random_generator = "";// Initialize the string to store random numbers
for ($i=1;$i<$digits+1;$i++) { // Loop the number of times of required digits
	if(rand(1,2) == 1){// to decide the digit should be numeric or alphabet
	// Add one random alphabet 
	$rand_index = array_rand($input);
	$random_generator .=$input[$rand_index]; // One char is added
	}
	else
	{
	// Add one numeric digit between 1 and 10
	$random_generator .=rand(1,10); // one number is added
	} // end of if else
} // end of for loop 

return $random_generator;

} // end of function
	
function orphan_styles() { 
	include(CONFIG.'config.php');
	$query_styles = "SELECT id,brewStyle,brewStyleType FROM styles WHERE brewStyleGroup >= 29";
	$styles = mysql_query($query_styles, $brewing) or die(mysql_error());
	$row_styles = mysql_fetch_assoc($styles);
	$totalRows_styles = mysql_num_rows($styles);
	
	$query_style_types = "SELECT id FROM style_types WHERE styleTypeOwn = 'custom'";
	$style_types = mysql_query($query_style_types, $brewing) or die(mysql_error());
	$row_style_types = mysql_fetch_assoc($style_types);
	$totalRows_style_types = mysql_num_rows($style_types);
	
	do { $a[] = style_type($row_style_types['id'], "2", "bcoe"); } while ($row_style_types = mysql_fetch_assoc($style_types));

	$return = "";
	if ($totalRows_styles > 0) {
		do {
			if (!in_array($row_styles['brewStyleType'], $a)) { 
				if ($row_styles['brewStyleType'] > 3) $return .= "<p><a href='index.php?section=admin&amp;go=styles&amp;action=edit&amp;id=".$row_styles['id']."'><span class='icon'><img src='images/pencil.png' alt='Edit ".$row_styles['brewStyle']."' title='Edit ".$row_styles['brewStyle']."'></span></a>".$row_styles['brewStyle']."</p>";
			}
		} while ($row_styles = mysql_fetch_assoc($styles));
	}
	if ($return == "") $return .= "<p>All custom styles have a valid style type associated with them.</p>";
	return $return;

}


function bjcp_rank($rank) {
    switch($rank) {
		case "Apprentice": $return = "Level 1:";
		break;
		case "Recognized": $return = "Level 2:";
		break;
		case "Certified": $return = "Level 3:";
		break;
		case "National": $return = "Level 4:";
		break;
		case "Master": $return = "Level 5:";
		break;
		case "Grand Master": $return = "Level 6:";
		break;
		case "Honorary Master": $return = "Level 5:";
		break;
		case "Honorary Grand Master": $return = "Level 6:";
		break;
		case "Experienced": $return = "Level 0:";
		break;
		case "Professional Brewer": $return = "Level 2:";
		break;
		default: $return = "";
	}
	if (($rank != "None") && ($rank != "")) $return .= " ".$rank;
	return $return;
}


function srm_color($srm,$method) {
	if ($method == "ebc") $srm = (1.97 * $srm); else $srm = $srm;
	
    if ($srm >= 01 && $srm < 02) $return = "#f3f993";
elseif ($srm >= 02 && $srm < 03) $return = "#f5f75c";
elseif ($srm >= 03 && $srm < 04) $return = "#f6f513";
elseif ($srm >= 04 && $srm < 05) $return = "#eae615";
elseif ($srm >= 05 && $srm < 06) $return = "#e0d01b";
elseif ($srm >= 06 && $srm < 07) $return = "#d5bc26";
elseif ($srm >= 07 && $srm < 08) $return = "#cdaa37";
elseif ($srm >= 08 && $srm < 09) $return = "#c1963c";
elseif ($srm >= 09 && $srm < 10) $return = "#be8c3a";
elseif ($srm >= 10 && $srm < 11) $return = "#be823a";
elseif ($srm >= 11 && $srm < 12) $return = "#c17a37";
elseif ($srm >= 12 && $srm < 13) $return = "#bf7138";
elseif ($srm >= 13 && $srm < 14) $return = "#bc6733";
elseif ($srm >= 14 && $srm < 15) $return = "#b26033";
elseif ($srm >= 15 && $srm < 16) $return = "#a85839";
elseif ($srm >= 16 && $srm < 17) $return = "#985336";
elseif ($srm >= 17 && $srm < 18) $return = "#8d4c32";
elseif ($srm >= 18 && $srm < 19) $return = "#7c452d";
elseif ($srm >= 19 && $srm < 20) $return = "#6b3a1e";
elseif ($srm >= 20 && $srm < 21) $return = "#5d341a";
elseif ($srm >= 21 && $srm < 22) $return = "#4e2a0c";
elseif ($srm >= 22 && $srm < 23) $return = "#4a2727";
elseif ($srm >= 23 && $srm < 24) $return = "#361f1b";
elseif ($srm >= 24 && $srm < 25) $return = "#261716";
elseif ($srm >= 25 && $srm < 26) $return = "#231716";
elseif ($srm >= 26 && $srm < 27) $return = "#19100f";
elseif ($srm >= 27 && $srm < 28) $return = "#16100f";
elseif ($srm >= 28 && $srm < 29) $return = "#120d0c";
elseif ($srm >= 29 && $srm < 30) $return = "#100b0a";
elseif ($srm >= 30 && $srm < 31) $return = "#050b0a";
elseif ($srm > 31) $return = "#000000";
  else $return = "#ffffff";
return $return;
}

function getContactCount() {
	include(CONFIG.'config.php');
	mysql_select_db($database, $brewing);
	
	$query_contact_count = "SELECT COUNT(*) as 'count' FROM contacts";
	$result = mysql_query($query_contact_count, $brewing) or die(mysql_error());
	$row = mysql_fetch_assoc($result);
	$contactCount = $row["count"];
	mysql_free_result($result);
	return $contactCount;
}

function getContacts() {
	include(CONFIG.'config.php');
	mysql_select_db($database, $brewing);
	
	$query_contacts = "SELECT * FROM contacts ORDER BY contactLastName, contactPosition";
	$contacts = mysql_query($query_contacts, $brewing) or die(mysql_error());
	return $contacts;
}

function brewer_info($bid) {
	include(CONFIG.'config.php');
	mysql_select_db($database, $brewing);
	$query_brewer_info = sprintf("SELECT brewerFirstName,brewerLastName,brewerPhone1,brewerJudgeRank,brewerJudgeID FROM brewer WHERE id='%s'", $bid);
	$brewer_info = mysql_query($query_brewer_info, $brewing) or die(mysql_error());
	$row_brewer_info = mysql_fetch_assoc($brewer_info);
	$r = $row_brewer_info['brewerFirstName']."^".$row_brewer_info['brewerLastName']."^".$row_brewer_info['brewerPhone1']."^".$row_brewer_info['brewerJudgeRank']."^".$row_brewer_info['brewerJudgeID'];
	return $r;
}

function get_entry_count() {
	include(CONFIG.'config.php');
	mysql_select_db($database, $brewing);
	
	$query_paid = "SELECT COUNT(*) as 'count' FROM brewing WHERE brewPaid='Y' AND brewReceived='Y'";
	$paid = mysql_query($query_paid, $brewing) or die(mysql_error());
	$row_paid = mysql_fetch_assoc($paid);
	$r = $row_paid['count'];
	return $r;

}

?>