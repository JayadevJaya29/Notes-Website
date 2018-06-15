<?php 
	 error_reporting(E_ALL);
	 date_default_timezone_set("Asia/Kolkata");
	 session_start();
	 $_SESSION['MESSSAGE']="";
	 unset($_SESSION['email']);
	 unset($_SESSION['name']);
	 $conn= new mysqli('localhost','root','root23','users');
	 if ($conn->connect_error) {
     die("Connection failed: " . $conn->connect_error);}
     $emailerror=$passworderror="";
	 $name=$email=$password="";
	 $errors=0;
	 if($_SERVER["REQUEST_METHOD"]=="POST")
	 {
	 	if(empty($_POST["email"]))
     	{
     		$emailerror="Please enter an email.";
     		$errors++;
     	}
     	else
     	{
     		$email=mysqli_real_escape_string($conn,$_POST["email"]);
     		$password=mysqli_real_escape_string($conn,$_POST["password"]);
     	}
     	if($errors==0)
     	{
     		$query=$conn->prepare("SELECT * FROM accounts WHERE email=?");
     		$query->bind_param("s",$email);
     		$query->execute();
     		$result=$query->get_result();
     		$row=mysqli_fetch_assoc($result);
     		if(mysqli_num_rows($result)==0)
     			$emailerror="Email not registered.";
     		else if(!password_verify($password,$row['password']))
     			$passworderror="Incorrect Password";
     		if(mysqli_num_rows($result)==1 && password_verify($password,$row['password']))
     		{
     			$_SESSION['name']=$row['name'];
     			$_SESSION['email']=$row['email'];
     			header('location:dashboard.php');
     		}
     		else
     			$_SESSION['MESSAGE']="Login Failed! Enter correct credentials.";
     	}
	 }
 ?>
<!DOCTYPE html>
<html>
<head>
	<title>Sign In</title>
	<link rel="stylesheet" href="sign.css" type="text/css">
</head>
<body>
	<div class="x">
		<h2>Login</h2>
	<div class="y">
		<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
			<input type="text"  class="z" name="email" placeholder="E-mail">
			<span class="error"><?php echo $emailerror; ?></span>
			<br><br>
			<input type="password"  class="z" name="password" placeholder="Password">
			<span class="error"><?php echo $passworderror; ?></span>
			<br><br>
			<input type="submit"  class="button" value="Sign In!">
		</form>
		<br>
		<p>New member? <a href="signup.php">Register Now!</a></p>
		
	</div>
	</div>
</body>
</html>