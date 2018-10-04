<?php
	session_start();

    // initialize variables
	$servername = "localhost";
	$username = "username";
	$password = "password";
	$dbname = "crud";

	$tablename = "tablename";

    // connect to database
	$conn = mysqli_connect($servername, $username, $password, $dbname);         
	if(!$conn ){
		die("Connection failed: " . mysqli_connect_error());
	}

	// Get columns
	$query = "SHOW COLUMNS FROM $tablename";
	$columnsfetch = mysqli_query($conn,$query);
	$columns = mysqli_fetch_all($columnsfetch);	// Save column to array

    // if save button is clicked
    if(isset($_POST['save'])){
		$fields = $values = array();
		$exclude = "save";

		if( !is_array($exclude) ) 
			$exclude = array($exclude);

		foreach( array_keys($_POST) as $key ) {
			if( !in_array($key, $exclude) ) {
				$field = $columns[$key][0];
				$fields[] = "`$field`";
				$values[] = "'" . mysqli_real_escape_string($conn, $_POST[$key]) . "'";
			}
		}
		
		// Generate string with ","
		$fields = implode(",", $fields);
		$values = implode(",", $values);

		$query = "INSERT INTO `$tablename` ($fields) VALUES ($values)";
        mysqli_query($conn, $query);
        $_SESSION['msg'] = "Record saved";
		header('Location: '.$_SERVER['PHP_SELF']); // redirect to the same page after inserting
		die;
    }

    // update records
    if (isset($_POST['update'])) {
		$fields = array();    
		$exclude = ["update", "id"];

		if( !is_array($exclude) ) 
			$exclude = array($exclude);

        $id =  mysqli_real_escape_string($conn, $_POST['id']);

		foreach( array_keys($_POST) as $key ) {
			if( !in_array($key, $exclude) ) {
				$field = $columns[$key][0];
				$fields[] = "$field='" . mysqli_real_escape_string($conn, $_POST[$key]) . "'";
			}
		}

		$fields = implode(",", $fields);

        mysqli_query($conn, "UPDATE $tablename SET $fields WHERE id='$id'");
		$_SESSION['msg'] = "Record updated";
		header('Location: '.$_SERVER['PHP_SELF']);
		die;
    }

    if(isset($_GET['del'])){
        $id = $_GET['del'];
        mysqli_query($conn, "DELETE FROM $tablename WHERE id='$id'");
        $_SESSION['msg'] = "Record deleted";
		header('Location: '.$_SERVER['PHP_SELF']);
		die;
    }

    // retrieve record
    $result = mysqli_query($conn, "SELECT * FROM $tablename");
	mysqli_close($conn);
?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous">
	<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js" integrity="sha384-cs/chFZiN24E4KMATLdqdvsezGxaGsi4hLGOzlXwp5UZB1LY//20VyM2taTB4QvJ" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js" integrity="sha384-uefMccjFJAIv6A+rW+L4AHf99KvxDjWSu1z9VI8SKNVmz4sk7buKt/6v9KI65qnm" crossorigin="anonymous"></script>
    <title>Zadanie 8(CRUD)</title>

	<script>
	/**
		if edit button is clicked
		change text in row to input field
	 */
	function edit(rowId) {
		cells = document.getElementById(rowId).children;
		for(var i = 2; i<cells.length-1; i++){
			cells[i].innerHTML = '<input type="text" value=' + cells[i].innerHTML + '>' + '<input type="hidden" value=' + cells[i].innerHTML + '>';
		}
		cells[cells.length-1].innerHTML = '<a class="btn btn-danger" onclick="back(' + rowId + ')">Back</a> ' + 
							'<a class="btn btn-primary" onclick="confirm(' + rowId + ')">Confirm</a>';
	}

	/**
		if back button is clicked
	 */
	function back(rowId){
		cells = document.getElementById(rowId).children;
		for(var i = 2; i<cells.length-1; i++){
			cells[i].innerHTML = cells[i].children[1].value;
		}
		cells[cells.length-1].innerHTML = '<a class="btn btn-success" onclick="edit(' + rowId + ')">Edit</a> ' +
							'<a class="btn btn-danger" href="?del=' + rowId + '">Delete</a>';
	}

	/**
		if confirm button is clicked
	 */
	function confirm(rowId){
		cells = document.getElementById(rowId).children;
		
		params = {id: cells[1].innerHTML};

		for(var i = 2; i< cells.length-1; i++){
			params[i-1] = cells[i].children[0].value;
		}

		params["update"] = "";

		post('', params, "post");
	}
	
	/**
		Send request
	 */
	function post(path, params, method) {
		method = method || "post"; // Set method to post by default if not specified.

		// The rest of this code assumes you are not using a library.
		// It can be made less wordy if you use one.
		var form = document.createElement("form");
		form.setAttribute("method", method);
		form.setAttribute("action", path);

		for(var key in params) {
			if(params.hasOwnProperty(key)) {
				var hiddenField = document.createElement("input");
				hiddenField.setAttribute("type", "hidden");
				hiddenField.setAttribute("name", key);
				hiddenField.setAttribute("value", params[key]);

				form.appendChild(hiddenField);
			}
		}

		document.body.appendChild(form);
		form.submit();
	}
	</script>
  </head>
<body style="background: url('image.jpeg') no-repeat;">

<div class="container">
	<!-- Show message if is set -->
    <?php if (isset($_SESSION['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
    		<button type="button" class="close" data-dismiss="alert">&times;</button>
            <?php
				echo "<strong>Success! </strong>";
                echo $_SESSION['msg'];
				unset($_SESSION['msg']);
            ?>
        </div>
	<?php endif; ?>
 
    <table class="table table-dark table-hover">
        <thead>
            <tr>
				<th>#</th>	
				<?php foreach ($columns as $col) { ?>
					<th><?php echo $col[0]; ?></th>
				<?php } ?>
                <th class ="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
			<!-- generate table of content (i = number of row) -->
            <?php $i = 0; while ( $row = mysqli_fetch_array($result)) { $i++;?>
                <tr id="<?php echo $row['id']; ?>">
						<?php echo "<td>$i</td>";?> 
					<?php foreach ($columns as $col) { ?>
						<td><?php echo $row[$col[0]]; ?></td>
					<?php } ?>
					<td class ="text-right">
						<a class="btn btn-success" onclick="edit(<?php echo $row['id']; ?>)">Edit</a>
						<a class="btn btn-danger" href="<?php echo '?del=' . $row['id']; ?>">Delete</a>
					</td>
                </tr>
            <?php } ?>
        </tbody>
		<tfoot>
    		<tr>
				<td></td>
				<td><form id="form" method="post"></td>
				<?php for ($i = 1; $i<count($columns); $i++) { ?>
					<td><input form="form" type="text" name="<?php echo $i; ?>"></td>
				<?php } ?>
				<td class ="text-right"><button form="form" class="btn btn-primary" type="submit" name="save" class="btn">Save</button></td>
    		</tr>
  		</tfoot>
    </table>

</div>
</body>
</html>