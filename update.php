<!doctype html>
<html>
<head>
	<title>Police Emergency Service System</title>
    <link href="header_style.css" rel="stylesheet" type="text/css">
    <link href="content_style.css" rel="stylesheet" type="text/css">
	
	<!-- start of step 11 -->
	<!-- part 3 -->
<?php
if (isset($_POST["btnUpdate"])) {
	
	require_once 'db.php';
	
	// create database connection
	$mysqli = mysqli_connect (DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
	// check connection 
	if ($mysqli->connect_errno) {
		die("Failed to connect to MySQL: ".$mysqli->connect_errno);
	}
	
	// update patrol car status
	$sql = "UPDATE patrolcar SET patrolcar_status_id = ? WHERE patrolcar_id = ? ";
	
	if (!($stmt = $mysqli->prepare($sql))) {
		die("Prepare failed: ".$mysqli->errno);
	}
	
	if (!$stmt->bind_param('ss', $_POST['patrolCarStatus'], $_POST['patrolCarId'])) {
		die("Binding parameters failed: ".$stmt->errno);
	}
	
	if (!$stmt->execute()) {
		die("Update patrolcar table failed: ".$stmt->errno);
	}
	
	// if patrol car status is Arrived (4) then capture the time of arrival
	if ($_POST["patrolCarStatus"] == '4') {
		$sql = "UPDATE dispatch SET time_arrive = NOW() WHERE time_arrive is NULL AND patrolcar_id = ?";
	
	
		if (!($stmt = $mysqli->prepare($sql))) {
			die("Prepare failed: ".$mysqli->errno);
		}
		
		if (!$stmt->bind_param('s', $_POST["patrolCarId"])) {
			die("Binding parameters failed: ".$stmt->errno);
		}
		
		if (!$stmt->execute()) {
			die("Update dispatch table failed 2: ".$stmt->errno);
		}
	}
else if ($_POST["patrolCarStatus"] == '3') { // else if patrol car status us FREE (3) then capture the time of completion 
	// First, retrieve the incident ID from dispatch table handled by that patrol car
	$sql = "SELECT incident_id FROM dispatch WHERE time_completed is NULL AND patrolcar_id = ?";
	
	if (!($stmt = $mysqli->prepare($sql))) {
		die("Prepare failed: ".$mysqli->errno);
	}
	
	if (!$stmt->bind_param('s', $_POST["patrolCarId"])) {
		die("Binding parameters failed: ".$stmt->errno);
	}
	
	if (!$stmt->execute()) {
		die("Execute failed: ".$stmt->errno);
	}

	
	$incidentId;
	
	while ($row = $resultset->fetch_assoc()) {
		$incidentId = $row["incident_id"];
	}
	
	// next update dispatch table
	$sql = "UPDATE dispatch SET time_completed = NOW()WHERE time_completed is NULL AND patrolcar_id = ?";
	
	if (!($stmt = $mysqli->prepare($sql))) {
		die("Prepare failed: ".$mysqli->errno);
	}
	
	if (!$stmt->bind_param('s', $_POST["patrolCarId"])) {
		die("Binding parameters failed: ".$stmt->errno);
	}
	
	if (!$stmt->execute()) {
		die("Update dispatch table failed : ".$stmt->errno);
	}
	
	// last but not least, update incident table to completed (3) all patrol car attended to it are FREE now
	
	$sql ="UPDATE incident SET incident_status_id = '3' WHERE incident_id = '$incidentId' AND NOT EXIST (SELECT * FROM dispatch WHERE time_completed IS NULL AND incident_id = '$incidentId')";
	
	if (!($stmt = $mysqli->prepare($sql))) {
		die("Prepare failed 11: ".$mysqli->errno);
	}
	
	if (!$stmt->execute()) {
		die("Update dispatch table failed: ".$stmt->errno);
	}
	
	$resultset->close();
}

$stmt->close();
$mysqli->close();
	?>

<script type="text/javascript">window.location="./logcall.php";</script>
<?php } ?>
</head>

<body>
<!-- start of step 7 -->
	<!-- part 1 -->
<?php require_once 'nav.php' ?>
<br><br>
<?php
if (!isset($_POST["btnSearch"])) {
?>	
<!-- create form to search for patrol car based on id -->
<form name="form1" method="post" action="<?php echo htmlentities ($_SERVER["PHP_SELF"]); ?>">
	<table class="ContentStyle">
		<tr></tr>
		<tr>
			<td>Patrol Car ID: </td>
			<td><input type="text" name="patrolCarId" id="patrolCarId"></td>
			<!-- must validate for no empty entry -->
			<td><input type="submit" name="btnSearch" id="btnSearch" value="Search"></td>
		</tr>
	</table>
</form>
<?php
} else 
// End of step 7 for part 7
// start of step 9
//		part 2
// past back here after clicking the btnSearch
{	require_once 'db.php';
	
	// create database connection 
	$mysqli = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
	// check connection
	if ($mysqli->connect_errno) {
		die("Failed to connect to MySQL: ".$mysqli->connect_errno);
	}
	
	// retrieve patrol car detail
	$sql = "SELECT * FROM patrolcar WHERE patrolcar_id = ?";
	
	if (!($stmt = $mysqli->prepare($sql))) {
		die("Prepare failed: ".$mysqli->errno);
	}
	
	if (!$stmt->bind_param('s', $_POST['patrolCarId'])) {
		die("Binding parameters failed: ".$stmt->errno);
	}
	
	if (!$stmt->execute()) {
		die("Execute failed: ".$stmt->errno);
	}
	
	if(!($resultset = $stmt->get_result())) {
		die("Getting result set failed: ".$stmt->errno);
	}
	
	// if the patrol car does not exist, redirect back to update.php
	if ($resultset->num_rows == 0) {
		?>
			<script type="text/javascript">window.location"./update.php";</script>
	<?php }
	
	//else if the patrol car found
	$patrolCarId;
	$patrolCarStatusId;
	
	while ($row = $resultset->fetch_assoc()) {
		$patrolCarId = $row['patrolcar_id'];
		$patrolCarStatusId = $row['patrolcar_status_id'];
	}
	
	// retrieve from patrolcar_status_id table for populating the combo box
	$sql = "SELECT * FROM patrolcar_status";
	if (!($stmt=$mysqli->prepare($sql))) {
		die("Prepare failed: ".$mysqli->errno);
	}
	
	if (!$stmt->execute()) {
		die("Execute failed: ".$mysqli->errno);
	}
	
	if (!($resultset = $stmt->get_result())) {
		die("Getting result set failed: ".$stmt->errno);
	}

	$patrolCarStatusArray; // an array variable
	
	while ($row = $resultset->fetch_assoc()) {
		$patrolCarStatusArray[$row['patrolcar_status_id']] = $row['patrolcar_status_desc'];
	}
	
	$stmt->close();
	$resultset->close();
	$mysqli->close();

?>
<!-- end of step 9  -->

<!-- start of step 10 -->
	<!-- display a form for operator to update status of patrolcar -->
<form name="form2" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">

<table class="ContentStyle">
	<tr></tr>
	<tr>
		<td>ID: </td>
		<td>
		<?php echo $patrolCarId ?>
		<input type="hidden" name="patrolCarId" id="patrolCarId" value="<?php echo $patrolCarId ?>">
		</td>
	</tr>
	
	<tr>
		<td>Status: </td>
		<td>
		<select name="patrolCarStatus" id="patrolCarStatus">
		<?php foreach($patrolCarStatusArray as $key => $value) {?>
		<option value="<?php echo $value ?>"
						<?php if($key==$patrolCarStatusId) {?> selected="selected"
						<?php } ?>
		>
			<?php echo $value ?>
		</option>
		<?php } ?>
		</select></td>
	</tr>
	
	<tr>
		<td>
			<input type="reset" name="btnCancel" id="btnCancel" value="Reset">
		</td>
		<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="btnUpdate" id="btnUpdate" value="Update">
		</td>
	</tr>
</table>
</form>
<?php } ?>
</body>
</html>