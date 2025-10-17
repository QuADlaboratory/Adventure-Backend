<?php
// connect to the SQL server
//__DIR__ . '/../../src/ADVENTURE/ - get rid of this when testing not on server
include(__DIR__ . '/../../src/ADVENTURE/connectToDB.php');

// get what the sender's ID is
$headers = apache_request_headers();
$auth_header = $headers['Authorization'];
$ID = substr($auth_header, 0, 8);

// Only enters if it is a http post request
if (!$_SERVER["REQUEST_METHOD"] == "POST"){
    echo "\nInvalid request method";
    http_response_code(405);  

//Authorization so that only participants can send things
} elseif(!authentication($conn, $ID)){
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
        // get the body of the request
        $body = json_decode(file_get_contents('php://input'), true);

        // get game type then if statement for respective thing
        $game_type = substr($auth_header, 9);

        if($game_type == "SART"){
            sartQuery($conn, $body, $ID);
        } elseif($game_type == "Stew"){
            stewInsertQuery($conn, $body, $ID);
        } elseif($game_type == "FISH"){
            fishInsertQueryInsertQuery($conn, $body, $ID);
        }else{
            echo '\nInvalid game type';
            http_response_code(405);
        }

        //after data has been saved to db, then the connection btwn server and db gets stopped
        mysqli_close($conn);

    // For logging in 
    } else {
        // login request
        if (file_get_contents('php://input') == "Loggingin" ){
            echo 'Login';
            http_response_code(204);
        } else{
            echo 'Invalid plain text request';
            http_response_code(406);
        }
    }
}

function authentication ($conn, $ID){
    //Authorization so that only participants can send things
    // get what the sender's ID is
    
    // first part is the name - then game

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

function sartQuery($conn, $body, $ID){
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
}
function stewInsertQuery($conn, $body, $ID){
    //list of the columns
    $list = ["start_time", "start_time_ms", "level", "trial_num" , "Ingredient_Name", "Ingredient_Image", "Ingredient_Size", "Is_Target", "Ingredient_Start",
        "Mask_Start", "touch_start","touch_end", "x1_touch", "y1_touch", "x2_touch", "y2_touch","reaction_time", "touch_raw", "swipe_dir", "screen_height", "screen_width"];

    // Handle the JSON data here	
    // loops for every trial
    foreach ($body as $elem){
        // getting the values into vars
        $task_start = $elem["Start_Time"]; 
        $task_start_ms = $elem['Start_Time_MS'];
        $trial_num = $elem["Trial_Num"];
        $lvl = $elem["Level"];
        $ingr = $elem["Ingredient_Name"];
        $ingr_image = $elem["Ingredient_Image"];
        $ingr_size = $elem["Ingredient_Size"];
        $is_target = $elem["Is_Target"];
        $ingr_start = $elem["Ingredient_Start"];
        $mask_start = $elem["Lid_Start"];
        $start_time_resp= $elem["Touch_Start"];
        $end_time_resp = $elem["Touch_End"];
        $x1_resp = $elem["Touch_X1"];
        $y1_resp = $elem["Touch_Y1"];
        $timestamps = $elem["Touch_Raw"];
        $x2_resp =$elem["Touch_X2"];
        $y2_resp = $elem["Touch_Y2"];
        $resp_time = $elem["Resp_Time"];
        $resp = $elem["Swipe_Dir"];
        $screen_height = $elem["Screen_Height"];
        $screen_width = $elem["Screen_Width"];

        // list of the data
        $listData = [$task_start, $task_start_ms, $lvl, $trial_num, $ingr, $ingr_image, $ingr_size, $is_target, $ingr_start, $mask_start, 
            $start_time_resp, $end_time_resp, $x1_resp, $y1_resp, $x2_resp, $y2_resp, $resp_time, $timestamps, $resp, $screen_height, $screen_width];
        
        // this section is to put all the not null values into the respective col
        // dbeaver was weird so these are defult to null and cannot send a null
        $col = "";
        $colData = "";
        // add only the none null since in SQL its defult null
        // later can automate this process so no need to hard code the query 
        for ($i=0; $i < count($list); $i++){
            if ($listData[$i] != null){
                $col = $col . ', '.$list[$i];
                $colData = $colData .", '".$listData[$i]."'" ;
            }
        }

        // the query is inserting a trial into the database
        $sql = "INSERT INTO stewtestingdata (part_id ".$col.") VALUES ('".$ID."' ".$colData.")";
        echo $sql;
                
        // if query is sucessful, then its added and a msg is sent saying it is
        if (mysqli_query($conn, $sql)){
            //echo "sent";
            http_response_code(201);
        }else{ // shows the error if not working
            echo "query error". mysqli_error($conn);
        }
    }
}
function fishInsertQuery($conn, $body, $ID){
    //list of the columns
    $list = ["start_time", "start_time_ms", "level", "trial",  "stimulus_start", "is_there_target", "target_image",  "target_size", "target_coordinate", 
            "target_x_position", "target_y_position", "target_x_jitter", "target_y_jitter", "touch_start", "touch_end", "touch_x1", "touch_y1","touch_x2", 
            "touch_y2", "touch_raw", "reaction_time", "response", "nontarget_location","Screen_Height", "Screen_Width","is_screen_focused"];

    // Handle the JSON data here	
    // loops for every trial
    foreach ($body as $elem){
        // getting the values into vars
        $task_start = $elem["Start_Time"]; 
        $task_start_ms = $elem['Start_Time_MS'];
        $trial_num = $elem["Trial_Num"];
        $lvl = $elem["Level"];

        $ingr = $elem["Ingredient_Name"];
        $ingr_image = $elem["Ingredient_Image"];
        $ingr_size = $elem["Ingredient_Size"];
        $is_target = $elem["Is_Target"];
        $ingr_start = $elem["Ingredient_Start"];
        $mask_start = $elem["Lid_Start"];

        $start_time_resp= $elem["Touch_Start"];
        $end_time_resp = $elem["Touch_End"];
        $x1_resp = $elem["Touch_X1"];
        $y1_resp = $elem["Touch_Y1"];
        $timestamps = $elem["Touch_Raw"];
        $x2_resp =$elem["Touch_X2"];
        $y2_resp = $elem["Touch_Y2"];
        $resp_time = $elem["Resp_Time"];
        $resp = $elem["Swipe_Dir"];

        $screen_height = $elem["Screen_Height"];
        $screen_width = $elem["Screen_Width"];

        // list of the var that could be null
        $listData = [$task_start, $task_start_ms, $lvl, $trial_num, $ingr, $ingr_image, $ingr_size, $is_target, $ingr_start, $mask_start, 
            $start_time_resp, $end_time_resp, $x1_resp, $y1_resp, $x2_resp, $y2_resp, $resp_time, $timestamps, $resp, $screen_height, $screen_width];
        //var_dump($listData);
        // this section is to put all the not null values into the respective col
        // dbeaver was weird so these are defult to null and cannot send a null
        $col = "";
        $colData = "";
        // add only the none null since in SQL its defult null
        // later can automate this process so no need to hard code the query 
        for ($i=0; $i < count($list); $i++){
            if ($elem[$list[$i]] != null){
                $col = $col . ', '.$list[$i];
                $colData = $colData .", '".$elem[$list[$i]]."'" ;
            }
        }

        // the query is inserting a trial into the database
        $sql = "INSERT INTO fish_testing_data (participate_id ".$col.") VALUES ('".$ID."' ".$colData.")";
        echo $sql;
                
        // if query is sucessful, then its added and a msg is sent saying it is
        if (mysqli_query($conn, $sql)){
            //echo "sent";
            http_response_code(201);
        }else{ // shows the error if not working
            echo "query error". mysqli_error($conn);
        }
    }
}


?>