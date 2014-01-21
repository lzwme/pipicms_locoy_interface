<?php
define('PI_ADMIN', preg_replace("|[/\\\]{1,}|",'/',dirname(__FILE__) ) );
require_once(PI_ADMIN."/../include/common.php");
//require_once(PI_INC."/check.admin.php");
require_once(PI_ADMIN."/coplugins/Snoopy.class.php");
header("Cache-Control:private");
error_reporting(E_ALL);
$dsql->safeCheck = false;
$dsql->SetLongLink();

//获得当前脚本名称，如果你的系统被禁用了$_SERVER变量，请自行更改这个选项
$EkNowurl = $s_scriptName = '';
$isUrlOpen = @ini_get("allow_url_fopen");
$EkNowurl = GetCurUrl();
$EkNowurls = explode('?',$EkNowurl);
$s_scriptName = $EkNowurls[0];
$Pirurl=getreferer();
if(empty($Pirurl)) $Pirurl=$EkNowurl;

//检验用户登录状态
if(!isset($_REQUEST['pwd']) || trim($_REQUEST['pwd']) != '123456'){
	die('deny!');
}

//栏目分类列表
if(isset($_REQUEST["list"])){
	echo '<select name="">';
	echo makeTypeOptionSelected(0, '&nbsp;&nbsp;', '', '', 0);
	echo "</select>";
	die();
}

/*
* 参数获取
 */
$v_data = array();

//影片名称
if (isset($_REQUEST['v_name']) && trim($_REQUEST['v_name']) != '') {
	$v_data['v_name'] = $_REQUEST['v_name'];
}else{
	die('影片名称不能为空！');
}
//影片分类
if (isset($_REQUEST['v_type']) && intval($_REQUEST['v_type']) != 0) {
	$v_data['tid'] = intval($_REQUEST['v_type']);
}else{
	die('所属分类不能为空！');
}
//下载地址
if (isset($_REQUEST['v_downdata']) && trim($_REQUEST['v_downdata']) != '') {
	$v_data['v_downdata'] = trim($_REQUEST['v_downdata']);
}
//播放地址
if (isset($_REQUEST['v_playdata']) && trim($_REQUEST['v_playdata']) != '') {
	$v_data['v_playdata'] = trim($_REQUEST['v_playdata']);
}else if (!isset($v_data['v_downdata'])) {
	die('播放地址和下载地址不可同时为空！');
}

//影片播放地址格式化
/*
v_playdata 为非标准格式，则需要格式化
影片数据标准格式参考如下：
	qvod$$第01集$qvod://175874380|9DF962244E01AF80A887D9097C7846DCF94954B6|女人帮妞儿第二季01[高清版][www.qpg001.com].mkv|$qvod#第02集$qvod://156234593|A375670BAA225365A9B5AACE3B79CFEBBB2BD270|女人帮妞儿第二季02[高清版][www.qpg001.com].mkv|$qvod$$$百度影音$$第01集$bdhd://175874380|7846FFFD968E330DA2F37DBBFB35A191|女人帮妞儿第二季01[高清版][www.qpg001.com].mkv$bdhd#第02集$bdhd://156234593|E33CB3B5F3B9F75347622EFA989CCC8F|女人帮妞儿第二季02[高清版][www.qpg001.com].mkv$bdhd
*/
if (isset($v_data['v_playdata']) && strpos($v_data['v_playdata'], '$$') === false) {
	//影片来源前缀
	if (isset($_REQUEST['v_playfrom']) && trim($_REQUEST['v_playfrom']) != '') {
		$v_playfrom = trim($_REQUEST['v_playfrom']);
	}else{
		$v_playfrom = getFromByPlaydata($v_data['v_playdata']);
	}

	//格式化方法，如果 playdata 只是一行一个地址，如
	//bdhd://156234593|E33CB3B5F3B9F75347622EFA989CCC8F|女人帮妞儿第二季02[高清版].mkv
	if (strpos($v_data['v_playdata'], '$') === false) {
		$v_playfrom_id = getReferedId($v_playfrom);	//来源简写id

		$_playdata_array = array_unique(explode("\n", str_replace("\r\n", "\n", $v_data['v_playdata'])));

		foreach ($_playdata_array as $key => $value) {
			if (empty($value)) {
				continue;
			}
			$_playdata_array[$key] = '第' . ($key+1) . '集$'. $value . '$' . $v_playfrom_id;
		}		
		
		$v_data['v_playdata'] = rtrim(implode('#', $_playdata_array), '#');
		
		unset($_playdata_array);
	}
	//加上来源标识
	$v_data['v_playdata'] = $v_playfrom . "$$" . $v_data['v_playdata'];//影片数据地址
}

if (isset($_REQUEST['v_pic']) && trim($_REQUEST['v_pic']) != '') {
	$v_data['v_pic'] =  $_REQUEST['v_pic'];//影片图片地址
}

if (isset($_REQUEST['v_state']) && trim($_REQUEST['v_state']) != '') {
$v_data['v_state'] = $_REQUEST['v_state'];//影片连载状态
}

if (isset($_REQUEST['v_lang']) && trim($_REQUEST['v_lang']) != '') {
$v_data['v_lang'] = $_REQUEST['v_lang'];//影片语言
}

if (isset($_REQUEST['v_publisharea']) && trim($_REQUEST['v_publisharea']) != '') {
	$v_data['v_publisharea'] = $_REQUEST['v_publisharea'];//影片地区
}

if (isset($_REQUEST['v_publishyear']) && trim($_REQUEST['v_publishyear']) != '') {
	$v_data['v_publishyear'] = $_REQUEST['v_publishyear'];//影片年份
}

if (isset($_REQUEST['v_note']) && trim($_REQUEST['v_note']) != '') {
	$v_data['v_note'] = $_REQUEST['v_note'];//影片备注
}

if (isset($_REQUEST['v_actor']) && trim($_REQUEST['v_actor']) != '') {
	$v_data['v_actor'] = $_REQUEST['v_actor'];//主演
}

if (isset($_REQUEST['v_director']) && trim($_REQUEST['v_director']) != '') {
	$v_data['v_director'] = $_REQUEST['v_director'];//导演
}

if (isset($_REQUEST['v_content']) && trim($_REQUEST['v_content']) != '') {
	$v_data['v_des'] = $_REQUEST['v_content'];//影片简介
}

if (isset($_REQUEST['v_tags']) && trim($_REQUEST['v_tags']) != '') {
	$v_data['v_tags'] = $_REQUEST['v_tags'];	//标签
}
if (isset($_REQUEST['v_tags']) && intval($_REQUEST['v_topic']) !== 0) {
	$v_data['v_topic'] = intval($_REQUEST['v_topic']);	//所属专题
}

$v_data['v_enname'] = Pinyin($v_data['v_name']);
$v_data['v_letter'] = strtoupper(substr($v_data['v_enname'],0,1));

$v_data['v_hit'] = rand(50,5000);	//点击数
$v_data['v_commend'] = rand(0,5);//推荐级别
$v_data['v_ismake'] = 0;//是否已生成

//var_dump($v_data);die();
echo $col->_into_database($v_data);
die();

function makeTopicSelect($selectName,$strSelect,$topicId)
{
	global $dsql,$cfg_iscache;
	$sql="select id,name from pi_topic order by sort asc";
	if($cfg_iscache){
	$mycachefile=md5('array_Topic_Lists_all');
	setCache($mycachefile,$sql);
	$rows=getCache($mycachefile);
	}else{
	$rows=array();
	$dsql->SetQuery($sql);
	$dsql->Execute('al');
	while($rowr=$dsql->GetObject('al'))
	{
	$rows[]=$rowr;
	}
	unset($rowr);
	}
	$str = "<select name='".$selectName."' id='".$selectName."' >";
	if(!empty($strSelect)) $str .= "<option value='0'>".$strSelect."</option>";
	foreach($rows as $row)
	{
		if(!empty($topicId) && ($row->id==$topicId)){
		$str .= "<option value='".$row->id."' selected>$row->name</option>";
		}else{
		$str .= "<option value='".$row->id."'>$row->name</option>";
		}
	}
	$str .= "</select>";
	return $str;
}

function makeTypeOptionSelected($topId,$separateStr,$span="",$compareValue,$tptype=0)
{
	$tlist=getTypeListsOnCache($tptype);
	if ($topId!=0){$span.=$separateStr;}else{$span="";}

	foreach($tlist as $row)
	{
		
		if($row->upid==$topId)
		{
		
			if ($row->tid==$compareValue){$selectedStr=" selected";}else{$selectedStr="";}	
			echo "<option value='".$row->tid."'".$selectedStr.">".$span."&nbsp;|—".$row->tname."</option>";
			makeTypeOptionSelected($row->tid,$separateStr,$span,$compareValue,$tptype);
			
		}
	}
	if (!empty($span)){$span=substr($span,(strlen($span)-strlen($separateStr)));}
}
function makeTypeOptionSelected_Multiple($topId,$separateStr,$span="",$compareValue,$tptype=0)
{
	$tlist=getTypeListsOnCache($tptype);
	if ($topId!=0){$span.=$separateStr;}else{$span="";}
	$ids_arr = split('[,]',$compareValue);
	foreach($tlist as $row)
	{
		
		if($row->upid==$topId)
		{
			
			for($i=0;$i<count($ids_arr);$i++)
			{
				if ($row->tid==$ids_arr[$i]){
					$selectedStr=" selected";
					break;
					}
					else
					{
					$selectedStr="";
					}
			}
			
			echo "<option value='".$row->tid."'".$selectedStr.">".$span."&nbsp;|—".$row->tname."</option>";
			makeTypeOptionSelected_Multiple($row->tid,$separateStr,$span,$compareValue,$tptype);
			
		}
	}
	if (!empty($span)){$span=substr($span,(strlen($span)-strlen($separateStr)));}
	
}


function getreferer()
{
	if(isset($_SERVER['HTTP_REFERER']))
	$refurl=$_SERVER['HTTP_REFERER'];
	$url='';
	if(!empty($refurl)){
		$refurlar=explode('/',$refurl);
		$i=count($refurlar)-1;
		$url=$refurlar[$i];
	}
	return $url;
}

function downSinglePic($picUrl,$vid,$vname,$filePath,$infotype)
{
	$spanstr=empty($infotype) ? "" : "<br/>";
	if(empty($picUrl) || substr($picUrl,0,7)!='http://'){
		echo "数据<font color=red>".$vname."</font>的图片路径错误1,请检查图片地址是否有效  ".$spanstr;
		return false;
	}
	$fileext=getFileFormat($filePath);
	$ps=split("/",$picUrl);
	$filename=urldecode($ps[count($ps)-1]);
	if ($fileext!="" && strpos("|.jpg|.gif|.png|.bmp|.jpeg|",strtolower($fileext))>0){
		if(!(strpos($picUrl,".ykimg.com/")>0)){
			if(empty($filename) || strpos($filename,".")==0){
				echo "数据<font color=red>".$vname."</font>的图片路径错误2,请检查图片地址是否有效 ".$spanstr;
				return false;
			}
		}
		$imgStream=getRemoteContent(substr($picUrl,0,strrpos($picUrl,'/')+1).str_replace('+','%20',urlencode($filename)));
		$createStreamFileFlag=createStreamFile($imgStream,$filePath);
		if($createStreamFileFlag){
			$streamLen=strlen($imgStream);
			if($streamLen<2048){
				echo "数据<font color=red>".$vname."</font>的图片下载发生错误5,请检查图片地址是否有效  ".$spanstr;
				return false;
			}else{
				return number_format($streamLen/1024,2);
			}
		}else{
			if(empty($vid)){
				echo "数据<font color=red>".$vname."</font>的图片下载发生错误3,请检查图片地址是否有效  ".$spanstr;
				return false;
			}else{
				echo "数据<font color=red>".$vname."</font>的图片下载发生错误4,id为<font color=red>".$vid."</font>,请检查图片地址是否有效  ".$spanstr;
				return false;
			}
		}
	}else{
		echo "数据<font color=red>".$vname."</font>的图片下载发生错误6,请检查图片地址是否有效  ".$spanstr;
		return false;
	}
}

function uploadftp($picpath,$picfile,$v_name,$picUrl)
{
	require_once(PI_INC."/ftp.class.php");
	$Newpicpath = str_replace("../","",$picpath);
	$ftp = new AppFtp($GLOBALS['app_ftphost'] ,$GLOBALS['app_ftpuser'] ,$GLOBALS['app_ftppass'] , $GLOBALS['app_ftpport'] , $GLOBALS['app_ftpdir']);
	if( $ftp->ftpStatus == 1){;
		$localfile= PI_ROOT .'/'. $Newpicpath . $picfile;
		$remotefile= $GLOBALS['app_ftpdir'].$Newpicpath . $picfile;
		$ftp -> mkdirs( $GLOBALS['app_ftpdir'].$Newpicpath );
		$ftpput = $ftp->put($localfile, $remotefile);
		if(!$ftpput){
			echo "数据$v_name上传图片到FTP远程服务器失败!本地地址$picUrl<br>";
			return false;
		}
		$ftp->bye();
		if ($GLOBALS['app_ftpdel']==1){
			unlink( $picpath . $picfile );
		}
	}
	else{
		echo $ftp->ftpStatusDes;return false;
	}
}

function uploadftp2($picUrl)
{
	require_once(PI_INC."/ftp.class.php");
	$ftp = new AppFtp($GLOBALS['app_ftphost'] ,$GLOBALS['app_ftpuser'] ,$GLOBALS['app_ftppass'] , $GLOBALS['app_ftpport'] , $GLOBALS['app_ftpdir']);
	$picpath = dirname($picUrl).'/';
	if( $ftp->ftpStatus == 1){;
		$localfile= PI_ROOT .'/'. $picUrl;
		$remotefile= $GLOBALS['app_ftpdir'].$picUrl;
		$ftp -> mkdirs( $GLOBALS['app_ftpdir'].$picpath );
		$ftpput = $ftp->put($localfile, $remotefile);
		if(!$ftpput){
			return false;
		}
		$ftp->bye();
		if ($GLOBALS['app_ftpdel']==1){
			unlink( PI_ROOT .'/'. $picUrl );
		}
		return true;
	}
	else{
		echo $ftp->ftpStatusDes;return false;
	}
}

function cache_clear($dir) {
  $dh=@opendir($dir);
  while ($file=@readdir($dh)) {
    if($file!="." && $file!="..") {
      $fullpath=$dir."/".$file;
      if(is_file($fullpath)) {
          @unlink($fullpath);
      }
    }
  }
  closedir($dh); 
}

function getFolderList($cDir)
{
	$dh = dir($cDir);
	$k=0;
	while($filename=$dh->read())
	{
		if($filename=='.' || $filename=='..' || m_ereg("\.inc",$filename)) continue;
		$filetime = filemtime($cDir.'/'.$filename);
		$f[$k]['filetime'] = isCurrentDay($filetime);
		$f[$k]['filename']=$filename;
		if(!m_ereg("\.",$filename)){
			$f[$k]['fileinfo']="文件夹";
		}else{
			$f[$k]['fileinfo']=getTemplateType($filename);
		}
		if(!m_ereg("\.",$filename)){
			$f[$k]['filesize']=getRealSize(getDirSize($cDir.'/'.$filename));
		}else{
			$f[$k]['filesize']=getRealSize(filesize($cDir.'/'.$filename));
		}
		$f[$k]['fileicon']=viewIcon($filename);
		$f[$k]['filetype']=getFileType($filename);
		$k++;
	}
	return $f;
}

function getFileType($filedir)
{
	if(!m_ereg("\.",$filedir)){
		return "folder";
	}else{
		$filetype=strtolower(getfileextend($filedir));
		$imgFileStr=".jpg|.jpeg|.gif|.bmp|.png";
		$pageFileStr =".html|.htm|.js|.css|.txt";
		if(strpos($imgFileStr,$filetype)>0) return "img";
		if(strpos($pageFileStr,$filetype)>0) return "txt";
	}
}

function viewIcon($filename)
{
	if(!m_ereg("\.",$filename)){
		return "folder";
	}else{
		$fileType=strtolower(getfileextend($filename));
		if($fileType=="js" || $fileType=="css"){
			return $fileType;
		}else{
			if ($fileType=="jpg" || $fileType=="jpeg") return "jpg";
			if ($fileType=="htm" || $fileType=="html" || $fileType=="shtml") return "html";
			if ($fileType=="gif" || $fileType=="png") return "gif";
			return "file";
		}
	}
}

function getfileextend($filename)
{ 
	$extend =explode(".", $filename);
	$va=count($extend)-1;
	return $extend[$va];
}


function getDirSize($dir)
{ 
	$handle = opendir($dir);
	$sizeResult = '';
	while (false!==($FolderOrFile = readdir($handle)))
	{ 
		if($FolderOrFile != "." && $FolderOrFile != "..") 
		{ 
			if(is_dir("$dir/$FolderOrFile"))
			{ 
				$sizeResult += getDirSize("$dir/$FolderOrFile"); 
			}
			else
			{ 
				$sizeResult += filesize("$dir/$FolderOrFile"); 
			}
		}    
	}
	closedir($handle);
	return $sizeResult;
}

// 单位自动转换函数
function getRealSize($size)
{ 
	$kb = 1024;         // Kilobyte
	$mb = 1024 * $kb;   // Megabyte
	$gb = 1024 * $mb;   // Gigabyte
	$tb = 1024 * $gb;   // Terabyte
	if($size == 0){
		return "0 B";
	}
	else if($size < $mb)
	{ 
     	return round($size/$kb,2)." K";
	}
	else if($size < $gb)
	{ 
    	return round($size/$mb,2)." M";
	}
	else if($size < $tb)
	{ 
    	return round($size/$gb,2)." G";
	}
	else
	{ 
     	return round($size/$tb,2)." T";
	}
}

/**
 * 由playdata判断播放来源
 * @param  [type] $str [description]
 * @return [type]      [description]
 */
function getFromByPlaydata($str)
{
	if (m_ereg("qvod",$str)) return "qvod";
	if (m_ereg("bdhd",$str)) return "百度影音";
	if (m_ereg("tudou.com",$str)) return "土豆高清";
	if (m_ereg("sina.com.cn",$str)) return "新浪高清";
	if (m_ereg("sohu.com",$str)) return "搜狐高清";
	if (m_ereg("hd_openv",$str)) return "天线高清";
	if (m_ereg("hd_56",$str)) return "56高清";
	if (m_ereg("56.com",$str)) return "56";
	if (m_ereg("youku.com",$str)) return "优酷";
	if (m_ereg("tudou.com",$str)) return "土豆";
	if (m_ereg("sohu",$str)) return "搜狐";
	if (m_ereg("iask",$str)) return "新浪";
	if (m_ereg("6rooms",$str)) return "六间房";
	if (m_ereg("qq.com",$str)) return "qq";
	if (m_ereg("youtube.com",$str)) return "youtube";
	if (m_ereg("17173.com",$str)) return "17173";
	if (m_ereg("ku6.com",$str)) return "ku6视频";
	if (m_ereg("flv",$str)) return "FLV";
	if (m_ereg("swf",$str)) return "SWF数据";
	if (m_ereg("real",$str)) return "real";
	if (m_ereg("media",$str)) return "media";
	if (m_ereg("pps.tv",$str)) return "ppstream";
	if (m_ereg("gvod",$str)) return "迅播高清";
	if (m_ereg("wp2008",$str)) return "远古高清";
	if (m_ereg("ppvod",$str)) return "ppvod高清";
	if (m_ereg("pvod",$str)) return "PVOD";
	if (m_ereg("cc",$str)) return "播客CC";
	if (m_ereg("pipi.cn",$str)) return "皮皮影音";
	if (m_ereg("webplayer9",$str)) return "久久影音";
	if (m_ereg("jidong",$str)) return "激动";
	if (m_ereg("flashPvod",$str)) return "闪播Pvod";

	return 'swf';
}

?>