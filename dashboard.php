<?php 
	error_reporting(E_ALL);
	date_default_timezone_set("Asia/Kolkata");
	$conn= new mysqli('localhost','root','root23','users');
	if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);}
    session_start();
    $email=$usererror=$nameerror=$taskerror=$noteerror=$imageerror=$titleerror="";
    if(isset($_SESSION['email'])&&isset($_SESSION['name']))
    {
        unset($_SESSION['listname']);
        unset($_SESSION['fromuser']);
        unset($_SESSION['LISTNAME']);
        unset($_SESSION['notetitle']);
        $labelsort="";
        $_SESSION['sort']="dates";
    	echo "<h1>Welcome ".$_SESSION['name']."<h1>";
    	echo "<h2><a href=\"signout.php\">Logout</a></h2>";
    	$email=$_SESSION['email'];
        if(isset($_POST["view"])&&(!empty($_POST["Select"])))   #view list
        {
            $list=trim($_POST["Select"]);
            $_SESSION['LISTNAME']=$list;
            header('location:view.php');
        }
        if(isset($_POST["viewtask"])&&(!empty($_POST["Selecttask"])))    #view another user's list
        {
            $id=trim($_POST["Selecttask"]);
            $sql=$conn->prepare("SELECT * FROM listaccess WHERE id=?");
            $sql->bind_param("i",$id);
            $sql->execute();
            $result=$sql->get_result();
            $row=mysqli_fetch_assoc($result);
            $_SESSION['listname']=$row['listname'];
            $_SESSION['fromuser']=$row['fromuser'];
            header('location:view.php');
        }
        if(isset($_POST["viewnote"])&&(!empty($_POST["selectnote"])))    #view note
        {
            $title=trim($_POST["selectnote"]);
            $_SESSION['notetitle']=$title;
            header('location:view.php');
        }
        if(isset($_POST["delete"])&&(!empty($_POST["Select"])))      #delete list
        {
            $list=trim($_POST["Select"]);
            $sql=$conn->prepare("DELETE FROM lists WHERE email=? AND listname=?");
            $sql->bind_param("ss",$email,$list);
            $sql->execute();
            $sql=$conn->prepare("DELETE FROM listaccess WHERE fromuser=? AND listname=?");
            $sql->bind_param("ss",$email,$list);
            $sql->execute();
            header('location:dashboard.php');
        }
        if(isset($_POST['addtask']))    #add list
        {
            $errors=0;
            $email=$_SESSION['email'];
            $name=$_SESSION['name'];
            if(empty($_POST["listname"]))
            {
                $nameerror="Please enter a name.";
                $errors++;
            }
            if(empty($_POST["task"]))
            {
                $taskerror="Please enter a task.";
                $errors++;
            }
            if($errors==0)
            {
                $listname=mysqli_real_escape_string($conn,$_POST["listname"]);
                $task=mysqli_real_escape_string($conn,$_POST["task"]);
                $date=date('Y-m-d H:i:s');
                $sql=$conn->prepare("INSERT INTO lists(email,listname,task,checked,dates)VALUES(?,?,?,'0',?)");
                $sql->bind_param("ssss",$email,$listname,$task,$date);
                if($sql->execute())
                    header('location:dashboard.php');
                else
                    echo "Error: " . $sql . "<br>" . $conn->error;
            }
        }
        if(isset($_POST["addnote"]))   #add note
        {
            $errors=0;
            $email=$_SESSION['email'];
            $name=$_SESSION['name'];
            if(empty($_POST["title"]))
            {
                $titleerror="Please enter a title.";
                $errors++;
            }
            if(empty($_POST["note"]))
            {
                $noteerror="Please enter a note description.";
                $errors++;
            }
            $title=mysqli_real_escape_string($conn,$_POST["title"]);
            $note=mysqli_real_escape_string($conn,$_POST["note"]);
            $sql=$conn->prepare("SELECT title FROM notes WHERE title=? AND email=?");
            $sql->bind_param("ss",$title,$email);
            $sql->execute();
            $result=$sql->get_result();
            if(mysqli_fetch_assoc($result)>0)
            {
                $titleerror="Already Exists. Choose Unique Title";
                $errors++;
            }
            if($errors==0)
            {
                $label=$_POST["label"];
                $date=date('Y-m-d H:i:s');
                $errors = 0;
                if(!empty($_FILES["noteimage"]["name"]))
                {
                    $target_dir = "image/";
                    $target_file = $target_dir . basename($_FILES["noteimage"]["name"]);
                    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
                    $check = getimagesize($_FILES["noteimage"]["tmp_name"]);
                    if($check !== false)
                    {
                        move_uploaded_file($_FILES["noteimage"]["tmp_name"], $target_file);
                        $sql=$conn->prepare("INSERT INTO notes (email,label,title,note,image,dates) VALUES(?,?,?,?,?,?)");
                        $sql->bind_param("ssssss",$email,$label,$title,$note,$target_file,$date);
                        if($sql->execute())
                        header('location:dashboard.php');
                        else
                        $imageerror="Image not uploaded!"; 
                    } 
                    else 
                        $imageerror="Select a valid image format."; 
                }
                else
                {
                    $target_file="";
                    $sql=$conn->prepare("INSERT INTO notes (email,label,title,note,image,dates) VALUES(?,?,?,?,?,?)");
                    $sql->bind_param("ssssss",$email,$label,$title,$note,$target_file,$date);
                    $sql->execute();
                    header('location:dashboard.php');
                }
            }
        }
        if(isset($_POST["adduser"]))    #add collaborater
        {
            $errors=0;
            if(empty($_POST["Select"]))
                { $usererror="Select a List!";
                 $errors++;}
            if(empty($_POST["user"]))
                { $usererror="Enter an User ID!";
                  $errors++;}
            $user=trim(mysqli_real_escape_string($conn,$_POST["user"]));
            $query=$conn->prepare("SELECT * FROM accounts WHERE email=?");
            $query->bind_param("s",$user);
            $query->execute();
            $result=$query->get_result();
            if(mysqli_num_rows($result)==0)
            {
                $usererror="User Does Not Exist";
                $errors++;
            }
            if($errors==0)
            {
                $list=trim($_POST["Select"]);
                $query=$conn->prepare("INSERT INTO listaccess (fromuser,touser,listname) VALUES (?,?,?)");
                $query->bind_param("sss",$email,$user,$list);
                if($query->execute())
                header('location:dashboard.php');
                else
                echo "Error: " . $query . "<br>" . $conn->error;
            }
        } 
        if(isset($_POST["deleteuser"]))     #remove collaborater
        {
            $errors=0;
           
            if(empty($_POST["Select"]))
                { $usererror="Select a List!";
                 $errors++;}
            if(empty($_POST["user"]))
                { $usererror="Enter an User ID!";
                  $errors++;}
            $user=trim(mysqli_real_escape_string($conn,$_POST["user"]));
            if($errors==0)
            {
                $list=trim($_POST["Select"]);
                $query=$conn->prepare("DELETE FROM listaccess WHERE touser=? AND listname=? AND  fromuser=?");
                $query->bind_param("sss",$user,$list,$email);
                if($query->execute())
                header('location:dashboard.php');
                else
                echo "Error: " . $query . "<br>" . $conn->error;
            }
        }
        if(isset($_POST["deletenote"]))    #delete note
        {
            $errors=0;
            if(empty($_POST["selectnote"]))
            {
                $errors++;
                $titleerror="Select a note to delete!";
            }
            if($errors==0)
            {
                $title=trim($_POST["selectnote"]);
                $sql=$conn->prepare("DELETE FROM notes WHERE email=? AND title=?");
                $sql->bind_param("ss",$email,$title);
                $sql->execute();
                header('location:dashboard.php');
            }
        }
        if(isset($_POST["viewlabel"]))       #sort by label
        {
            $_SESSION['sort']="label";
        }
        if(isset($_POST["sortdate"]))
            $_SESSION['sort']="dates";
   		$sql=$conn->prepare("SELECT listname,MAX(dates) FROM lists WHERE email=? GROUP BY listname ORDER BY MAX(dates) desc");
   		$sql->bind_param("s",$email);
   		$sql->execute();
   		$result=$sql->get_result();
   		echo "<div class=\"x\"><div class=\"y\"> <h3>Your Lists :</h3>";
   		$i=1;
        echo "<form action=\"";echo htmlspecialchars($_SERVER["PHP_SELF"]);echo "\" method=\"post\">";
   		echo "<table><tr><th>S.no	</th> <th> List Name </th> <th>Last Modified</th></tr>";
   		while ($row=mysqli_fetch_assoc($result)) 
   		{
   			echo "<tr> <td><input type=\"radio\" name =\"Select\" value=\" ".$row['listname']."\" > ".$i."</td><td>" .$row['listname']." </td><td>".$row['MAX(dates)']."</td></tr>";
   			
   			$i++;
   		}
        echo "</table> <br><input id=\"button\" type =\"submit\" name=\"view\" value = \"View\"/> <input id=\"button\" type =\"submit\" name=\"delete\" value = \"Delete\"/><br> <input type=\"text\" name=\"user\" placeholder=\"User Id\"> <input id=\"button\" type =\"submit\" name=\"adduser\" value = \"Add User\"/> <input id=\"button\" type =\"submit\" name=\"deleteuser\" value = \"Delete User\"/> <span>  ".$usererror." </span> <br> <br> <input type=\"text\" name=\"listname\" placeholder=\"ListName\"> <span>  ".$nameerror." </span> <br><br><input type=\"text\" name=\"task\" placeholder=\"Task\"><span>  ".$taskerror."</span><br><br><input type=\"submit\" name=\"addtask\" value=\"Add Task\"></form>";
        echo "<h3> Lists you have been given access to: </h3>";
        echo "<form action=\"";echo htmlspecialchars($_SERVER["PHP_SELF"]);echo "\" method=\"post\">";
        echo "<table><tr><th>S.no   </th> <th> List Name </th> <th> Access granted by</th></tr>";
        $sql=$conn->prepare("SELECT * FROM listaccess WHERE touser=? ORDER BY listname");
        $sql->bind_param("s",$email);
        $sql->execute();
        $result=$sql->get_result();
        $i=1;
        while($row=mysqli_fetch_assoc($result))
        {
            echo "<tr><td><input type=\"radio\" name =\"Selecttask\" value=\" ".$row['id']."\" > ".$i."</td> <td>" .$row['listname']."</td><td>".$row['fromuser']."</td></tr>";
            $i++;
        }
        echo "</table><br><input id=\"button\" type =\"submit\" name=\"viewtask\" value = \"View\"/><br></form><br>";
   		echo "<h3>Your Notes :</h3>";
        echo "<form action=\"";echo htmlspecialchars($_SERVER["PHP_SELF"]);echo "\" method=\"post\" enctype=\"multipart/form-data\">";
        echo "<table><tr><th>S.no   </th><th>Label </th> <th> Note Title </th> <th> Date Modified</th></tr>";
        
        if(($_SESSION['sort'])=="dates")
        {
            $sql=$conn->prepare("SELECT title,label,dates FROM notes WHERE email=? ORDER BY dates DESC");
            $sql->bind_param("s",$email);
        }
        else
        {
            $labelsort=$_POST["label"];
            $sql=$conn->prepare("SELECT title,label,dates FROM notes WHERE email=? AND label=? ORDER BY dates");
            $sql->bind_param("ss",$email,$labelsort);
        }
        if($sql->execute())
            echo "";
        else
            echo "Error: " . $sql . "<br>" . $conn->error; 
        $result=$sql->get_result();
        $k=1;
        while($row=mysqli_fetch_assoc($result))
        {
            echo "<tr><td><input type=\"radio\" name=\"selectnote\" value=\"".$row['title']."\">".$k."</td><td>".$row['label']."</td><td>".$row['title']."</td><td>".$row['dates']."</td></tr>";
            $k++;
        }
        echo"</table><br><input type=\"submit\" name=\"viewnote\" value=\"view\"> <input type=\"submit\" name=\"deletenote\" value=\"Delete\"> <input type=\"submit\" value=\"Sort by Date\" name=\"sortdate\">   <br> <br><select name=\"label\"><option value=\"Personal\">Personal</option><option value=\"Assignment\">Assignment</option><option value=\"Classnotes\">Classnotes</option></select> <input type=\"submit\" value=\"View by Label\" name=\"viewlabel\"><br><br> <input type=\"text\" name=\"title\" placeholder=\"Title\"><span>  ".$titleerror." </span> <br><br><input type=\"text\" name=\"note\" placeholder=\"Note..\"><span>  ".$noteerror."</span><br><br><input type=\"file\" name=\"noteimage\" accept=\"image/*\" value=\"Upload Image\" ><span> ".$imageerror."</span><br><br><input type=\"submit\" name=\"addnote\" value=\"Add Note\"></form></div></div>";
        echo"<!DOCTYPE html>
         <html>
         <head>
            <title>Dashboard</title>
            <link rel=\"stylesheet\" href=\"dashboard.css\" type=\"text/css\">
         </head>
         <body>
            <br><br>
         </body>
         </html>";
   	}
    echo"";
?>