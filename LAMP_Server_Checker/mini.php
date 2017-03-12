<?php
//error_reporting(0);
@header("content-Type: text/html; charset=utf-8");
//date_default_timezone_set('Asia/Shanghai');
if(isset($_GET["size"]) && $_GET["size"]=='1') {
if(isset($_GET["v"])) $dir=$_GET["v"];
else $dir=$_SERVER['DOCUMENT_ROOT']?str_replace('\\','/',$_SERVER['DOCUMENT_ROOT']):str_replace('\\','/',dirname(__FILE__));
function DirSize($directory){
$dir_size = 0;
if($dir_handle = @opendir($directory)){
	while($filename = readdir($dir_handle)){
		$subFile = $directory."/".$filename;
		if($filename != "." && $filename != ".." && !is_link($subFile)) {
		if(is_dir($subFile)) $dir_size += DirSize($subFile);
		if(is_file($subFile)) $dir_size += filesize($subFile);
		}
	}
	closedir($dir_handle);
	return $dir_size;
	}
}
function formatsize($size) {
$units = array(' B', ' KB', ' MB', ' GB', ' TB');
for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
return round($size, 2).$units[$i];
}
echo $dir.'&nbsp;&nbsp;'.formatsize(DirSize($dir)).'<br />';
echo 'Storage used / total: '.chkspc('u').' GB / '.chkspc('t').' GB<br />';
echo 'Mem used / total: '.chkmem('u').' MB / '.chkmem('t').' MB<br />';
echo '<a href="'.$_SERVER['SCRIPT_NAME'].'">Back</a>';
exit();
}
if(isset($_GET["info"]) && $_GET["info"]=='1') {
	echo '<a href="javascript:history.go(-1)">返回前页</a>';
	phpinfo();
	exit();
}
if(isset($_GET["rechknet"]) && $_GET["rechknet"]=='1' && isset($_GET["v"])) $netdev=$_GET["v"];
else $netdev='?';
function chkmem($type='') {
    if (false === ($str = @file("/proc/meminfo"))) return '0';
    $str = implode("", $str);
    preg_match_all("/MemTotal\s{0,}\:+\s{0,}([\d\.]+).+?MemFree\s{0,}\:+\s{0,}([\d\.]+).+?Cached\s{0,}\:+\s{0,}([\d\.]+).+?SwapTotal\s{0,}\:+\s{0,}([\d\.]+).+?SwapFree\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buf);
	preg_match_all("/Buffers\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buffers);
    $res['memTotal'] = round($buf[1][0]/1024, 2);
    $res['memFree'] = round($buf[2][0]/1024, 2);
    $res['memBuffers'] = round($buffers[1][0]/1024, 2);
	$res['memCached'] = round($buf[3][0]/1024, 2);
    //$res['memUsed'] = $res['memTotal']-$res['memFree'];
    //$res['memPercent'] = (floatval($res['memTotal'])!=0)?round($res['memUsed']/$res['memTotal']*100,2):0;
    $res['memRealUsed'] = $res['memTotal'] - $res['memFree'] - $res['memCached'] - $res['memBuffers'];
	//$res['memRealFree'] = $res['memTotal'] - $res['memRealUsed'];
    $res['memRealPercent'] = (floatval($res['memTotal'])!=0)?round($res['memRealUsed']/$res['memTotal']*100,2):0;
	switch($type) {
		case 't':
		return $res['memTotal'];
		break;
		case 'u':
		return $res['memRealUsed'];
		break;
		case 'f':
		return $res['memFree'];
		break;
		default:
		return $res['memRealPercent'];
		break;
	}
}
function chkspc($type='') {
	$dt = round(@disk_total_space(".")/(1024*1024*1024),3);
	$du = $dt - round(@disk_free_space(".")/(1024*1024*1024),3);
	$res = (floatval($dt)!=0)?round($du/$dt*100,2):0;
	if($type=='t') return $dt;
	else if($type=='u') return $du;
	else return $res;
}
function chknet($mode='',$dev='?') {
	if (false === ($strs = @file("/proc/net/dev"))) return '``';
	for ($i = 2; $i < count($strs); $i++ ) {
		preg_match_all( "/([^\s]+):[\s]{0,}(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/", $strs[$i], $info );
		if($mode=='?eth') {
			echo '<li><a onclick="window.location.href=\''.$_SERVER['SCRIPT_NAME'].'?rechknet=1&v='.$info[1][0].'\'">'.$info[1][0].'</a></li>';
			continue;
		}
		if(($dev!='?')?($info[1][0]==$dev):(($info[1][0]=='eth0' || $info[1][0]=='wlan0') || ($i==count($strs) && $info[1][0]!='eth0' && $info[1][0]!='wlan0' && $info[1][0]=='lo'))) {
			if($mode=='rt') return $info[10][0].'`'.$info[2][0].'`';//tx`rx`
			else if($mode=='tx') return $info[10][0];
			else if($mode=='rx') return $info[2][0];
			else return $info[1][0];//dev
			break;
		}	
	}
}
if(isset($_GET["f"]) && isset($_GET["v"])) {
$netdev=$_GET["v"];
function chkuptime() {
    if (false === ($str = @file("/proc/uptime"))) return '0,0,0,';
    $str = explode(" ", implode("", $str));
    $str = trim($str[0]);
    $min = $str / 60;
    $hours = $min / 60;
    $days = floor($hours / 24);
    $hours = floor($hours - ($days * 24));
    $min = floor($min - ($days * 60 * 24) - ($hours * 60));
    if ($days !== 0) $uptime = $days.','; else $uptime = '0,';
    if ($hours !== 0) $uptime.= $hours.','; else $uptime.= '0,';
    $uptime.= $min.',';
	return $uptime;
}
function chkloadavg() {
    if (false === ($str = @file("/proc/loadavg"))) return '0.00,0.00,0.00,';
    $str = explode(" ", implode("", $str));
    $str = array_chunk($str, 3);
    $res = implode(",", $str[0]);
    return $res.',';
}
$stat='';
$stat=chknet('rt',$netdev);
$stat.=date("Y-n-j H:i:s").',';
$stat.=chkuptime();
$stat.=chkloadavg();
$stat.=chkmem().',';
$stat.=chkspc();
exit($stat); //Send csv
}
function chksqlite3() {
	if(class_exists('PDO') && extension_loaded('pdo_sqlite')) {
		$sqlite = SQLite3::version();
		return '运行正常, Version '.$sqlite['versionString'];
	} else return 'Error';
}
$info='';
$info='操作系统： '.php_uname('s').' '.php_uname('r').'<br />';
$info.='HTTPD程序： '.($_SERVER['SERVER_SOFTWARE'] ? $_SERVER['SERVER_SOFTWARE'] : getenv('SERVER_SOFTWARE')).'<br />';
$info.='PHP版本： <a onclick="window.location.href=\''.$_SERVER['SCRIPT_NAME'].'?info=1\'">'.phpversion().'</a><br />';
$info.='上传文件大小限制： '.ini_get('upload_max_filesize').'<br />';
$info.='POST方法上传限制： '.ini_get('post_max_size').'<br />';
$info.='PDO SQLite库状态： '.chksqlite3().'<br />';
?>
<!DOCTYPE html>
<html>
<head>
<title>服务器信息</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style type="text/css">
html {
	font-family:Sans-serif;
	font-size:100%;
	line-height:1.6em;
	cursor: default;
	moz-user-select:-moz-none;
	-moz-user-select:none;
	-o-user-select:none;
	-khtml-user-select:none;
	-webkit-user-select:none;
	-ms-user-select:none;
	user-select:none;
}
body {
	text-align:left;
}
a {
	cursor:default;
	text-decoration:none;
	color:#000;
}
#main {
	margin:0 auto;
	width:25em;
}
h1 {
	font-size:1.2em;
	font-weight:normal;
}
</style>
<?php if(isset($_GET["rechknet"]) && $_GET["rechknet"]=='1' && !isset($_GET["v"])) { ?>
</head><body><h1>选择一个网络设备：</h1><ul>
<?php chknet('?eth'); ?></ul><a href="javascript:history.go(-1)">返回前页</a></body></html>
<?php exit(); } ?>
<script type="text/javascript">
function uaredirect(f){try{var b=false;if(arguments[1]){var e=window.location.host;var a=window.location.href;if(isSubdomain(arguments[1],e)==1){f=f+"/#m/"+a;b=true}else{if(isSubdomain(arguments[1],e)==2){f=f+"/#m/"+a;b=true}else{f=a;b=false}}}else{b=true}if(b){var c=window.location.hash;if(!c.match("fromapp")){if((navigator.userAgent.match(/(iPhone|iPod|Android|ios)/i))){location.replace(f)}}}}catch(d){}}function isSubdomain(c,d){this.getdomain=function(f){var e=f.indexOf("://");if(e>0){var h=f.substr(e+3)}else{var h=f}var g=/^www\./;if(g.test(h)){h=h.substr(4)}return h};if(c==d){return 1}else{var c=this.getdomain(c);var b=this.getdomain(d);if(c==b){return 1}else{c=c.replace(".","\\.");var a=new RegExp("\\."+c+"$");if(b.match(a)){return 2}else{return 0}}}};
uaredirect("<?php echo $_SERVER['SCRIPT_NAME']; ?>"+"?mobile=1","<?php echo $_SERVER['SCRIPT_NAME']; ?>");
function nomenu() {
	return false;
}
document.oncontextmenu = nomenu;
function randomplus(min,max) {
	var str = "",arr = ['0','1','2','3','4','5','6','7','8','9','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
	min = Math.round(Math.random() * (max-min)) + min;
	for(var i=0; i<min; i++) {
		pos = Math.round(Math.random() * (arr.length-1));
		str += arr[pos];
	}
	return str;
}
var ts=<?php echo chknet('tx',$netdev); ?>;
var rs=<?php echo chknet('rx',$netdev); ?>;
function formats(Dight,How) {
  if (Dight<0){
  	var Last=0+" B/s";
  }else if (Dight<1024){
  	var Last=Math.round(Dight*Math.pow(10,How))/Math.pow(10,How)+" B/s";
  }else if (Dight<1048576){
  	Dight=Dight/1024;
  	var Last=Math.round(Dight*Math.pow(10,How))/Math.pow(10,How)+" KB/s";
  }else{
  	Dight=Dight/1048576;
  	var Last=Math.round(Dight*Math.pow(10,How))/Math.pow(10,How)+" MB/s";
  }
	return Last; 
}
function getdata() {
var xmlhttp;
if (window.XMLHttpRequest) {
  xmlhttp=new XMLHttpRequest();
  }
else {
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttp.open("GET","<?php echo $_SERVER['SCRIPT_NAME']; ?>?v=<?php echo $netdev; ?>&f=" + randomplus(2,4),true);
xmlhttp.onreadystatechange=function() {
  if (xmlhttp.readyState==4 && xmlhttp.status==200) {
	  var strs=xmlhttp.responseText.split("`");
	  var data=strs[2].split(",");
	  var div=document.getElementById("realtime");
	  var span=div.getElementsByTagName("span");
	  for(var i=0;i<span.length;i++) {
		  span[i].innerHTML=data[i];
	  }
	  document.getElementById("ts").innerHTML=(formats((strs[0]-ts),1));ts=strs[0];
	  document.getElementById("rs").innerHTML=(formats((strs[1]-rs),1));rs=strs[1];
    }
  }
xmlhttp.send();
}
window.setInterval(getdata,1000);
</script>
</head>
<body>
<div id="main">
<div id="static"><a onclick="window.location.href='<?php echo $_SERVER['SCRIPT_NAME']; ?>'"><h1>服务器信息： </h1></a><?php echo $info; ?>
已载入模块： <?php echo '<a onclick="window.location.href=\'';
if(isset($_GET["allmod"])) echo 'javascript:history.go(-1)';
else {
	($_SERVER["QUERY_STRING"]=='')?($qs='allmod=1'):($qs=$_SERVER["QUERY_STRING"].'&allmod=1');
	echo $_SERVER['SCRIPT_NAME'].'?'.$qs;
}
echo '\'">'.count(get_loaded_extensions()).'</a> 个<br />';
if(isset($_GET["allmod"]) && $_GET["allmod"]=='1') {
	$able=get_loaded_extensions();
foreach ($able as $key=>$value) {
	if ($key!=0 && $key%5==0) {
		echo '<br />';
	}
	echo "$value,&nbsp;";
	}
} ?>
<h1>实时状态： </h1></div>
<div id="realtime">
<table class="rt1"><tbody>
<tr><td>服务器时间： </td><td colspan="4"><span id="time"><?php echo date("Y-n-j H:i:s"); ?></span></td></tr>
<tr><td>运行时长： </td><td colspan="4"><span id="upday">0</span> 天 <span id="uphour">0</span> 小时 <span id="upmin">0</span> 分钟</td></tr>
<tr><td>平均负荷： </td><td colspan="4"><span id="load1">0.00</span>,&nbsp;&nbsp;<span id="load5">0.00</span>,&nbsp;&nbsp;<span id="load15">0.00</span></td></tr>
<tr><td>内存使用率： </td><td colspan="4"><span id="mem"><?php echo chkmem(); ?></span>%</td></tr>
<tr><td>磁盘使用率： </td><td colspan="4"><span id="dsk"><?php echo chkspc(); ?></span>%</td></tr>
<tr><td><a onclick="window.location.href='<?php echo $_SERVER['SCRIPT_NAME'] ?>?rechknet=1'"><?php echo chknet('',$netdev); ?></a>速率： </td><td>Tx </td><td style="width:5.5em;"><span id="ts">0 B/s</span></td><td>Rx </td><td style="width:5.5em;"><span id="rs">0 B/s</span></td></tr>
</tbody></table>
</div></div>
</body>
</html>