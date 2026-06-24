<?php

include('config.php');

$sql = "SELECT * FROM `".$table."` WHERE `time` <= '".time()."' ORDER BY `time` ASC LIMIT 50";

$getList = mysqli_fetch_all(mysqli_query($sqlConnect, $sql), MYSQLI_ASSOC);
$log["list"] = $getList;

if ($getList != NULL && is_array($getList)) {
    foreach ($getList as $key => $oneList) {
        if ($oneList["trigger"] != NULL) {
            $fire["name"] = $oneList["trigger"];
            $action = json_decode(send_bearer("https://api.smartsender.com/v1/contacts/".$oneList["userId"]."/fire", $oneList["token"], "POST", $fire), true);
            $log["data"][$key]["fire"] = $fire;
            $log["data"][$key]["result"] = $action;
            if ($action["state"] == true) {
                mysqli_query($sqlConnect, "DELETE FROM `".$table."` WHERE `id` = '".$oneList["id"]."'");
            }
        } else if ($oneList["message"] != NULL) {
            $message = [
                "type" => "text",
                "watermark" => 1,
                "content" => $oneList["message"]
            ];
            $action = json_decode(send_bearer("https://api.smartsender.com/v1/contacts/".$oneList["userId"]."/send", $oneList["token"], "POST", $message), true);
            $log["data"][$key]["fire"] = $fire;
            $log["data"][$key]["result"] = $action;
            if ($action["state"] == true) {
                mysqli_query($sqlConnect, "DELETE FROM `".$table."` WHERE `id` = '".$oneList["id"]."'");
            }
        } else {
            mysqli_query($sqlConnect, "DELETE FROM `".$table."` WHERE `id` = '".$oneList["id"]."'");
        }
    }
}
