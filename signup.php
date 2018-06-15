<?php 
	 error_reporting(E_ALL);
	 date_default_timezone_set("Asia/Kolkata");
	 session_start();
	 $_SESSION['MESSSAGE']="";
	 $nameerror=$emailerror=$passworderror=$passwordmatcherror="";
	 $name=$email=$password1=$password2="";
	 $errors=0;
	 $conn= new mysqli('localhost','root','root23','users');
	 if ($conn->connect_error) {
     die("Connection failed: " . $conn->connect_error);}
     if($_SERVER["REQUEST_METHOD"]=="POST")
     {
     	if(empty($_POST["name"]))
     	{
     		$nameerror="Please enter a name.";
     		$errors++;
     	}
     	else
     	{
     		$name=trim(mysqli_real_escape_string($conn,$_POST["name"]));
     		if (!preg_match("/^[a-zA-Z ]*$/",$name)) {
     		 $nameerror = "Only letters and white space allowed";
     		 $errors++;}
     	}
     	if(empty($_POST["email"]))
     	{
     		$emailerror="Please enter an email.";
     		$errors++;
     	}
     	else
     	{
     		$email=trim(mysqli_real_escape_string($conn,$_POST["email"]));
     		if(!filter_var($email,FILTER_VALIDATE_EMAIL)) {
     		 $emailerror="Not a valid email format.";
     		 $errors++;}
     	}
     	$query="SELECT email FROM accounts WHERE email='".$email."'";
     	$queryresult= $conn->query($query) ;
     	if($queryresult->num_rows>0)
     	{
     		$emailerror="email already registered. Login instead";
     		$errors++;
     	}
     	if(empty($_POST["password1"])||(strlen($_POST["password1"])<8))
     	{
     		$passworderror="Please enter a strong password of atleast 8 characters.";
     		$errors++;
     	}
     	else
     	{
     		$password1=(mysqli_real_escape_string($conn,$_POST["password1"]));
     		$password2=(mysqli_real_escape_string($conn,$_POST["password2"]));
     		if($password1!=$password2)
     		{
     			$passwordmatcherror="Passwords don't match!";
     			$errors++;
     		}

     	}
     	if($errors==0)
     	{
     		$password1=password_hash($password1,PASSWORD_DEFAULT);
     		$sql=$conn->prepare("INSERT INTO accounts (email,name,password) VALUES (?,?,?) ");
     		$sql->bind_param("sss",$email,$name,$password1);
     		if($sql->execute())
     			header('location:signin.php');
     		else
     			$_SESSION['MESSSAGE']="Registration Failed!"; 
     	}
     }
 ?>

<!DOCTYPE html>
<html>
	<head>
		<title>Sign Up</title>
		<link rel="stylesheet" href="sign.css" type="text/css">
	</head>
	<body>
		<div class="x">
			<h2>Sign Up Now!</h2>
		<div class="y">
			<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" >
				<input type="text" class="z" name="name" placeholder="Name*">
				<span class="error"><?php echo $nameerror; ?></span>
				<br><br>
				<input type="text" class="z"  name="email" placeholder="E-mail*" >
				<span class="error"><?php echo $emailerror; ?></span>
				<br><br>
				<input type="password" class="z"  name="password1" placeholder="Password*">
				<span class="error"><?php echo $passworderror; ?></span>
				<br><br>
				<input type="password" class="z"  name="password2" placeholder="Confirm Password*">
				<span class="error"><?php echo $passwordmatcherror; ?></span>
				<br><br>
				<input type="submit" class="button" value="Sign Up!">
			</form>
			<br>
			<p>Already a member? <a href="signin.php">Login</a></p>
			<br>
			<span class="error"><?php echo $_SESSION['MESSSAGE']; ?></span>
		</div>	
		</div>
	</body>
</html>