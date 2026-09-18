<?php
function connection(){
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "webdev";
    $conn = mysqli_connect($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die();
    }
    return $conn;
}

function getUser($id){
    $connection = connection();
    $sql ="SELECT * FROM internship_users WHERE id = ".$id;
    $result = mysqli_query($connection, $sql);
    if($result && mysqli_num_rows($result) > 0){
        return mysqli_fetch_assoc($result);
    }
    return false;
}
function updateUser($id, $email_address, $firstname, $middlename, $surname,  $company, $start_date, $hours_required){
    $connection = connection();
    $sql = "UPDATE internship_users SET email_address = '".$email_address."',firstname = '".$firstname."', middlename = '".$middlename."',surname = '".$surname."',company = '".$company."',start_date = '".$start_date."',hours_required = '".$hours_required."' where id = ".$id; 
    if(mysqli_query($connection, $sql)){
        return "Successfully updated.";
    }else{
        return false;
    }
}

?>