<?php
    session_start();

    if (!(isset($_SESSION["loggedin"])) || $_SESSION["loggedin"] == false) {
        header( "Location: login.html" );
    } 

?>

<html>
    <head>
        <title>Loop : Home</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
        <link rel="stylesheet" type="text/css" href="css/home.css?v=<?php echo time(); ?>">
    </head>
    <script type="text/javascript">
        function loopJob(vacancyID, companyID) {
            window.location.href= 'loopJob.php?vacancyID=' + vacancyID +'&companyID=' + companyID;
        }

        function unLoopJob(vacancyID, companyID) {
            window.location.href= 'unLoopJob.php?vacancyID=' + vacancyID +'&companyID=' + companyID;
                if (confirm("Are you sure you want to delete this looped job?") == true) {
                    window.location.href= 'unLoopJob.php?vacancyID=' + vacancyID +'&companyID=' + companyID;
                };
        }

        function showSkills(modalNumber) {
            var modal = document.getElementById("myModal" + modalNumber);
            modal.style.display = "block";
            
            var span = document.getElementsByClassName("close" + modalNumber)[0];
            span.onclick = function() {
                modal.style.display = "none";
            }

            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }
        }

            var modal = document.getElementById("friendmodal");
     
            
            var span = document.getElementsByClassName("close")[0];

            btn.onclick = function() {
            modal.style.display = "block";
            }

    
    </script>
    <body>
        <?php include ("headerTemplate.html");?>
        <h1 class="page-header">Job Feed</h1>
       

    </div>
        <hr>
        <div class="page-box">
            <?php
                include ("serverConfig.php");
                $conn = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
                if ($conn -> connect_error) {
                    die("Connection failed:" .$conn -> connect_error);
                }
                print '
                <form method="post" action="home.php?sortBySkills=true">
                <h3>Select Skills</h3>
                <div class="custom-select">
                <select name="skill">';
                $skillsSql = "select * from skills;";
                $skillsResult = $conn -> query($skillsSql);
                print "<option value=''>Select A Skill</option>";
                while($skillsRow = $skillsResult->fetch_assoc())
                {   
                    $getUsersSkillsSql = "select * from userskills WHERE userID={$userID}";
                    $getUsersSkillsResult = $conn -> query($getUsersSkillsSql);
                    print "<option value='{$skillsRow['skillTitle']}'>{$skillsRow['skillTitle']}</option>";
                }
                print '</select><input type="submit" name="sortBySkills" value="Sort"></div></form><br>';
                $sql = "";
                if (isset($_GET['sortBySkills'])) {
                    if(!empty($_POST['skill'])) {
                        $sql = "select a.vacancyTitle, a.vacancyDescription, a.requiredExperience, a.role, a.timeAdded, b.companyName, a.vacancyID, b.companyID, d.skillTitle, d.skillDescription
                        from vacancies a
                        INNER JOIN companies b
                        ON a.companyID = b.companyID
                        INNER JOIN skillsforvacancy c
                        ON a.vacancyID = c.vacancyID
                        INNER JOIN skills d
                        ON c.skillID = d.skillID
                        WHERE d.skillTitle = '{$_POST['skill']}'
                        ORDER BY timeAdded DESC;";
                    } else {
                        $sql = "select a.vacancyTitle, a.vacancyDescription, a.requiredExperience, a.role, a.timeAdded, b.companyName, a.vacancyID, b.companyID
                        from vacancies a
                        INNER JOIN companies b
                        ON a.companyID = b.companyID
                        ORDER BY timeAdded DESC;";
                    }
                } else {
                    $sql = "select a.vacancyTitle, a.vacancyDescription, a.requiredExperience, a.role, a.timeAdded, b.companyName, a.vacancyID, b.companyID
                    from vacancies a
                    INNER JOIN companies b
                    ON a.companyID = b.companyID
                    ORDER BY timeAdded DESC;";
                }
                $result = $conn -> query($sql);
                
                if(mysqli_num_rows($result) != 0) {
                    $counter = 0;
                    while($row = $result->fetch_assoc())
                    {   
                        $skillsNeeded = array();
                        $skillsSql = "select a.skillTitle, a.skillDescription
                        from skills a
                        INNER JOIN skillsforvacancy b
                        ON a.skillID = b.skillID
                        INNER JOIN vacancies c
                        ON b.vacancyID = c.vacancyID
                        WHERE c.vacancyID= {$row['vacancyID']}";
                        $skillsResult = $conn -> query($skillsSql);
                        while($skillsRow = $skillsResult -> fetch_assoc()) {
                            $skill = array('skillTitle' => $skillsRow['skillTitle'], 'skillDesc' => $skillsRow['skillDescription']);
                            $skillsNeeded[] = $skill;
                        }
                        $counter++;

                        print "<div class='container vacancy'>
                                    <div class='row'>
                                        <div class='col-4' >
                                            <img class='img-fluid' width='200' height='200' src='images/job-icon.jpg'  alt='logo here'></img>
                                        </div>
                                        <div class='col-8' >
                                            <a class='head vacancyDetails text-lg-center' href='company.php?companyID={$row['companyID']}'><b><p>{$row['companyName']}</p></b></a>
                                            <p class='vacancyDetails text-left'><b>Title: </b>{$row['vacancyTitle']}</p>
                                            <p class='vacancyDetails text-left'><b>Description: </b>{$row['vacancyDescription']}</p>
                                            <p class='vacancyDetails text-left'><b>Role: </b>{$row['role']}</p>
                                            <p class='vacancyDetails text-left'><b>Req. Experience: </b>{$row['requiredExperience']}</p>
                                            <button class='showskills' onClick='showSkills({$counter})'>Show Skills</button>";
                                        
                                        $loopedJobSQL = "select * from looped where userID = {$_SESSION['user']} AND companyID = {$row['companyID']} AND vacancyID = {$row['vacancyID']};";
                                        $loopedJobResult = $conn -> query($loopedJobSQL);
                                        $loopedJobRow = $loopedJobResult->fetch_assoc();
                                        if($loopedJobRow) {
                                            print "<img class='img-fluid' src='images/cancel_loop.png' alt='logo here' style='height: 12%;' onClick='unLoopJob(${row['vacancyID']}, ${row['companyID']})'></img>";
                                        } else {
                                            print "<img class='img-fluid' src='images/Like_Loop_small.png' alt='logo here'  onClick='loopJob(${row['vacancyID']}, ${row['companyID']})'></img>";
                                        }
                                        print "<div id='myModal{$counter}' class='modal'>
                                                <!-- Modal content -->
                                                <div class='modal-content'>
                                                    <span class='close{$counter} close'>&times;</span>
                                                    <table class='skillsTable'>
                                                    <thead>
                                                        <tr>
                                                            <th>Skills Required</th>
                                                            <th>Skills Description</th>
                                                        </tr>
                                                    </thead>";
                                        if(!empty($skillsNeeded)) {
                                            foreach ($skillsNeeded as $row) 
                                            {   
                                                echo '<tr>';
                                                echo '<td>' . $row['skillTitle'] . '</td>';
                                                echo '<td>' . $row['skillDesc'] . '</td>';
                                                echo '</tr>';
                                            }
                                        } else echo "<tr><td colspan='3'>No Specific Skills Required</td></tr>";
                                        print "</table></div></div>";
                                        
                                        
                                        
                                        print "</div></div></div>";
                                        
                    }
                } else {
                    print "<h1>No Vacanies Found.</h1>";
                }
                    $conn->close();
            ?>
        </div>
    </body>
</html>

<?php
    if (isset($_GET['sortBySkills'])) {
        echo $_POST['skill'];
    }
?>