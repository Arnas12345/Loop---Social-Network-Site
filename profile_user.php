

<html>
    <head>
        <title>Loop : Home</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
        <link rel="stylesheet" type="text/css" href="css/profile_user.css?v=<?php echo time(); ?>">
    </head>
    <body>
        <?php include("headerTemplate.html"); ?>
        <h1 class="page-header">My Profile</h1>
        <?php 
            session_start();
            print "<h2> userID: {$_SESSION['user']} </h2>";
        ?>
        <hr>
        <div class = "profile-container" >
            <div class = "profileImage" >
                <?php

                    include ("serverConfig.php");
                    $conn = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
                    if ($conn -> connect_error) {
                        die("Connection failed:" .$conn -> connect_error);
                    }

                    $sql = "select * from users where userID =\"{$_SESSION['user']}%\";";
                    $result = $conn -> query($sql);
                    $row = $result->fetch_assoc();

                    $profileImage = null;

                    if (isset($row['profileImage'])) $profileImage = $row['profileImage'];

                    if($profileImage === null) {
                        print '<img src = "images/blank-profile-picture.png" alt="profile image" height="25%" width="25%" style="min-width:180px; min-height:180px; border-radius:50%;" >';
                    }
                    else {
                        print "<img src = 'profileImages/{$profileImage}' alt='profile image' height='25%' width='25%' style='min-width:180px; min-height:180px; border-radius:50%; object-fit: cover; overflow:hidden;' >";
                    }

                ?>
            </div>
            <div class="editProfile">
                <form action="editProfile.php">
                    <input type="submit" value="Edit Profile" />
                </form>
            </div>
        </div>
        <div class = "description-container">
            <div class = "description-heading">
                <H1 style = "text-align: center;">Description</H1>
            </div>
            <div class = "bio-description">
                <h3>Bio:</h3>
                <?php

                    include ("serverConfig.php");
                    $conn = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
                    if ($conn -> connect_error) {
                        die("Connection failed:" .$conn -> connect_error);
                    }

                    $sql = "select * from users where userID =\"{$_SESSION['user']}%\";";
                    $result = $conn -> query($sql);
                    if($row = $result->fetch_assoc()) {
                        print "<p class='userDetails'>{$row['description']}</p>";
                        setcookie("description",$row['description'],time()+3600);
                        $conn->close();
                    } 
                    else {
                        print "<p>No Bio found.</p>";
                    }
                ?>

            </div>
            <div class = "skills-description">
                <h3>Skills:</h3>
                <?php
                    fetchProfileElement("skills");
                ?>
            </div>
            
            <div class = "Qualifications-description">
                <h3>Employment History:</h3>
                <?php
                    fetchProfileElement("employment-history");
                ?>
            </div>

            <div class = "Certs-description">
                <h3>Qualifications:</h3>
                <?php
                    fetchProfileElement("qualifications");                    
                ?>
            </div>

            <div class = "Qualifications-description">
                <h3>Current Employer:</h3>
                <?php
                    fetchProfileElement("current-employer");
                ?>
            </div>
        </div>
    </body>
</html>

<?php 

    function fetchProfileElement($elementToFetch) {
        include ("serverConfig.php");
        $conn = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
        if ($conn -> connect_error) {
            die("Connection failed:" .$conn -> connect_error);
        }
        $userID = $_SESSION['user'];

        switch($elementToFetch){
            case ("current-employer") : 
                $sql = "SELECT a.companyName
                    FROM companies a
                    INNER JOIN users b
                    ON a.companyID = b.companyID
                    WHERE b.userID = {$userID};";
                $result = $conn -> query($sql);
                if($row = $result->fetch_assoc()) {
                    print "<p class='userDetails'>{$row['companyName']}</p>";
                    setcookie("currentEmployer",$row['companyName'],time()+3600);
                    $conn->close();
                } else {
                    setcookie("currentEmployer", "", time() - 3600);
                    print "<p>No Current Employer.</p>";
                }
                break;

            case ("qualifications") : 
                $sql = "SELECT a.academicTitle, a.academicDescription, a.academicLevel, b.completionDate
                    FROM accademicdegrees a
                    INNER JOIN userqualificaion b
                    ON a.academicID = b.academicID
                    WHERE b.userID = {$userID};";
                $result = $conn -> query($sql);
                if(mysqli_num_rows($result) != 0) {
                    while($resultRow = $result->fetch_assoc()) {
                        print "<p>Graduated {$resultRow['academicDescription']}, {$resultRow['academicLevel']} at {$resultRow['academicTitle']} on {$resultRow['completionDate']}</p>";
                    }
                } else {
                    print "<p>No Previous Job History Found.</p>";
                }
                break;

            case ("employment-history") :
                $sql = "SELECT a.companyName, b.FromDate, b.ToDate
                    FROM companies a
                    INNER JOIN jobhistory b
                    ON a.companyID = b.companyID
                    WHERE b.userID = {$userID};";
                $result = $conn -> query($sql);
                if(mysqli_num_rows($result) != 0) {
                    while($resultRow = $result->fetch_assoc()) {
                        print "<p>{$resultRow['companyName']}, {$resultRow['FromDate']} - {$resultRow['ToDate']}</p>";
                    }
                } else {
                    print "<p>No Previous Job History Found.</p>";
                }
                break;
            
            case ("skills") :
                $sql = "SELECT a.skillTitle
                    FROM skills a
                    INNER JOIN userskills b
                    ON a.skillID = b.skillID
                    WHERE b.userID = {$userID};";
                $result = $conn -> query($sql);
                if(mysqli_num_rows($result) != 0) {
                    while($resultRow = $result->fetch_assoc()) {
                        print "<p>{$resultRow['skillTitle']}</p>";
                    }
                } else {
                    print "<p>No Skills Found.</p>";
                }
                break;

            default : break;
        }
        
    }

?>