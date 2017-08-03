<?php

// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}
// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET");
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

}

ini_set('display_errors',false);
ini_set('display_startup_errors',false);
error_reporting(-1);

$CACHE_FILE = "/tmp/system_stats_cache.json";

function getDb() {
  try {
       $dbh = new PDO("pgsql:dbname=sharengo;host=localhost;port=5433", 'cs', 'gmjk51pa');
  } catch (PDOException $e) {
    echo "-1:Database error : $e";
  }
  return $dbh;
}

function getTableSize($dbh,$table,$where=NULL) {
  $sql ="SELECT count(*) FROM $table";
  if ($where) {
    $sql .= " WHERE $where ";
  }
  $q = $dbh->query($sql);

  return $q->fetchColumn();
}

function getCarsCount($dbh, $id_fleet=1) {
  $result = array();
  $sql = "SELECT status, count(*) as num FROM cars WHERE fleet_id= $id_fleet GROUP BY status";
  foreach($dbh->query($sql) as $row) {
    $result[$row['status']]=$row['num'];
  }
  return $result;
}


function getReservationsCount($dbh) {
  $result = array();
  $sql = "SELECT  reason, count(*) as num FROM reservations_archive  WHERE length>0 AND beginning_ts > (now() - '24 hours'::INTERVAL) GROUP BY reason";
  foreach($dbh->query($sql) as $row) {
    $result[$row['reason']]=$row['num'];
  }
  return $result;
}


function getRealTripsDailyStats($dbh) {
  return getTripsDailyStats($dbh,' payable=true and timestamp_end is not null AND ');
}

function getServiceTripsDailyStats($dbh) {
  return getTripsDailyStats($dbh,' timestamp_end is not null AND ');
}

function getOpenTripsDailyStats($dbh) {
  return getTripsDailyStats($dbh,' payable=true and  timestamp_end is null AND ');
}


function getTripsDailyStats($dbh, $condition='' ) {
    $result = array();
    $sql = " SELECT date_part('hour',timestamp_beginning) as hour, count(*) as num FROM trips WHERE $condition timestamp_beginning > now()::date GROUP BY hour  ORDER BY hour";
    foreach($dbh->query($sql) as $row) {
        $result[$row['hour']]=$row['num'];
    }

    $data = array();
    for($i=0;$i<24;$i++) {
        if (!array_key_exists($i,$result))
          $data[$i]= array($i,0);
        else
          $data[$i]= array($i,$result[$i]);

    }

    return $data;
}

function getTripsYesterdayStats($dbh) {
    $result = array();
    $sql = " SELECT date_part('hour',timestamp_beginning) as hour, count(*)/30 as num FROM trips " .
           " WHERE   payable=true and timestamp_end is not null and timestamp_beginning > (now()::date - '30 day'::INTERVAL) AND   timestamp_beginning < now()::date  GROUP BY hour  ORDER BY hour";
    foreach($dbh->query($sql) as $row) {
        $result[$row['hour']]=$row['num'];
    }

    $data = array();
    for($i=0;$i<24;$i++) {
        if (!array_key_exists($i,$result))
          $data[$i]= array($i,0);
        else
          $data[$i]= array($i,$result[$i]);

    }

    return $data;
}

function getTripsDistribution($dbh) {
  $result = array('lte10m'=>0, 'lte1h' => 0 , 'lte8h' => 0 , 'lte1d' => 0 , 'gt1d' =>0);
  $sql = "SELECT EXTRACT( EPOCH FROM now() - timestamp_beginning) as duration FROM trips WHERE timestamp_end is null ORDER by duration";

  foreach($dbh->query($sql) as $row) {
    if ($row['duration']<=10*60)
        $result['lte10m']++;
    else if ($row['duration']<=60*60)
        $result['lte1h']++;
    else if ($row['duration']<=480*60)
        $result['lte8h']++;
    else if ($row['duration']<=1440*60)
        $result['lte1d']++;
    else
        $result['gt1d']++;
  }

  return $result;
}

function getPM2info() {
    exec('/usr/local/scripts/pm2-jsdump.sh',$response);
    $json = json_decode($response[0]);
    $data = array();
    foreach($json as $row) {

        $data[$row->name] = new stdclass();
        $data[$row->name]->status = $row->pm2_env->status;
        $data[$row->name]->pm_uptime = $row->pm2_env->pm_uptime;
        $data[$row->name]->created_at = $row->pm2_env->created_at;
        $data[$row->name]->restarts = $row->pm2_env->restart_time;
        $data[$row->name]->unstable_restarts = $row->pm2_env->unstable_restarts;
        $data[$row->name]->pm_id = $row->pm_id;
        $data[$row->name]->memory = $row->monit->memory;
        $data[$row->name]->cpu = $row->monit->cpu;

    }
    return $data;
}
function getDriverLicenseValidation() {
	$data = array();
	
	$curl = curl_init();
	$optArray = array(
		CURLOPT_URL => 'http://95.110.203.186:8080/test/vpn_status.php',
		CURLOPT_RETURNTRANSFER => true
	);
	
	curl_setopt_array($curl, $optArray);
	
	//curl_setopt($curl, CURLOPT_URL, "http://95.110.203.186:8080/test/vpn_status.php");
	$response = curl_exec($curl);
	$responseInfo = curl_getinfo($curl);
	curl_close($curl);
	$data = $response;
	return $data;
}

function checkProcess($process) {
    exec("/bin/pidof \"$process\"",$response);
    if ($response)
        $pids = explode(' ',$response[0]);
    else
        $pids=0;

    return array("running" => ($response?true:false), "pids" => count($pids));
}

function getSystemMemInfo() {
    $data = explode("\n", file_get_contents("/proc/meminfo"));
    $meminfo = array();
    foreach ($data as $line) {
        if (strlen(trim($line))>0) {
        	list($key, $val) = explode(":", $line);
        	$meminfo[$key] = trim($val);
        }
    }
    return $meminfo;
}

function getSystemLoad() {
  $data = explode(" ", file_get_contents("/proc/loadavg"));
  $load = array();
  $load['m1']= floatval($data[0]);
  $load['m5']=floatval($data[1]);
  $load['m15']=floatval($data[2]);
  return $load;
}

function getDiskStats($dir) {
    $df = disk_free_space($dir);
    $ds = disk_total_space($dir);
    $fill = ($df/$ds*100);
    return array("free"=>$df, "size"=>$ds, "fill"=>$fill);
}

function getUptime() {
    $str = trim( file_get_contents( '/proc/uptime' ) );
    $str = explode( ' ', $str );

    $uptime = floatval($str[0]);
    $raw = $uptime;
    $days = floor($uptime/86400);
    $uptime -= $days*86400;
    $hours = floor($uptime/3600);
    $uptime -= $hours*3600;
    $minutes = floor($uptime/60);

    return array('formatted'=>"$days d $hours h $minutes m" , 'raw_uptime' => $raw);

}


function getCache() {
   global $CACHE_FILE;
   if (!file_exists($CACHE_FILE))
     return NULL;

   $cache_age = filemtime($CACHE_FILE);

   if(time()-$cache_age>=5) {
     return NULL;
   }

   try {
       $cache = file_get_contents($CACHE_FILE);
       return $cache;
   } catch(Exception $e) {
       return NULL;
   }
}

function updateCache($newCache) {
   global $CACHE_FILE;
   file_put_contents($CACHE_FILE,$newCache);
}

$cache = getCache();
if ($cache!=NULL) {
  echo $cache;
} else {

  $result = new stdClass();

  $result->system = new stdClass();
  $result->system->load = getSystemLoad();
  $result->system->mem = getSystemMemInfo ();
  $result->system->root = getDiskStats('/');
  $result->system->data = getDiskStats('/var');
  $result->system->uptime = getUptime();
  $result->system->timestamp = time();



  $result->postgres = checkProcess('postgres');
  $result->pgpool = checkProcess('pgpool');
  $result->apache2 = checkProcess('apache2');
  $result->redis_server = checkProcess('redis-server');
  $result->redis_sentinel = checkProcess('redis-sentinel');
  $result->mongodb = checkProcess('mongod');
  $result->pm2 = checkProcess('PM2 v1.1.2: God Daemon');
  $result->pm2['jobs']=getPM2info();
  $result->dlv=getDriverLicenseValidation();
  

  $dbh = getDb();
  $result->db = new stdClass();
  $result->db->trips = getTableSize($dbh,'trips');
  $result->db->trips_last_hour =  getTableSize($dbh,'trips',' payable=true and  timestamp_beginning > NOW() - \'1 hour\'::INTERVAL');
  $result->db->trips_from_midnight =  getTableSize($dbh,'trips','payable=true and  timestamp_beginning > NOW()::date');;
  $result->db->trips_day = getRealTripsDailyStats($dbh);
  $result->db->trips_service_day = getServiceTripsDailyStats($dbh);
  $result->db->trips_open_day = getOpenTripsDailyStats($dbh);
  $result->db->trips_yesterday = getTripsYesterdayStats($dbh);
  $result->db->trips_distribution = getTripsDistribution($dbh);
  $result->db->opentrips =  getTableSize($dbh,'trips',' payable=true and  timestamp_end is null ');
  $result->db->reservations = getTableSize($dbh,'reservations', ' length > 0 ');
  $result->db->cars_mi = getCarsCount($dbh,1);
  $result->db->cars_fi = getCarsCount($dbh,2);
  $result->db->cars_rm = getCarsCount($dbh,3);
  $result->db->cars_mo = getCarsCount($dbh,4);
  $result->db->reservations_count = getReservationsCount($dbh); 

  //$result->pm2 = getPM2stats();


  $json = json_encode($result);
  echo $json;
  updateCache($json);
 }

?>
