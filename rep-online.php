<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');


	//setting values for the order by and order type variables
	isset($_REQUEST['orderBy']) ? $orderBy = $_REQUEST['orderBy'] : $orderBy = "radacctid";
	isset($_REQUEST['orderType']) ? $orderType = $_REQUEST['orderType'] : $orderType = "asc";
	
	
	
	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query for listing of records on page: ";



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>
<script src="library/javascript/rounded-corners.js" type="text/javascript"></script>
<script src="library/javascript/form-field-tooltip.js" type="text/javascript"></script>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />
<link rel="stylesheet" href="css/form-field-tooltip.css" type="text/css" media="screen,projection" />
<link rel="stylesheet" type="text/css" href="library/js_date/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="library/js_date/select-free.css"/>
<![endif]-->
</head>

<?php
        include_once ("library/tabber/tab-layout.php");
?>

<?php

    include ("menu-reports.php");

?>

		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><? echo $l['Intro']['reponline.php']; ?>
				<h144>+</h144></a></h2>
				
		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo $l['helpPage']['reponline']; ?>
			<br/>
		</div>
		<br/>


<div class="tabber">

     <div class="tabbertab" title="Statistics">
        <br/>	
	
<?php

	include 'library/opendb.php';
        include 'include/common/calcs.php';
	include 'include/management/pages_numbering.php';		// must be included after opendb because it needs to read the CONFIG_IFACE_TABLES_LISTING variable from the config file
	
	//orig: used as maethod to get total rows - this is required for the pages_numbering.php page
	$sql = "SELECT Username, FramedIPAddress, CallingStationId, AcctStartTime, AcctSessionTime, NASIPAddress FROM ".$configValues['CONFIG_DB_TBL_RADACCT']." WHERE (AcctStopTime is NULL)";
	$res = $dbSocket->query($sql);
	$numrows = $res->numRows();


	/* we are searching for both kind of attributes for the password, being User-Password, the more
	   common one and the other which is Password, this is also done for considerations of backwards
	   compatibility with version 0.7        */
	
	$sql = "SELECT Username, FramedIPAddress, CallingStationId, AcctStartTime, UNIX_TIMESTAMP(`AcctStartTime`) ".
		" as AcctSessionTime, NASIPAddress FROM ".$configValues['CONFIG_DB_TBL_RADACCT'].
		" WHERE (AcctStopTime is NULL) ORDER BY $orderBy $orderType LIMIT $offset, $rowsPerPage";
	$res = $dbSocket->query($sql);
	$logDebugSQL = "";
	$logDebugSQL .= $sql . "\n";

	/* START - Related to pages_numbering.php */
	$maxPage = ceil($numrows/$rowsPerPage);
	/* END */

	echo "<form name='usersonline' method='get' >";

	echo "<table border='0' class='table1'>\n";
	echo "
		<thead>
			<tr>
			<th colspan='10' align='left'>

                                Select:
                                <a class=\"table\" href=\"javascript:SetChecked(1,'clearSessionsUsers[]','usersonline')\">All</a>

                                <a class=\"table\" href=\"javascript:SetChecked(0,'clearSessionsUsers[]','usersonline')\">None</a>
                        <br/>
                                <input class='button' type='button' value='".$l['button']['ClearSessions']."' onClick='javascript:removeCheckbox(\"usersonline\",\"mng-del.php\")' />
                                <br/><br/>
                ";

	if ($configValues['CONFIG_IFACE_TABLES_LISTING_NUM'] == "yes")
		setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType);

	echo "</th></tr>
			</thead>
	";

	if ($orderType == "asc") {
			$orderType = "desc";
	} else  if ($orderType == "desc") {
			$orderType = "asc";
	}

	echo "<thread> <tr>
		<th scope='col'>
		<a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=username&orderType=$orderType\">
		".$l['all']['Username']. "</a>
		</th>

		<th scope='col'>
		<a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=framedipaddress&orderType=$orderType\">
		".$l['all']['IPAddress']."</a>
		</th>

		<th scope='col'>
		<a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=acctstarttime&orderType=$orderType\">
		".$l['all']['StartTime']."</a>
		</th>

		<th scope='col'>
		<a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=acctsessiontime&orderType=$orderType\">
		".$l['all']['TotalTime']."</a>
		</th>

		<th scope='col'>
		<a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=nasipaddress&orderType=$orderType\">
		".$l['all']['NASIPAddress']."</a>
		</th>

	</tr> </thread>";

	while($row = $res->fetchRow()) {

		$dateDiff = (time() - $row[4]);
		$totalTime = seconds2time($dateDiff);

		echo "<tr>
				<td> <input type='checkbox' name='clearSessionsUsers[]' value='$row[0]'>
					<a class='tablenovisit' href='javascript:return;'
					onclick=\"javascript:__displayTooltip();\" 
					tooltipText=\"
						<a class='toolTip' href='mng-edit.php?username=$row[0]'>".$l['Tooltip']['UserEdit']."</a>
						<a class='toolTip' href='config-maint-disconnect-user.php?username=$row[0]&nasaddr=$row[5]'>
						".$l['all']['Disconnect']."</a>
						<br/>\"
					>$row[0]</a>
					</td>
				<td> <b>IP:</b> $row[1]<br/> <b>MAC:</b> $row[2]</td>
				<td> $row[3] </td>
				<td> $totalTime </td>
				<td> $row[5] </td>
		</tr>";
	}

        echo "
                                        <tfoot>
                                                        <tr>
                                                        <th colspan='10' align='left'>
        ";
        setupLinks($pageNum, $maxPage, $orderBy, $orderType);
        echo "
                                                        </th>
                                                        </tr>
                                        </tfoot>
                ";
	
	echo "</table>";
	include 'library/closedb.php';
		
?>

	</div>


     <div class="tabbertab" title="Graph">
        <br/>


<?php
	echo "<center>";
	echo "<img src=\"library/graphs-reports-online-users.php\" />";
	echo "</center>";
?>

	</div>
</div>



<?php
	include('include/config/logging.php');
?>
		
		</div>
		
		<div id="footer">
		
								<?php
        include 'page-footer.php';
?>


<script type="text/javascript">
var tooltipObj = new DHTMLgoodies_formTooltip();
tooltipObj.setTooltipPosition('right');
tooltipObj.setPageBgColor('#EEEEEE');
tooltipObj.setTooltipCornerSize(15);
tooltipObj.initFormFieldTooltip();
</script>
		
		</div>
		
</div>
</div>


</body>
</html>
