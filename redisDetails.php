<?php

$redisStatus = shell_exec("redis-cli info");
$cmdPerSec = 0; //instantaneous_ops_per_sec
$memUsed = 0; //used_memory_human
foreach (explode("\n",$redisStatus) as $item) {
    if(strpos($item,"instantaneous_ops_per_sec")===0){
        $cmdPerSec=str_replace("instantaneous_ops_per_sec:","",$item);
    }else if(strpos($item,"used_memory_human")===0){
        $memUsed=str_replace("used_memory_human:","",$item);
    }
}
echo json_encode(["ops"=>"ops/s:".$cmdPerSec]);//." mem:".$memUsed;
