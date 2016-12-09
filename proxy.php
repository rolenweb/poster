<?php
require 'vendor/autoload.php';
require 'tools/CurlClient.php';
require 'config/wp.php';

use Goutte\Client;
use tools\CurlClient;
use Jitsu\RegexUtil;
use Simplon\Mysql\Mysql;
use Sabre\Xml\Reader;


$url = $wp_login[4]['url'];
$user = $wp_login[4]['login'];
$psw = $wp_login[4]['pass'];

$type_proxy = ['allproxy','socks5','socks4','socks45','US','UK','RU','AR','FR','BR','CA','CN','CO','CZ','DE','HK','IN','ID','IR','IT','KR','MX','NL','SG'];

if (empty($type_proxy)) {
	error('The type is null');
	die;
}

for (;;) {
	$f_schedule = 'schedule/proxy.txt';
	$schedule = file($f_schedule);
	if (empty($schedule[0])) {
		error('Schedule is null');
		break;
	}
	info('Start: '.$schedule[0]);
	switch ($schedule[0]) {
		case 'allproxy':
			$proxy = parseAllProxy();
			$title = maskAllProxe()[rand(0,count(maskAllProxe())-1)];
			break;
		case 'socks5':
			$proxy = parseSocks5();
			$title = maskSocks5()[rand(0,count(maskSocks5())-1)];
			break;
		case 'socks4':
			$proxy = parseSocks4();
			$title = maskSocks4()[rand(0,count(maskSocks4())-1)];
			break;
		case 'socks45':
			$proxy = parseSocks45();
			$title = maskSocks45()[rand(0,count(maskSocks45())-1)];
			break;
		case 'US':
			$proxy = parseProxyCountry('US');
			$title = str_replace('CODE','us',maskProxyCountry()[rand(0,count(maskProxyCountry())-1)]);
			break;
		case 'UK':
			$proxy = parseProxyCountry('UK');
			$title = str_replace('CODE','uk',maskProxyCountry()[rand(0,count(maskProxyCountry())-1)]);
			break;
		case 'RU':
			$proxy = parseProxyCountry('RU');
			$title = str_replace('CODE','Russia',maskProxyCountry()[rand(0,count(maskProxyCountry())-1)]);
			break;
		case 'AR':
			$proxy = parseProxyCountry('AR');
			$title = str_replace('CODE','Argentina',maskProxyCountry()[rand(0,count(maskProxyCountry())-1)]);
			break;
		case 'FR':
			$proxy = parseProxyCountry('FR');
			$title = str_replace('CODE','France',maskProxyCountry()[rand(0,count(maskProxyCountry())-1)]);
			break;
		case 'BR':
			$proxy = parseProxyCountry('BR');
			$title = str_replace('CODE','Brazil',maskProxyCountry()[rand(0,count(maskProxyCountry())-1)]);
			break;
		case 'CA':
			$proxy = parseProxyCountry('CA');
			$title = str_replace('CODE','Canada',maskProxyCountry()[rand(0,count(maskProxyCountry())-1)]);
			break;
		case 'CN':
			$proxy = parseProxyCountry('CN');
			$title = str_replace('CODE','China',maskProxyCountry()[rand(0,count(maskProxyCountry())-1)]);
			break;
		case 'CO':
			$proxy = parseProxyCountry('CO');
			$title = str_replace('CODE','Colombia',maskProxyCountry()[rand(0,count(maskProxyCountry())-1)]);
			break;
		case 'CZ':
			$proxy = parseProxyCountry('CZ');
			$title = str_replace('CODE','Czech Republic',maskProxyCountry()[rand(0,count(maskProxyCountry())-1)]);
			break;
		case 'DE':
			$proxy = parseProxyCountry('DE');
			$title = str_replace('CODE','Germany',maskProxyCountry()[rand(0,count(maskProxyCountry())-1)]);
			break;
		case 'HK':
			$proxy = parseProxyCountry('HK');
			$title = str_replace('CODE','Hong Kong',maskProxyCountry()[rand(0,count(maskProxyCountry())-1)]);
			break;
		case 'IN':
			$proxy = parseProxyCountry('IN');
			$title = str_replace('CODE','India',maskProxyCountry()[rand(0,count(maskProxyCountry())-1)]);
			break;
		case 'ID':
			$proxy = parseProxyCountry('ID');
			$title = str_replace('CODE','Indonesia',maskProxyCountry()[rand(0,count(maskProxyCountry())-1)]);
			break;
		case 'IR':
			$proxy = parseProxyCountry('IR');
			$title = str_replace('CODE','Iran',maskProxyCountry()[rand(0,count(maskProxyCountry())-1)]);
			break;
		case 'IT':
			$proxy = parseProxyCountry('IT');
			$title = str_replace('CODE','Italy',maskProxyCountry()[rand(0,count(maskProxyCountry())-1)]);
			break;
		case 'KR':
			$proxy = parseProxyCountry('KR');
			$title = str_replace('CODE','Korea',maskProxyCountry()[rand(0,count(maskProxyCountry())-1)]);
			break;
		case 'MX':
			$proxy = parseProxyCountry('MX');
			$title = str_replace('CODE','Mexico',maskProxyCountry()[rand(0,count(maskProxyCountry())-1)]);
			break;
		case 'NL':
			$proxy = parseProxyCountry('NL');
			$title = str_replace('CODE','Netherlands',maskProxyCountry()[rand(0,count(maskProxyCountry())-1)]);
			break;
		case 'SG':
			$proxy = parseProxyCountry('SG');
			$title = str_replace('CODE','Singapore',maskProxyCountry()[rand(0,count(maskProxyCountry())-1)]);
			break;
		
	}
	if (empty($proxy)) {
		error('Proxy: '.$schedule[0].' is null');
		break;
	}

	if (empty($title)) {
		error('Title is null');
		break;
	}

	$content = createContent($title,$proxy);
	$tags = findTags($title);

	$content['tags'] = [];
	$post = post($url,$user,$psw,$content['title'],$content['description'],$tags);
    error($post);

	$current_key = array_search($schedule[0],$type_proxy);
	$next_key = ($current_key < count($type_proxy)-1) ? $current_key+1 : 0;
	file_put_contents($f_schedule, $type_proxy[$next_key]);
	$time_sleep = rand(3000,4200);
	info('Sleep: '.$time_sleep.' sec.');
}

function createContent($title,$list)
{
	$limit = (count($list) > 20) ? 20 : count($list);
	$content = [];
	$content['title'] = ucfirst($title).' on '.date("M d, Y");
    $content['description'] = '<table class = "table table-bordered">';  
    $content['description'] .= '<thead>';
    $content['description'] .= '<tr>';         
    $content['description'] .= '<th>';
    $content['description'] .= 'IP adress';         
    $content['description'] .= '</th>';         
    $content['description'] .= '<th>';
    $content['description'] .= 'Port';         
    $content['description'] .= '</th>';         
    $content['description'] .= '<th>';
    $content['description'] .= 'Country, City';         
    $content['description'] .= '</th>';         
    $content['description'] .= '<th>';
    $content['description'] .= 'Speed';         
    $content['description'] .= '</th>';         
    $content['description'] .= '<th>';
    $content['description'] .= 'Type';         
    $content['description'] .= '</th>';         
    $content['description'] .= '<th>';
    $content['description'] .= 'Anonymity';         
    $content['description'] .= '</th>';         
    $content['description'] .= '</tr>';         
    $content['description'] .= '</thead>';
    $content['description'] .= '<tbody>';         
    foreach ($list as $n => $item) {
    	if ($n <= $limit) {
    		$content['description'] .= '<tr>';         
	    	$content['description'] .= '<td>';         
	    	$content['description'] .= trim($item['ip']);         	
	    	$content['description'] .= '</td>';              	
	    	$content['description'] .= '<td>';         
	    	$content['description'] .= trim($item['port']);         	
	    	$content['description'] .= '</td>';              	
	    	$content['description'] .= '<td>';         
	    	$content['description'] .= trim($item['country']);         	
	    	$content['description'] .= '</td>';              	
	    	$content['description'] .= '<td>';         
	    	$content['description'] .= trim($item['speed']);         	
	    	$content['description'] .= '</td>';              	
	    	$content['description'] .= '<td>';         
	    	$content['description'] .= trim($item['type']);         	
	    	$content['description'] .= '</td>';              	
	    	$content['description'] .= '<td>';         
	    	$content['description'] .= trim($item['anonymous']);         	
	    	$content['description'] .= '</td>';              	
	    	$content['description'] .= '</tr>'; 
    	}
    	             	
    }     
    $content['description'] .= '</tbody>';         
    $content['description'] .= '</table>';         
    return $content;
}


function parseAllProxy()
{
	$out = [];
	$hideme = hideme('proxy'); //http://incloak.com
	if (empty($hideme) === false) {
		$out = $hideme;
	}
	$list1 = list1('proxy');
	if (empty($list1) === false) {
		$out = array_merge($out,$list1);
	}
	return $out;
}

function parseSocks5()
{
	$out = [];
	$hideme = hideme('socks5'); //http://incloak.com

	if (empty($hideme) === false) {
		$out = $hideme;
	}
	$list1 = list1('socks5');
	
	if (empty($list1) === false) {
		$out = array_merge($out,$list1);
	}
	return $out;
}

function parseSocks4()
{
	$out = [];
	$hideme = hideme('socks4'); //http://incloak.com
	if (empty($hideme) === false) {
		$out = $hideme;
	}
	
	return $out;
}

function parseSocks45()
{
	$out = [];
	$hideme = hideme('socks45'); //http://incloak.com

	if (empty($hideme) === false) {
		$out = $hideme;
	}
	$list1 = list1('socks5');
	
	if (empty($list1) === false) {
		$out = array_merge($out,$list1);
	}
	return $out;
}

function parseProxyCountry($name)
{
	$out = [];
	$hideme = hideme($name); //http://incloak.com
	//var_dump($hideme);
	//die;
	if (empty($hideme) === false) {
		$out = $hideme;
	}
	$list1 = list1($name);
	
	if (empty($list1) === false) {
		$out = array_merge($out,$list1);
	}
	return $out;
}

function hideme($type)
{
	$out = [];
	switch ($type) {
		case 'proxy':
			$url = 'http://incloak.com/proxy-list/?type=hs';
			break;

		case 'socks5':
			$url = 'http://incloak.com/proxy-list/?type=5';
			break;
		
		case 'socks4':
			$url = 'http://incloak.com/proxy-list/?type=4';
			break;

		case 'socks45':
			$url = 'http://incloak.com/proxy-list/?type=45';
			break;

		case 'US':
			$url = 'http://incloak.com/proxy-list/?country=US';
			break;

		case 'UK':
			$url = 'http://incloak.com/proxy-list/?country=GB';
			break;

		case 'RU':
			$url = 'http://incloak.com/proxy-list/?country=RU';
			break;

		case 'AR':
			$url = 'http://incloak.com/proxy-list/?country=AR';
			break;

		case 'FR':
			$url = 'http://incloak.com/proxy-list/?country=FR';
			break;

		case 'BR':
			$url = 'http://incloak.com/proxy-list/?country=BR';
			break;

		case 'CA':
			$url = 'http://incloak.com/proxy-list/?country=CA';
			break;

		case 'CN':
			$url = 'http://incloak.com/proxy-list/?country=CN';
			break;

		case 'CO':
			$url = 'http://incloak.com/proxy-list/?country=CO';
			break;

		case 'CZ':
			$url = 'http://incloak.com/proxy-list/?country=CZ';
			break;

		case 'DE':
			$url = 'http://incloak.com/proxy-list/?country=DE';
			break;

		case 'HK':
			$url = 'http://incloak.com/proxy-list/?country=HK';
			break;

		case 'IN':
			$url = 'http://incloak.com/proxy-list/?country=IN';
			break;

		case 'ID':
			$url = 'http://incloak.com/proxy-list/?country=ID';
			break;

		case 'IR':
			$url = 'http://incloak.com/proxy-list/?country=IR';
			break;

		case 'IT':
			$url = 'http://incloak.com/proxy-list/?country=IT';
			break;

		case 'KR':
			$url = 'http://incloak.com/proxy-list/?country=KR';
			break;

		case 'MX':
			$url = 'http://incloak.com/proxy-list/?country=MX';
			break;

		case 'NL':
			$url = 'http://incloak.com/proxy-list/?country=NL';
			break;

		case 'SG':
			$url = 'http://incloak.com/proxy-list/?country=SG';
			break;

		case 'SE':
			$url = 'http://incloak.com/proxy-list/?country=SE';
			break;

		case 'TH':
			$url = 'http://incloak.com/proxy-list/?country=TH';
			break;

		case 'UA':
			$url = 'http://incloak.com/proxy-list/?country=UA';
			break;
		default:
			# code...
			break;
	}
	$client = new CurlClient();
	$content = parseUrl($url);
	$ips = $client->parseProperty($content,'string','table.proxy__t td.tdl',$url,null);
	$ports = $client->parseProperty($content,'string','table.proxy__t tr td:nth-child(2)',$url,null);
	$countries = $client->parseProperty($content,'string','table.proxy__t tr td:nth-child(3) div',$url,null);
	$speeds = $client->parseProperty($content,'string','table.proxy__t tr td:nth-child(4) div div p',$url,null);
	$type = $client->parseProperty($content,'string','table.proxy__t tr td:nth-child(5)',$url,null);
	$anonymous = $client->parseProperty($content,'string','table.proxy__t tr td:nth-child(6)',$url,null);

	if (empty($ips)) {
		error('Hideme: list of the ip is null');
		return;
	}
	$i = 0;
	foreach ($ips as $n => $ip) {
		$out[$i]['ip'] = $ip;
		$out[$i]['port'] = (empty($ports[$n]) === false) ? trim($ports[$n]) : null;
		$out[$i]['country'] = (empty($countries[$n]) === false) ? trim($countries[$n]) : null;
		$out[$i]['speed'] = (empty($speeds[$n]) === false) ? trim($speeds[$n]) : null;
		$out[$i]['type'] = (empty($type[$n]) === false) ? trim($type[$n]) : null;
		$out[$i]['anonymous'] = (empty($anonymous[$n]) === false) ? trim($anonymous[$n]) : null;
		$i++;
	}

	return $out;
}

function list1($type)
{
	$out = [];

	switch ($type) {
		case 'proxy':
			$url = 'http://free-proxy-list.net/';
			break;

		case 'socks5':
			$url = 'http://www.socks-proxy.net/';
			break;
		
		case 'US':
			$url = 'http://www.us-proxy.org/';
			break;

		case 'UK':
			$url = 'http://free-proxy-list.net/uk-proxy.html';
			break;

		case 'RU':
			$url = 'http://free-proxy-list.net/';
			break;

		case 'AR':
			$url = 'http://free-proxy-list.net/';
			break;

		case 'FR':
			$url = 'http://free-proxy-list.net/';
			break;

		case 'BR':
			$url = 'http://free-proxy-list.net/';
			break;

		case 'CA':
			$url = 'http://free-proxy-list.net/';
			break;

		case 'CN':
			$url = 'http://free-proxy-list.net/';
			break;

		case 'CO':
			$url = 'http://free-proxy-list.net/';
			break;

		case 'CZ':
			$url = 'http://free-proxy-list.net/';
			break;

		case 'DE':
			$url = 'http://free-proxy-list.net/';
			break;

		case 'HK':
			$url = 'http://free-proxy-list.net/';
			break;

		case 'IN':
			$url = 'http://free-proxy-list.net/';
			break;

		case 'ID':
			$url = 'http://free-proxy-list.net/';
			break;

		case 'IR':
			$url = 'http://free-proxy-list.net/';
			break;

		case 'IT':
			$url = 'http://free-proxy-list.net/';
			break;

		case 'KR':
			$url = 'http://free-proxy-list.net/';
			break;

		case 'MX':
			$url = 'http://free-proxy-list.net/';
			break;

		case 'NL':
			$url = 'http://free-proxy-list.net/';
			break;

		case 'SG':
			$url = 'http://free-proxy-list.net/';
			break;

		case 'SE':
			$url = 'http://free-proxy-list.net/';
			break;

		case 'TH':
			$url = 'http://free-proxy-list.net/';
			break;

		case 'UA':
			$url = 'http://free-proxy-list.net/';
			break;
		default:
			# code...
			break;
	}
	
	$client = new CurlClient();
	$content = parseUrl($url);
	switch ($type) {
		case 'proxy':
			$ips = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(1)',$url,null);
			$ports = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(2)',$url,null);
			$countries = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(4)',$url,null);
			$anonymous = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(5)',$url,null);
			break;
		case 'socks5':
			
			$ips = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(1)',$url,null);
			$ports = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(2)',$url,null);
			$countries = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(4)',$url,null);
			$type = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(5)',$url,null);
			$anonymous = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(6)',$url,null);
			break;

		case 'US':
			
			$ips = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(1)',$url,null);
			$ports = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(2)',$url,null);
			$countries = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(4)',$url,null);
			$anonymous = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(5)',$url,null);
			
			break;

		case 'UK':
			
			$ips = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(1)',$url,null);
			$ports = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(2)',$url,null);
			$countries = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(4)',$url,null);
			$anonymous = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(5)',$url,null);
			break;
		case 'RU':
			
			$ips = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(1)',$url,null);
			$ports = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(2)',$url,null);
			$countries_code = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(3)',$url,null);
			$countries = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(4)',$url,null);
			$anonymous = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(5)',$url,null);

			if (empty($ips)) {
				error('List1: list of the ip is null');
				return;
			}
			$i = 0;
			foreach ($ips as $n => $ip) {
				$code = (empty($countries_code[$n]) === false) ? trim($countries_code[$n]) : null;
				if ($code === $type) {
					$out[$i]['ip'] = $ip;
					$out[$i]['port'] = (empty($ports[$n]) === false) ? trim($ports[$n]) : null;
					$out[$i]['country'] = (empty($countries[$n]) === false) ? trim($countries[$n]) : null;
					$out[$i]['speed'] = (empty($speeds[$n]) === false) ? trim($speeds[$n]) : null;
					$out[$i]['type'] = (empty($type[$n]) === false) ? trim($type[$n]) : 'HTTP';
					$out[$i]['anonymous'] = (empty($anonymous[$n]) === false) ? synAnonymous(trim($anonymous[$n])) : null;
					$i++;
				}
				
			}

			return $out;
			
			break;

		case 'AR':
			
			$ips = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(1)',$url,null);
			$ports = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(2)',$url,null);
			$countries_code = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(3)',$url,null);
			$countries = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(4)',$url,null);
			$anonymous = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(5)',$url,null);

			if (empty($ips)) {
				error('List1: list of the ip is null');
				return;
			}
			$i = 0;
			foreach ($ips as $n => $ip) {
				$code = (empty($countries_code[$n]) === false) ? trim($countries_code[$n]) : null;
				if ($code === $type) {
					$out[$i]['ip'] = $ip;
					$out[$i]['port'] = (empty($ports[$n]) === false) ? trim($ports[$n]) : null;
					$out[$i]['country'] = (empty($countries[$n]) === false) ? trim($countries[$n]) : null;
					$out[$i]['speed'] = (empty($speeds[$n]) === false) ? trim($speeds[$n]) : null;
					$out[$i]['type'] = (empty($type[$n]) === false) ? trim($type[$n]) : 'HTTP';
					$out[$i]['anonymous'] = (empty($anonymous[$n]) === false) ? synAnonymous(trim($anonymous[$n])) : null;
					$i++;
				}
				
			}

			return $out;
			
			break;

		case 'FR':
			
			$ips = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(1)',$url,null);
			$ports = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(2)',$url,null);
			$countries_code = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(3)',$url,null);
			$countries = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(4)',$url,null);
			$anonymous = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(5)',$url,null);

			if (empty($ips)) {
				error('List1: list of the ip is null');
				return;
			}
			$i = 0;
			foreach ($ips as $n => $ip) {
				$code = (empty($countries_code[$n]) === false) ? trim($countries_code[$n]) : null;
				if ($code === $type) {
					$out[$i]['ip'] = $ip;
					$out[$i]['port'] = (empty($ports[$n]) === false) ? trim($ports[$n]) : null;
					$out[$i]['country'] = (empty($countries[$n]) === false) ? trim($countries[$n]) : null;
					$out[$i]['speed'] = (empty($speeds[$n]) === false) ? trim($speeds[$n]) : null;
					$out[$i]['type'] = (empty($type[$n]) === false) ? trim($type[$n]) : 'HTTP';
					$out[$i]['anonymous'] = (empty($anonymous[$n]) === false) ? synAnonymous(trim($anonymous[$n])) : null;
					$i++;
				}
				
			}

			return $out;
			
			break;

		case 'BR':
			
			$ips = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(1)',$url,null);
			$ports = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(2)',$url,null);
			$countries_code = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(3)',$url,null);
			$countries = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(4)',$url,null);
			$anonymous = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(5)',$url,null);

			if (empty($ips)) {
				error('List1: list of the ip is null');
				return;
			}
			$i = 0;
			foreach ($ips as $n => $ip) {
				$code = (empty($countries_code[$n]) === false) ? trim($countries_code[$n]) : null;
				if ($code === $type) {
					$out[$i]['ip'] = $ip;
					$out[$i]['port'] = (empty($ports[$n]) === false) ? trim($ports[$n]) : null;
					$out[$i]['country'] = (empty($countries[$n]) === false) ? trim($countries[$n]) : null;
					$out[$i]['speed'] = (empty($speeds[$n]) === false) ? trim($speeds[$n]) : null;
					$out[$i]['type'] = (empty($type[$n]) === false) ? trim($type[$n]) : 'HTTP';
					$out[$i]['anonymous'] = (empty($anonymous[$n]) === false) ? synAnonymous(trim($anonymous[$n])) : null;
					$i++;
				}
				
			}

			return $out;
			
			break;

		case 'CA':
			
			$ips = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(1)',$url,null);
			$ports = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(2)',$url,null);
			$countries_code = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(3)',$url,null);
			$countries = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(4)',$url,null);
			$anonymous = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(5)',$url,null);

			if (empty($ips)) {
				error('List1: list of the ip is null');
				return;
			}
			$i = 0;
			foreach ($ips as $n => $ip) {
				$code = (empty($countries_code[$n]) === false) ? trim($countries_code[$n]) : null;
				if ($code === $type) {
					$out[$i]['ip'] = $ip;
					$out[$i]['port'] = (empty($ports[$n]) === false) ? trim($ports[$n]) : null;
					$out[$i]['country'] = (empty($countries[$n]) === false) ? trim($countries[$n]) : null;
					$out[$i]['speed'] = (empty($speeds[$n]) === false) ? trim($speeds[$n]) : null;
					$out[$i]['type'] = (empty($type[$n]) === false) ? trim($type[$n]) : 'HTTP';
					$out[$i]['anonymous'] = (empty($anonymous[$n]) === false) ? synAnonymous(trim($anonymous[$n])) : null;
					$i++;
				}
				
			}

			return $out;
			
			break;

		case 'CN':
			
			$ips = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(1)',$url,null);
			$ports = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(2)',$url,null);
			$countries_code = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(3)',$url,null);
			$countries = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(4)',$url,null);
			$anonymous = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(5)',$url,null);

			if (empty($ips)) {
				error('List1: list of the ip is null');
				return;
			}
			$i = 0;
			foreach ($ips as $n => $ip) {
				$code = (empty($countries_code[$n]) === false) ? trim($countries_code[$n]) : null;
				if ($code === $type) {
					$out[$i]['ip'] = $ip;
					$out[$i]['port'] = (empty($ports[$n]) === false) ? trim($ports[$n]) : null;
					$out[$i]['country'] = (empty($countries[$n]) === false) ? trim($countries[$n]) : null;
					$out[$i]['speed'] = (empty($speeds[$n]) === false) ? trim($speeds[$n]) : null;
					$out[$i]['type'] = (empty($type[$n]) === false) ? trim($type[$n]) : 'HTTP';
					$out[$i]['anonymous'] = (empty($anonymous[$n]) === false) ? synAnonymous(trim($anonymous[$n])) : null;
					$i++;
				}
				
			}

			return $out;
			
			break;

		case 'CO':
			
			$ips = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(1)',$url,null);
			$ports = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(2)',$url,null);
			$countries_code = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(3)',$url,null);
			$countries = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(4)',$url,null);
			$anonymous = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(5)',$url,null);

			if (empty($ips)) {
				error('List1: list of the ip is null');
				return;
			}
			$i = 0;
			foreach ($ips as $n => $ip) {
				$code = (empty($countries_code[$n]) === false) ? trim($countries_code[$n]) : null;
				if ($code === $type) {
					$out[$i]['ip'] = $ip;
					$out[$i]['port'] = (empty($ports[$n]) === false) ? trim($ports[$n]) : null;
					$out[$i]['country'] = (empty($countries[$n]) === false) ? trim($countries[$n]) : null;
					$out[$i]['speed'] = (empty($speeds[$n]) === false) ? trim($speeds[$n]) : null;
					$out[$i]['type'] = (empty($type[$n]) === false) ? trim($type[$n]) : 'HTTP';
					$out[$i]['anonymous'] = (empty($anonymous[$n]) === false) ? synAnonymous(trim($anonymous[$n])) : null;
					$i++;
				}
				
			}

			return $out;
			
			break;

		case 'CZ':
			
			$ips = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(1)',$url,null);
			$ports = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(2)',$url,null);
			$countries_code = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(3)',$url,null);
			$countries = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(4)',$url,null);
			$anonymous = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(5)',$url,null);

			if (empty($ips)) {
				error('List1: list of the ip is null');
				return;
			}
			$i = 0;
			foreach ($ips as $n => $ip) {
				$code = (empty($countries_code[$n]) === false) ? trim($countries_code[$n]) : null;
				if ($code === $type) {
					$out[$i]['ip'] = $ip;
					$out[$i]['port'] = (empty($ports[$n]) === false) ? trim($ports[$n]) : null;
					$out[$i]['country'] = (empty($countries[$n]) === false) ? trim($countries[$n]) : null;
					$out[$i]['speed'] = (empty($speeds[$n]) === false) ? trim($speeds[$n]) : null;
					$out[$i]['type'] = (empty($type[$n]) === false) ? trim($type[$n]) : 'HTTP';
					$out[$i]['anonymous'] = (empty($anonymous[$n]) === false) ? synAnonymous(trim($anonymous[$n])) : null;
					$i++;
				}
				
			}

			return $out;
			
			break;

		case 'DE':
			
			$ips = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(1)',$url,null);
			$ports = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(2)',$url,null);
			$countries_code = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(3)',$url,null);
			$countries = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(4)',$url,null);
			$anonymous = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(5)',$url,null);

			if (empty($ips)) {
				error('List1: list of the ip is null');
				return;
			}
			$i = 0;
			foreach ($ips as $n => $ip) {
				$code = (empty($countries_code[$n]) === false) ? trim($countries_code[$n]) : null;
				if ($code === $type) {
					$out[$i]['ip'] = $ip;
					$out[$i]['port'] = (empty($ports[$n]) === false) ? trim($ports[$n]) : null;
					$out[$i]['country'] = (empty($countries[$n]) === false) ? trim($countries[$n]) : null;
					$out[$i]['speed'] = (empty($speeds[$n]) === false) ? trim($speeds[$n]) : null;
					$out[$i]['type'] = (empty($type[$n]) === false) ? trim($type[$n]) : 'HTTP';
					$out[$i]['anonymous'] = (empty($anonymous[$n]) === false) ? synAnonymous(trim($anonymous[$n])) : null;
					$i++;
				}
				
			}

			return $out;
			
			break;

		case 'HK':
			
			$ips = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(1)',$url,null);
			$ports = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(2)',$url,null);
			$countries_code = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(3)',$url,null);
			$countries = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(4)',$url,null);
			$anonymous = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(5)',$url,null);

			if (empty($ips)) {
				error('List1: list of the ip is null');
				return;
			}
			$i = 0;
			foreach ($ips as $n => $ip) {
				$code = (empty($countries_code[$n]) === false) ? trim($countries_code[$n]) : null;
				if ($code === $type) {
					$out[$i]['ip'] = $ip;
					$out[$i]['port'] = (empty($ports[$n]) === false) ? trim($ports[$n]) : null;
					$out[$i]['country'] = (empty($countries[$n]) === false) ? trim($countries[$n]) : null;
					$out[$i]['speed'] = (empty($speeds[$n]) === false) ? trim($speeds[$n]) : null;
					$out[$i]['type'] = (empty($type[$n]) === false) ? trim($type[$n]) : 'HTTP';
					$out[$i]['anonymous'] = (empty($anonymous[$n]) === false) ? synAnonymous(trim($anonymous[$n])) : null;
					$i++;
				}
				
			}

			return $out;
			
			break;

		case 'IN':
			
			$ips = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(1)',$url,null);
			$ports = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(2)',$url,null);
			$countries_code = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(3)',$url,null);
			$countries = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(4)',$url,null);
			$anonymous = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(5)',$url,null);

			if (empty($ips)) {
				error('List1: list of the ip is null');
				return;
			}
			$i = 0;
			foreach ($ips as $n => $ip) {
				$code = (empty($countries_code[$n]) === false) ? trim($countries_code[$n]) : null;
				if ($code === $type) {
					$out[$i]['ip'] = $ip;
					$out[$i]['port'] = (empty($ports[$n]) === false) ? trim($ports[$n]) : null;
					$out[$i]['country'] = (empty($countries[$n]) === false) ? trim($countries[$n]) : null;
					$out[$i]['speed'] = (empty($speeds[$n]) === false) ? trim($speeds[$n]) : null;
					$out[$i]['type'] = (empty($type[$n]) === false) ? trim($type[$n]) : 'HTTP';
					$out[$i]['anonymous'] = (empty($anonymous[$n]) === false) ? synAnonymous(trim($anonymous[$n])) : null;
					$i++;
				}
				
			}

			return $out;
			
			break;

		case 'ID':
			
			$ips = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(1)',$url,null);
			$ports = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(2)',$url,null);
			$countries_code = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(3)',$url,null);
			$countries = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(4)',$url,null);
			$anonymous = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(5)',$url,null);

			if (empty($ips)) {
				error('List1: list of the ip is null');
				return;
			}
			$i = 0;
			foreach ($ips as $n => $ip) {
				$code = (empty($countries_code[$n]) === false) ? trim($countries_code[$n]) : null;
				if ($code === $type) {
					$out[$i]['ip'] = $ip;
					$out[$i]['port'] = (empty($ports[$n]) === false) ? trim($ports[$n]) : null;
					$out[$i]['country'] = (empty($countries[$n]) === false) ? trim($countries[$n]) : null;
					$out[$i]['speed'] = (empty($speeds[$n]) === false) ? trim($speeds[$n]) : null;
					$out[$i]['type'] = (empty($type[$n]) === false) ? trim($type[$n]) : 'HTTP';
					$out[$i]['anonymous'] = (empty($anonymous[$n]) === false) ? synAnonymous(trim($anonymous[$n])) : null;
					$i++;
				}
				
			}

			return $out;
			
			break;

		case 'IR':
			
			$ips = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(1)',$url,null);
			$ports = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(2)',$url,null);
			$countries_code = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(3)',$url,null);
			$countries = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(4)',$url,null);
			$anonymous = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(5)',$url,null);

			if (empty($ips)) {
				error('List1: list of the ip is null');
				return;
			}
			$i = 0;
			foreach ($ips as $n => $ip) {
				$code = (empty($countries_code[$n]) === false) ? trim($countries_code[$n]) : null;
				if ($code === $type) {
					$out[$i]['ip'] = $ip;
					$out[$i]['port'] = (empty($ports[$n]) === false) ? trim($ports[$n]) : null;
					$out[$i]['country'] = (empty($countries[$n]) === false) ? trim($countries[$n]) : null;
					$out[$i]['speed'] = (empty($speeds[$n]) === false) ? trim($speeds[$n]) : null;
					$out[$i]['type'] = (empty($type[$n]) === false) ? trim($type[$n]) : 'HTTP';
					$out[$i]['anonymous'] = (empty($anonymous[$n]) === false) ? synAnonymous(trim($anonymous[$n])) : null;
					$i++;
				}
				
			}

			return $out;
			
			break;

		case 'IT':
			
			$ips = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(1)',$url,null);
			$ports = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(2)',$url,null);
			$countries_code = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(3)',$url,null);
			$countries = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(4)',$url,null);
			$anonymous = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(5)',$url,null);

			if (empty($ips)) {
				error('List1: list of the ip is null');
				return;
			}
			$i = 0;
			foreach ($ips as $n => $ip) {
				$code = (empty($countries_code[$n]) === false) ? trim($countries_code[$n]) : null;
				if ($code === $type) {
					$out[$i]['ip'] = $ip;
					$out[$i]['port'] = (empty($ports[$n]) === false) ? trim($ports[$n]) : null;
					$out[$i]['country'] = (empty($countries[$n]) === false) ? trim($countries[$n]) : null;
					$out[$i]['speed'] = (empty($speeds[$n]) === false) ? trim($speeds[$n]) : null;
					$out[$i]['type'] = (empty($type[$n]) === false) ? trim($type[$n]) : 'HTTP';
					$out[$i]['anonymous'] = (empty($anonymous[$n]) === false) ? synAnonymous(trim($anonymous[$n])) : null;
					$i++;
				}
				
			}

			return $out;
			
			break;

		case 'KR':
			
			$ips = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(1)',$url,null);
			$ports = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(2)',$url,null);
			$countries_code = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(3)',$url,null);
			$countries = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(4)',$url,null);
			$anonymous = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(5)',$url,null);

			if (empty($ips)) {
				error('List1: list of the ip is null');
				return;
			}
			$i = 0;
			foreach ($ips as $n => $ip) {
				$code = (empty($countries_code[$n]) === false) ? trim($countries_code[$n]) : null;
				if ($code === $type) {
					$out[$i]['ip'] = $ip;
					$out[$i]['port'] = (empty($ports[$n]) === false) ? trim($ports[$n]) : null;
					$out[$i]['country'] = (empty($countries[$n]) === false) ? trim($countries[$n]) : null;
					$out[$i]['speed'] = (empty($speeds[$n]) === false) ? trim($speeds[$n]) : null;
					$out[$i]['type'] = (empty($type[$n]) === false) ? trim($type[$n]) : 'HTTP';
					$out[$i]['anonymous'] = (empty($anonymous[$n]) === false) ? synAnonymous(trim($anonymous[$n])) : null;
					$i++;
				}
				
			}

			return $out;
			
			break;

		case 'MX':
			
			$ips = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(1)',$url,null);
			$ports = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(2)',$url,null);
			$countries_code = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(3)',$url,null);
			$countries = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(4)',$url,null);
			$anonymous = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(5)',$url,null);

			if (empty($ips)) {
				error('List1: list of the ip is null');
				return;
			}
			$i = 0;
			foreach ($ips as $n => $ip) {
				$code = (empty($countries_code[$n]) === false) ? trim($countries_code[$n]) : null;
				if ($code === $type) {
					$out[$i]['ip'] = $ip;
					$out[$i]['port'] = (empty($ports[$n]) === false) ? trim($ports[$n]) : null;
					$out[$i]['country'] = (empty($countries[$n]) === false) ? trim($countries[$n]) : null;
					$out[$i]['speed'] = (empty($speeds[$n]) === false) ? trim($speeds[$n]) : null;
					$out[$i]['type'] = (empty($type[$n]) === false) ? trim($type[$n]) : 'HTTP';
					$out[$i]['anonymous'] = (empty($anonymous[$n]) === false) ? synAnonymous(trim($anonymous[$n])) : null;
					$i++;
				}
				
			}

			return $out;
			
			break;

		case 'NL':
			
			$ips = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(1)',$url,null);
			$ports = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(2)',$url,null);
			$countries_code = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(3)',$url,null);
			$countries = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(4)',$url,null);
			$anonymous = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(5)',$url,null);

			if (empty($ips)) {
				error('List1: list of the ip is null');
				return;
			}
			$i = 0;
			foreach ($ips as $n => $ip) {
				$code = (empty($countries_code[$n]) === false) ? trim($countries_code[$n]) : null;
				if ($code === $type) {
					$out[$i]['ip'] = $ip;
					$out[$i]['port'] = (empty($ports[$n]) === false) ? trim($ports[$n]) : null;
					$out[$i]['country'] = (empty($countries[$n]) === false) ? trim($countries[$n]) : null;
					$out[$i]['speed'] = (empty($speeds[$n]) === false) ? trim($speeds[$n]) : null;
					$out[$i]['type'] = (empty($type[$n]) === false) ? trim($type[$n]) : 'HTTP';
					$out[$i]['anonymous'] = (empty($anonymous[$n]) === false) ? synAnonymous(trim($anonymous[$n])) : null;
					$i++;
				}
				
			}

			return $out;
			
			break;

		case 'SG':
			
			$ips = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(1)',$url,null);
			$ports = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(2)',$url,null);
			$countries_code = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(3)',$url,null);
			$countries = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(4)',$url,null);
			$anonymous = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(5)',$url,null);

			if (empty($ips)) {
				error('List1: list of the ip is null');
				return;
			}
			$i = 0;
			foreach ($ips as $n => $ip) {
				$code = (empty($countries_code[$n]) === false) ? trim($countries_code[$n]) : null;
				if ($code === $type) {
					$out[$i]['ip'] = $ip;
					$out[$i]['port'] = (empty($ports[$n]) === false) ? trim($ports[$n]) : null;
					$out[$i]['country'] = (empty($countries[$n]) === false) ? trim($countries[$n]) : null;
					$out[$i]['speed'] = (empty($speeds[$n]) === false) ? trim($speeds[$n]) : null;
					$out[$i]['type'] = (empty($type[$n]) === false) ? trim($type[$n]) : 'HTTP';
					$out[$i]['anonymous'] = (empty($anonymous[$n]) === false) ? synAnonymous(trim($anonymous[$n])) : null;
					$i++;
				}
				
			}

			return $out;
			
			break;

		case 'SE':
			
			$ips = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(1)',$url,null);
			$ports = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(2)',$url,null);
			$countries_code = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(3)',$url,null);
			$countries = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(4)',$url,null);
			$anonymous = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(5)',$url,null);

			if (empty($ips)) {
				error('List1: list of the ip is null');
				return;
			}
			$i = 0;
			foreach ($ips as $n => $ip) {
				$code = (empty($countries_code[$n]) === false) ? trim($countries_code[$n]) : null;
				if ($code === $type) {
					$out[$i]['ip'] = $ip;
					$out[$i]['port'] = (empty($ports[$n]) === false) ? trim($ports[$n]) : null;
					$out[$i]['country'] = (empty($countries[$n]) === false) ? trim($countries[$n]) : null;
					$out[$i]['speed'] = (empty($speeds[$n]) === false) ? trim($speeds[$n]) : null;
					$out[$i]['type'] = (empty($type[$n]) === false) ? trim($type[$n]) : 'HTTP';
					$out[$i]['anonymous'] = (empty($anonymous[$n]) === false) ? synAnonymous(trim($anonymous[$n])) : null;
					$i++;
				}
				
			}

			return $out;
			
			break;

		case 'TH':
			
			$ips = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(1)',$url,null);
			$ports = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(2)',$url,null);
			$countries_code = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(3)',$url,null);
			$countries = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(4)',$url,null);
			$anonymous = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(5)',$url,null);

			if (empty($ips)) {
				error('List1: list of the ip is null');
				return;
			}
			$i = 0;
			foreach ($ips as $n => $ip) {
				$code = (empty($countries_code[$n]) === false) ? trim($countries_code[$n]) : null;
				if ($code === $type) {
					$out[$i]['ip'] = $ip;
					$out[$i]['port'] = (empty($ports[$n]) === false) ? trim($ports[$n]) : null;
					$out[$i]['country'] = (empty($countries[$n]) === false) ? trim($countries[$n]) : null;
					$out[$i]['speed'] = (empty($speeds[$n]) === false) ? trim($speeds[$n]) : null;
					$out[$i]['type'] = (empty($type[$n]) === false) ? trim($type[$n]) : 'HTTP';
					$out[$i]['anonymous'] = (empty($anonymous[$n]) === false) ? synAnonymous(trim($anonymous[$n])) : null;
					$i++;
				}
				
			}

			return $out;
			
			break;

		case 'UA':
			
			$ips = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(1)',$url,null);
			$ports = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(2)',$url,null);
			$countries_code = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(3)',$url,null);
			$countries = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(4)',$url,null);
			$anonymous = $client->parseProperty($content,'string','table#proxylisttable tr td:nth-child(5)',$url,null);

			if (empty($ips)) {
				error('List1: list of the ip is null');
				return;
			}
			$i = 0;
			foreach ($ips as $n => $ip) {
				$code = (empty($countries_code[$n]) === false) ? trim($countries_code[$n]) : null;
				if ($code === $type) {
					$out[$i]['ip'] = $ip;
					$out[$i]['port'] = (empty($ports[$n]) === false) ? trim($ports[$n]) : null;
					$out[$i]['country'] = (empty($countries[$n]) === false) ? trim($countries[$n]) : null;
					$out[$i]['speed'] = (empty($speeds[$n]) === false) ? trim($speeds[$n]) : null;
					$out[$i]['type'] = (empty($type[$n]) === false) ? trim($type[$n]) : 'HTTP';
					$out[$i]['anonymous'] = (empty($anonymous[$n]) === false) ? synAnonymous(trim($anonymous[$n])) : null;
					$i++;
				}
				
			}

			return $out;
			
			break;

		default:
			# code...
			break;
	}
	

	if (empty($ips)) {
		error('List1: list of the ip is null');
		return;
	}
	$i = 0;
	foreach ($ips as $n => $ip) {
		$out[$i]['ip'] = $ip;
		$out[$i]['port'] = (empty($ports[$n]) === false) ? trim($ports[$n]) : null;
		$out[$i]['country'] = (empty($countries[$n]) === false) ? trim($countries[$n]) : null;
		$out[$i]['speed'] = (empty($speeds[$n]) === false) ? trim($speeds[$n]) : null;
		$out[$i]['type'] = (empty($type[$n]) === false) ? trim($type[$n]) : 'HTTP';
		$out[$i]['anonymous'] = (empty($anonymous[$n]) === false) ? synAnonymous(trim($anonymous[$n])) : null;
		$i++;
	}

	return $out;
}

function parseUrl($url)
{
	$out = [];
	$client = new CurlClient();
	return $client->parsePage($url);
}



function match($str,$pat, $flags = '', $start = null, $end = null)
{
	$regex = RegexUtil::create($pat, $flags, $start, $end);
	return RegexUtil::match($regex, $str, $offset = 0);
}

function matchAll($str,$pat,$flags = '', $start = null, $end = null)
{
	$regex = RegexUtil::create($pat, $flags, $start, $end);
	return RegexUtil::matchAll($regex, $str, $offset = 0);
}



function connectDb()
{
	require 'config/db.php';
	
	return new Mysql(
	    $config['host'],
	    $config['user'],
	    $config['password'],
	    $config['database']
	);
}

function insertTable($table,$data)
{
	return connectDb()->insertMany($table, $data);	
}

function synAnonymous($name)
{
	switch ($name) {
		case 'anonymous':
			return 'Medium';
		case 'Anonymous':
			return 'Medium';
		case 'elite proxy':
			return 'High';
		case 'transparent':
			return 'No';
	}
}

function maskAllProxe()
{
	return [
		'free proxy list',
		'proxy server list',
		'http proxy list',
		'proxy list txt',
		'proxy list pro',
		'proxy list https',
		'anonymous proxy list',
		'checked proxy lists',
		'proxy online list',
		'free proxy server list',
		'web proxy list',
		'proxy ip list',
		'free http proxy list',
		'free proxy list https',
		'open proxy list',
		'free proxy list txt',
		'public proxy list'
	];
}

function maskSocks5()
{
	return [
		'proxy list socks5',
		'socks5 proxy list free',
		'free socks 5',
		'socks 5 proxies',
		'socks v5 proxy',
		'free socks5',
		'proxy socks5 free',
		'socks5 free list',
		'socks5 proxy list free',
		'socks5 proxy',
		'socks5 list',
		'proxy list socks5',
		'socks5 http',
		'socks5 txt',
		'buy socks5 proxy',
		'socks5 elite',
		'socks5 server'
	];
}

function maskSocks4()
{
	return [
		'proxy list socks4',
		'socks4 proxy list free',
		'free socks 4',
		'socks 4 proxies',
		'socks v4 proxy',
		'free socks4',
		'proxy socks4 free',
		'socks4 free list',
		'socks4 proxy list free',
		'socks4 proxy',
		'socks4 list',
		'proxy list socks4',
		'socks4 http',
		'socks4 txt',
		'buy socks4 proxy',
		'socks4 elite',
		'socks4 server'
	];
}

function maskSocks45()
{
	return [
		'socks proxy list',
		'free socks proxy',
		'socks proxy list',
		'http socks proxy',
		'free socks proxy list',
		'buy socks proxy',
		'socks4 socks5',
		'socks4 5',
		'http socks4 socks5',
		'proxies socks4 5'
	];
}

function maskProxyCountry()
{
	return [
		'proxy list CODE',
		'proxy list free CODE',
		'CODE proxy list',
		'CODE proxy',
		'proxy free CODE',
		'CODE proxy servers',
		'proxy CODE web',
		'http proxy CODE',
		'CODE proxy online',
		'proxy online CODE',
		'CODE anonymous proxy',
		'CODE web proxy',
		'CODE free proxy https',
		'CODE https proxy list',
		'proxy CODE shared',
		'proxy CODE'
	];
}

function post($url,$user,$psw,$title,$content,$tags,$post_thumbnail = null){
    $url_post = $url.'/xmlrpc.php';
    $content_wp = array(
        'post_type' => 'post',
        'post_content' => $content,
        'post_title' => $title,
        'post_status' => 'publish',
        'post_thumbnail' => $post_thumbnail,
        'terms' => [
               'post_tag' => $tags,
        ],
    );
    $response = xmlRpc($url_post, $content_wp, $user, $psw);
    
    $errors = getResponseError($response);
    if (empty($errors) === false) {
    	error($errors);
    	return;
    }
    return getResponsePost($response);
}



function xmlRpc($url, $content, $user, $psw,$action = 'wp.newPost'){
        // initialize curl
        $ch = curl_init();
        // set url ie path to xmlrpc.php
        curl_setopt($ch, CURLOPT_URL, $url);
        // xmlrpc only supports post requests
        curl_setopt($ch, CURLOPT_POST, true);
        // return transfear
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // setup post data
        
        // parameters are blog_id, username, password and content
        $params = array(1, $user, $psw, $content);
        
        $params = xmlrpc_encode_request($action, $params,array('encoding' => 'utf-8', 'escaping'=>'markup'));
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        // execute the request
        $response = curl_exec($ch);
        // shutdown curl
        curl_close($ch);
        
        return $response;
}

function getResponseError($xml){
    $out = '';
        $obj = new SimpleXMLElement($xml);
        if ($obj->fault != NULL) {
            if ($obj->fault->value != NULL) {
                if ($obj->fault->value->struct != NULL) {
                    if ($obj->fault->value->struct->member != NULL) {
                        foreach ($obj->fault->value->struct->member as $member) {
                        	
                            if ($member->value->int != NULL) {
                                $out .= 'Code: '.$member->value->int.' ';
                            }
                            if ($member->value->string != NULL) {
                                $out .= 'Description: '.$member->value->string.' ';
                            }
                        }            
                    } 
                }
            }
        }
        return $out;
}

function getResponseLoadFile($xml){
	$reader = new Reader();
	$reader->xml($xml);
	$result = $reader->parse();
	if (empty($result['value'][0]['value'][0]['value'][0]['value'][0]['value']) === false) {
        foreach ($result['value'][0]['value'][0]['value'][0]['value'][0]['value'] as $member) {
        	if ($member['value'][0]['value'] === 'type' || $member['value'][0]['value'] === 'id' || $member['value'][0]['value'] === 'thumbnail' || $member['value'][0]['value'] === 'file') {
        		$out[$member['value'][0]['value']] = $member['value'][1]['value'][0]['value'];
        	}
        }
    }
    return $out;
}

function getResponsePost($xml){
	$out = null;
	$reader = new Reader();
	$reader->xml($xml);
	$result = $reader->parse();
	if (empty($result['value'][0]['value'][0]['value'][0]['value'][0]['value']) === false) {
        $out = $result['value'][0]['value'][0]['value'][0]['value'][0]['value'];
    }
    return $out;
}

function tags()
{
	return array(
	  array('term_id' => '2','name' => 'socks5','slug' => 'socks5','term_group' => '0'),
	  array('term_id' => '3','name' => 'socks4','slug' => 'socks4','term_group' => '0'),
	  array('term_id' => '4','name' => 'us','slug' => 'us','term_group' => '0'),
	  array('term_id' => '5','name' => 'uk','slug' => 'uk','term_group' => '0'),
	  array('term_id' => '6','name' => 'Russia','slug' => 'russia','term_group' => '0'),
	  array('term_id' => '7','name' => 'Argentina','slug' => 'argentina','term_group' => '0'),
	  array('term_id' => '8','name' => 'France','slug' => 'france','term_group' => '0'),
	  array('term_id' => '9','name' => 'Brazil','slug' => 'brazil','term_group' => '0'),
	  array('term_id' => '10','name' => 'Canada','slug' => 'canada','term_group' => '0'),
	  array('term_id' => '11','name' => 'China','slug' => 'china','term_group' => '0'),
	  array('term_id' => '12','name' => 'Colombia','slug' => 'colombia','term_group' => '0'),
	  array('term_id' => '13','name' => 'Czech Republic','slug' => 'czech-republic','term_group' => '0'),
	  array('term_id' => '14','name' => 'Germany','slug' => 'germany','term_group' => '0'),
	  array('term_id' => '15','name' => 'Hong Kong','slug' => 'hong-kong','term_group' => '0'),
	  array('term_id' => '16','name' => 'India','slug' => 'india','term_group' => '0'),
	  array('term_id' => '17','name' => 'Indonesia','slug' => 'indonesia','term_group' => '0'),
	  array('term_id' => '18','name' => 'Iran','slug' => 'iran','term_group' => '0'),
	  array('term_id' => '19','name' => 'Italy','slug' => 'italy','term_group' => '0'),
	  array('term_id' => '20','name' => 'Korea','slug' => 'korea','term_group' => '0'),
	  array('term_id' => '21','name' => 'Mexico','slug' => 'mexic','term_group' => '0'),
	  array('term_id' => '22','name' => 'Netherlands','slug' => 'netherlands','term_group' => '0'),
	  array('term_id' => '23','name' => 'Singapore','slug' => 'singapore','term_group' => '0'),
	  array('term_id' => '24','name' => 'free','slug' => 'free','term_group' => '0'),
	  array('term_id' => '25','name' => 'anonymous','slug' => 'anonymous','term_group' => '0')
	);
}

function getIdTag($name)
{
	$key = array_search($name, array_column(tags(), 'name'));
	if (empty($key) === false) {
		return (empty(tags()[$key]['term_id']) === false) ? tags()[$key]['term_id'] : null;
	}
}

function findTags($title)
{
	$out = [];
	$words = explode(' ', $title);
	if (empty($words) === false) {
		foreach ($words as $word) {
			$id = getIdTag($word);
			if (empty($id) === false) {
				$out[] = $id;
			}
		}
	}
	return $out;
}

function error($string)
{
	echo "\033[31m".$string."\033[0m".PHP_EOL;
}

function success($string)
{
	echo "\033[32m".$string."\033[0m".PHP_EOL;
}

function info($string)
{
	echo "\033[33m".$string."\033[0m".PHP_EOL;
}
?>