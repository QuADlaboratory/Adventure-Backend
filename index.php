<?php
// connect to the SQL server
//__DIR__ . '/../../src/ADVENTURE/ - get rid of this when testing not on server
include(__DIR__ . '/../../src/ADVENTURE/connectToDB.php');

// Only enters if it is a http post request
if (!$_SERVER["REQUEST_METHOD"] == "POST"){
    echo "\nInvalid request method";
    http_response_code(405);  

//Authorization so that only participants can send things
} elseif(!authentication($conn)){
    http_response_code(401);
    echo "\nInvalid User ID";

// only handle JSON and plain text
} elseif (!$_SERVER['CONTENT_TYPE'] == 'application/json; charset=UTF-8' &&  !$_SERVER['CONTENT_TYPE'] == 'text/plain'){
    echo "\nInvalid content type";
    http_response_code(415);

// Body cannot be empty
} elseif (file_get_contents('php://input') == null){
    echo file_get_contents('php://input');
    echo "\nInvalid data";
    http_response_code(400);

} else {
    // The request is to put in sql data
    if ($_SERVER['CONTENT_TYPE'] == 'application/json; charset=UTF-8'){
        $body = json_decode(file_get_contents('php://input'), true);
        $list = ["touch_start", "touch_end", "x1_touch", "y1_touch", "touch_raw","x2_touch", "y2_touch"];
        // Handle the JSON data here	
        // loops for every trial
        foreach ($body as $elem){
            // getting the values into vars
            $task_start = $elem["task_start"]; 
            $trial_num = $elem["trial_num"];
            $digit = $elem["digit"];
            $fontsize = $elem["fontsize"];
            $digit_start = $elem["digit_start"];
            $mask_start = $elem["mask_start"];
            $start_time_resp= $elem["start_time_resp"];
            $end_time_resp = $elem["end_time_resp"];
            $x1_resp = $elem["x1_resp"];
            $y1_resp = $elem["y1_resp"];
            $timestamps = $elem["timestamps"];
            $x2_resp =$elem["x2_resp"];
            $y2_resp = $elem["y2_resp"];
            $resp_time = $elem["resp_time"];
            $resp = $elem["resp"];
            $screen_height = $elem["screen_height"];
            $screen_width = $elem["screen_width"];

            // list of the var that could be null
            $listData = [$start_time_resp, $end_time_resp, $x1_resp, $y1_resp, $timestamps, $x2_resp, $y2_resp];

            // this section is to put all the not null values into the respective col
            // dbeaver was weird so these are defult to null and cannot send a null
            $col = "";
            $colData = "";
            for ($i=0; $i < count($list); $i++){
                if ($listData[$i] != null){
                    $col = $col . ', '.$list[$i];
                    $colData = $colData .", '".$listData[$i]."'" ;
                }
            }

            // the query is inserting a trial into the database
            $sql = "INSERT INTO sartdata 
                    (part_id, start_time, start_time_ms, trial_num, digit, fontsize, digit_start, mask_start".$col.", touch_dur, swipe_dir, screen_height, screen_width) 
                    VALUES ('".$ID."', '".$task_start."','" .$elem['task_start_ms']."' ,'".$trial_num."' , '".$digit."', '".$fontsize."', '".$digit_start."', '".$mask_start."'".$colData.",'".$resp_time."', '".$resp."', '".$screen_height."', '".$screen_width."')";
                    
            // if query is sucessful, then its added and a msg is sent saying it is
            if (mysqli_query($conn, $sql)){
                //echo "sent";
                http_response_code(201);
            }else{ // shows the error if not working
                echo "query error". mysqli_error($conn);
            }
        }
        //after data has been saved to db, then the connection btwn server and db gets stopped
        mysqli_close($conn);

    // text is     
    } else {
        // login request
        if (file_get_contents('php://input') == "Loggingin" ){
            echo 'Login';
            http_response_code(204);
        } else{
            echo 'Invalid plain text request';
            http_response_code(400);
        }
    }

}

function authentication ($conn){
    //Authorization so that only participants can send things
    // get what the sender's ID is
    $headers = apache_request_headers();
    $ID = $headers['Authorization'];

    // The query is checking how many rows have in the part_id of the participantid table are $ID
    $query = "SELECT * FROM participantid WHERE part_id = '$ID'";
    $result = mysqli_query($conn, $query); //error 
    $user = mysqli_fetch_assoc($result);

    // if it is 0 then the id is not in the database, if 1 then it is a valid ID
    if(mysqli_num_rows($result) > 0){
        return true;
    }else{
        return false;
    }
}

?>