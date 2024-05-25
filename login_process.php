<?php
if ($_SERVER["REQUEST_METHOD"] == "POST"){
    include '_dbconnect.php';
    
    
    $username = $_POST['username'];
    $password = $_POST['password'];
    

    $sql2 = " SELECT * FROM user WHERE user_mail = '$username' and password = '$password'";
    $result1 = mysqli_query($con, $sql2);
    $num1 = mysqli_num_rows($result1);

    if($num1==1){
        session_start();
        $_SESSION['mail']=$username;
        
        ?>
            <script>location.assign('index.php')</script>
        <?php
    }
    else{
        ?>
            <script>location.assign('login.html')</script>
        <?php
    }
}
?>