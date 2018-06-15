<?php 
	error_reporting(E_ALL);
	date_default_timezone_set("Asia/Kolkata");
	$conn= new mysqli('localhost','root','root23','users');
	if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);}
    session_start();
    if(isset($_SESSION['LISTNAME'])&&isset($_SESSION['email']))    #view your list
    {
    	$listname=$_SESSION['LISTNAME'];
    	$email=$_SESSION['email'];
    	$name=$_SESSION['name'];
    	$i=1;
    	echo "<h1>Welcome ".$name.".</h1>";
    	echo "<h2><a href=\"dashboard.php\">Dashboard</a><br><a href=\"signout.php\">Logout</a></h2>";
    	$sql=$conn->prepare("SELECT * FROM lists WHERE email=? AND listname=? ORDER BY task");
    	$sql->bind_param("ss",$email,$listname);
    	$sql->execute();
    	$result=$sql->get_result();
    	echo "<form action=\"";echo htmlspecialchars($_SERVER["PHP_SELF"]);echo "\" method=\"post\">";
    	echo "<br><div class=\"x\"><div class=\"y\"><input type=\"text\" name=\"listname1\" value=\"".$listname."\"<br>";
    	echo "<table><tr><th>S.no	</th> <th> Tasks </th> </tr>";
    	while($row=mysqli_fetch_assoc($result))
    	{
    		if($row['checked']==1)
    			$c="checked";
    		else
    			$c="";
    		echo "<tr><td><input type=\"checkbox\" name=\"".$i."\" ".$c." >".$i."</td> <td><input type=\"text\" name=\"task".$i."\" value=\"".$row['task']."\"</td></tr>";
    		$i++;
    	}	
    	echo"</table><br><br><br><input type=\"text\" name=\"add1\" placeholder=\"Add Task\"> <input type =\"submit\" name=\"update\" value=\"Update\"></form></div></div>";
	    if(isset($_POST["update"]))
	    {
	    	$sql=$conn->prepare("DELETE FROM lists WHERE email=? AND listname=?");
	    	$sql->bind_param("ss",$email,$listname);
	    	$sql->execute();
	    	if(!empty($_POST['listname1']))
	    		$listname=$_POST['listname1'];
	    	$x=$_SESSION['LISTNAME'];
	    	$_SESSION['LISTNAME']=$listname;
	    	$sql=$conn->prepare("UPDATE listaccess SET listname=? WHERE fromuser=? AND listname=?");
	    	$sql->bind_param("sss",$listname,$email,$x);
	    	$sql->execute();
	    	for($j=1;$j<$i;$j++)
			{
				$count=0;
				if(isset($_POST["$j"]))
					$count=1;
				if(empty($_POST["task$j"]))
					continue;
				$task=$_POST["task$j"];
				$date=date('Y-m-d H:i:s');
				$sql=$conn->prepare("INSERT INTO lists (email,listname,task,checked,dates) VALUES (?,?,?,?,?)");
				$sql->bind_param("sssis",$email,$listname,$task,$count,$date);
				$sql->execute();
			}
			if(!empty($_POST["add1"]))
			{
				$task=$_POST["add1"];
				$date=date('Y-m-d H:i:s');
				$count=0;
				$sql=$conn->prepare("INSERT INTO lists (email,listname,task,checked,dates) VALUES (?,?,?,?,?)");
				$sql->bind_param("sssis",$email,$listname,$task,$count,$date);
				$sql->execute();
			}
			header('location:view.php');
	    }
	}
	if(isset($_SESSION['listname'])&&isset($_SESSION['email']))    #view list of another user
	{
		$fromuser=$_SESSION['fromuser'];
		$email=$_SESSION['email'];
		$listname=$_SESSION['listname'];
		$name=$_SESSION['name'];
		$i=1;
		echo "<h1>Welcome ".$name.". This is List : ".$listname." which you have been given edit permissions from ".$fromuser."</h1>";
		echo "<h2><a href=\"dashboard.php\">Dashboard</a><br><a href=\"signout.php\">Logout</a></h2>";
    	$sql=$conn->prepare("SELECT * FROM lists WHERE email=? AND listname=? ORDER BY task");
    	$sql->bind_param("ss",$fromuser,$listname);
    	$sql->execute();
    	$result=$sql->get_result();
    	echo "<form action=\"";echo htmlspecialchars($_SERVER["PHP_SELF"]);echo "\" method=\"post\">";
    	echo "<div class=\"x\"><div class=\"y\"><table><tr><th>S.no	</th> <th> Tasks </th> </tr>";
    	while($row=mysqli_fetch_assoc($result))
    	{
    		if($row['checked']==1)
    			$c="checked";
    		else
    			$c="";
    		echo "<tr><td><input type=\"checkbox\" name=\"".$i."\" ".$c." >".$i."</td> <td><input type=\"text\" name=\"task".$i."\" value=\"".$row['task']."\"</td></tr>";
    		$i++;
    	}	
    	echo"</table><br><br><input type=\"text\" name=\"add2\" placeholder=\"Add Task\"> <input type =\"submit\" name=\"update2\" value=\"Update\"></form></div></div>";
	    if(isset($_POST["update2"]))
	    {
	    	$sql=$conn->prepare("DELETE FROM lists WHERE email=? AND listname=?");
	    	$sql->bind_param("ss",$fromuser,$listname);
	    	$sql->execute();
	    	for($j=1;$j<$i;$j++)
			{
				$count=0;
				if(isset($_POST["$j"]))
					$count=1;
				if(empty($_POST["task$j"]))
					continue;
				$task=$_POST["task$j"];
				$date=date('Y-m-d H:i:s');
				$sql=$conn->prepare("INSERT INTO lists (email,listname,task,checked,dates) VALUES (?,?,?,?,?)");
				$sql->bind_param("sssis",$fromuser,$listname,$task,$count,$date);
				$sql->execute();
			}
			if(!empty($_POST["add2"]))
			{
				$task=$_POST["add2"];
				$date=date('Y-m-d H:i:s');
				$count=0;
				$sql=$conn->prepare("INSERT INTO lists (email,listname,task,checked,dates) VALUES (?,?,?,?,?)");
				$sql->bind_param("sssis",$fromuser,$listname,$task,$count,$date);
				$sql->execute();
			}
			header('location:view.php');
	    }
	}
	if(isset($_SESSION['notetitle'])&&isset($_SESSION['email']))      #view note
	{
		$email=$_SESSION['email'];
		$name=$_SESSION['name'];
		$title=$_SESSION['notetitle'];
		$sql=$conn->prepare("SELECT * FROM notes WHERE title=? AND email=?");
		$sql->bind_param("ss",$title,$email);
		$sql->execute();
		$result=$sql->get_result();
		$row=mysqli_fetch_assoc($result);
		echo "<h1>Welcome ".$name.".</h1>";
		echo "<h2><a href=\"dashboard.php\">Dashboard</a><br><a href=\"signout.php\">Logout</a></h2>";
		echo "<form action=\"";echo htmlspecialchars($_SERVER["PHP_SELF"]);echo "\" method=\"post\">";
		echo "<div class=\"x\"><div class=\"y\"><h4>Title : </h4><input type=\"text\" name=\"title\" value=\"".$title."\"><br><br><h4>Note : </h4><input name=\"note\" value=\"".$row['note']."\" type=\"text\" id=\"note\"><br><br><h4>Image : </h4><img src=\"".$row['image']."\" alt=\" \"><br><br><input type=\"submit\" value=\"update\" name=\"updatenote\"><br></form></div></div>";
		if(isset($_POST["updatenote"]))
		{
			$title2=trim($_POST["title"]);
			$note=trim($_POST["note"]);
			$sql=$conn->prepare("UPDATE notes SET title=?,note=? WHERE email=? AND title=?");
			$sql->bind_param("ssss",$title2,$note,$email,$title);
			$sql->execute();
			$_SESSION['notetitle']=$title2;
			header('location:view.php');
		}
	}
   	echo"<!DOCTYPE html>
    <html>
    <head>
    <title>View</title>
    <link rel=\"stylesheet\" href=\"dashboard.css\" type=\"text/css\">
    </head>
    <body>
    <br><br>
    </body>
    </html>";
?>