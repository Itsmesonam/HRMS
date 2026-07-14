<?php
$conn = mysqli_connect("localhost", "root", "", "hrms");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
if(isset($_POST['register']))
{
	$firstname = $_POST['firstname'];
	$lastname = $_POST['lastname'];
	$password = $_POST['password'];
	$cpassword = $_POST['cpassword'];
	$gender = $_POST['gender'];
	$email = $_POST['email'];
	$phone = $_POST['phone'];
	$address = $_POST['address'];

	if($password == $cpassword)
	{
		$query = "INSERT INTO users(firstname,lastname,password,gender,email,phone,address)
		VALUES('$firstname','$lastname','$password','$gender','$email','$phone','$address')";

		$data = mysqli_query($conn,$query);

		if($data)
		{
			echo "<script>alert('Registration Successful');</script>";
		}
		else
		{
			echo "<script>alert('Registration Failed');</script>";
		}
	}
	else
	{
		echo "<script>alert('Password and Confirm Password do not match');</script>";
	}
}

?>

<!DOCTYPE html>

<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="css/register.css">
	<title>HRMS </title>
</head>
<body>
	<div class="container">
		<div class="title">
			Registration Form
		</div>

	<form action="" method="POST">
		<div class="input_field">
			<label>First Name</label>
			<input type="text" class="input" >
		</div>


	<div class="form">
		<div class="input_field">
			<label>Last Name</label>
			<input type="text" class="input" >
		</div>
	<div class="form">
		<div class="input_field">
			<label>Password</label>
			<input type="password" class="input" >
		</div>
	<div class="form">
		<div class="input_field">
			<label>confirm Password</label>
			<input type="password" class="input" >
		</div>
	<div class="form">
		<div class="input_field">
			<label>Gender</label>
			<select>
				<option>select</option>
				<option>Male</option>
				<option>Female</option>
			</select>
		</div>
	<div class="form">
		<div class="input_field">
			<label>Email</label>
			<input type="text" class="input" >
		</div>


	<div class="form">
		<div class="input_field">
			<label>Phone</label>
			<input type="text" class="input" >
		</div>
	<div class="form">
		<div class="input_field">
			<label>Address</label>
			<textarea class="input" name="address"></textarea>
			
		</div>
	<div class="input_field terms">
		<label Class="check-label">
			<input type="checkbox" class="checkbox">
			<span class="checkmarks"></span>
		
		</label>
		<p>I agree to the terms and conditions</p>
		
	</div>
	<div class="input_field"></div>
	<input type="submit" value="Register" class="btn">
	</form>
</body>
</html>