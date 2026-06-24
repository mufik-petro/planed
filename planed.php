<?php



include('config.php');

$input = json_decode(file_get_contents("php://input"), true);

if ($input["token"] == NULL) {
    $result["state"] = false;
    $result["error"]["message"][] = "'token' is missing";
}
if ($input["userId"] == NULL) {
    $result["state"] = false;
    $result["error"]["message"][] = "'userId' is missing";
}
if ($input["trigger"] == NULL && $input["message"] == NULL) {
    $result["state"] = false;
    $result["error"]["message"][] = "'trigger' or 'message' is missing";
}
if ($input["timezone"] != NULL) {
    $setTimeZone = date_default_timezone_set($input["timezone"]);
    if ($setTimeZone === false) {
        $result["state"] = false;
        $result["error"]["message"][] = "'timezone' is invalid";
    }
}
if ($input["time"] == NULL) {
    $result["state"] = false;
    $result["error"]["message"][] = "'time' is missing";
} else {
    $time = $input["time"];
    settype($time, "int"); settype($time, "string"); settype($input["time"], "string");
    if ($time == $input["time"]) { // Якщо в тілі запиту часова мітка
        if ($time <= time()) {
            $result["state"] = false;
            $result["error"]["message"][] = "'time' in the past1";
        }
    } else { // Якщо в тілі запиту рядкове представлення часу
        if ($input["autotime"] === true && stripos($input["time"], "+") !== false) {
            $appendTime = "+".explode("+", $input["time"], 2)[1];
            $count = 0;
            for ($time = strtotime($input["time"]); time() > $time; $time = strtotime($appendTime, $time)) {
                $count++;
                if ($count > 100) {
                    $result["state"] = false;
                    $result["error"]["message"][] = "failed use appendTime";
                    break;
                }
            }
        } else if ($input["autotime"] === true && $input["appendTime"] != NULL) {
            for ($time = strtotime($input["time"]); time() > $time; $time = strtotime($input["appendTime"], $time)) {
                $count++;
                if ($count > 100) {
                    $result["state"] = false;
                    $result["error"]["message"][] = "failed use appendTime";
                    break;
                }
            }
        } else {
            $time = strtotime($input["time"]);
            if ($time <= time()) {
                $result["state"] = false;
                $result["error"]["message"][] = "'time' in the past2";
            }
        }
    }
}
if ($result["state"] === false) {
    echo json_encode($result);
    exit;
}

$sql = "INSERT INTO `".$table."` (`time`, `userId`, `trigger`, `message`, `token`) VALUES ('".$time."', '".$input["userId"]."', '".$input["trigger"]."', '".mysqli_real_escape_string($sqlConnect, $input["message"])."', '".$input["token"]."')";
$insert = mysqli_query($sqlConnect, $sql);
if ($insert == false) {
    $result["state"] = false;
    $result["error"]["message"][] = "failed insert to sql";
    $result["error"]["sql"] = mysqli_error_list($sqlConnect);
} else {
    $result["state"] = true;
    $result["insert"]["id"] = mysqli_insert_id($sqlConnect);
    $result["datetime"] = date("d.m.Y H:i:s", $time);
}

echo json_encode($result);






