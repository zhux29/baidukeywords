<?php
ini_set('max_execution_time','0');
if(!ini_get('safe_mode')){set_time_limit(0);}
header('Content-type: text/html; charset=utf-8');
function search($keyword,$url,$ip ='113.102.128.247',$page = 1 ){
    $res='';
    if($page > 10){
        $res .= '<tr><td>'.$keyword.'</td><td>0</td><td>10页之外</td></tr>';
        return $res;
    }else{
    $pm = ($page - 1) * 10;
    $rsState = false;
    $enKeyword = urlencode($keyword);
    $firstRow = ($page - 1) * 10;
    $urls='https://www.baidu.com/s?ie=utf-8&wd='.$enKeyword.'&pn='.$firstRow;
    $curl = curl_init();
     $dir = pathinfo($urls);//以数组的形式返回路径的信息
     $host = $dir['dirname'];//路径
     $ref = $host.'/';
    curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
    curl_setopt($curl, CURLOPT_URL, $urls); // 要访问的地址    
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
    curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('X-FORWARDED-FOR:'.$ip, 'CLIENT-IP:'.$ip));//IP 
    if($ref){
      curl_setopt($curl, CURLOPT_REFERER, $ref);//带来的Referer
    }else{
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
    }
    curl_setopt($curl, CURLOPT_HTTPGET, 1); // 发送一个常规的Post请求
    curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
    curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
    $contents = curl_exec($curl);
    //print_r(curl_getinfo($curl));
    curl_close($curl);
   $preg='/<div\s+class=\"f13[\s\S]*?\"><a\s+target=\"_blank\"\s+href=\"[^>]+\">[\s\S]*?<\/a><\/div>/i';
    preg_match_all($preg,$contents,$rs);
    //print_r($rs[0]);
    foreach($rs[0] as $k=>$v){
        $pm++;
        if(strstr($v,$url)){
            $rsState = true;
            $res .='<tr><td><a target="_blank" href="http://www.baidu.com/s?wd='.$enKeyword.'&pn='.$firstRow.'">'.$keyword .'</a></td><td>'.$pm.'</td><td>第'. $page . '页</td></tr>';
            return $res;//只返回第一个排名数据
        }
    }
    unset($contents);
    if($rsState === false){
        $res .= search($keyword, $url,$ip,++$page);
    }
    return $res;
    }
}

function getClientIp() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) 
        $ip = $_SERVER['HTTP_CLIENT_IP']; 
    else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) 
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR']; 
    else if (!empty($_SERVER['REMOTE_ADDR'])) 
        $ip = $_SERVER['REMOTE_ADDR']; 
    else 
        $ip = 'err'; 
    return $ip; 
} 

if(isset($_REQUEST['submit'])){
    $time = explode(' ',microtime());
    $start = $time[0] + $time[1];
    $url = $_REQUEST['url'];
    //$ip = getClientIp(); 
    $res = '';
    $keywords=explode("\r\n", trim($_REQUEST['keyword']));
    foreach($keywords as $k=>$v){
    if(!empty($v) && $v != ' ' && $v != NULL)  $res .= search($v,$url);
    }
    $endtime = explode(' ',microtime());
    $end = $endtime[0] + $endtime[1];
    echo '<table border="1" cellspacing="0"><tr><td>关键词</td><td>排名</td><td>位置</td></tr>';
    echo $res;
    echo '</table><hr>程序运行时间: '.($end - $start);
    die();
}
?>
<!DOCTYPE>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>百度关键词排名查询</title>
</head>
<body>
<form action="" method="post">
<ul style="list-style:none;">
<li>
<span style="vertical-align: top;">关键字：</span><textarea name="keyword" rows="20" cols="40" wrap="hard"></textarea>
</li>
<li>
<span>url地址：</span><input type="text" name="url" size="40">
</li>
<li>
<input type="submit" name="submit" value="搜索">
</li>
</ul>
</form>
</body>
</html>