<?php
require 'vendor/autoload.php';

use Sabre\Xml\Reader;
use Simplon\Mysql\Mysql;

	$wp_data = explode('|', file('wp_login.txt')[2]);

	$url = rtrim($wp_data[0]);
	$user = rtrim($wp_data[1]);
	$psw = rtrim($wp_data[2]);

    if (posted()) {
        info('The curency rates is posted');
        return;
    }

	$rates = loadCurrencyRates('USD', date("Y-m-d"));

    $contents = createContent($rates);
    
    if (empty($contents)) {
        error('contents are null');
    }

    foreach ($contents as $content) {
        if(empty($content) === false) {
            //var_dump($content);
            $post = post($url,$user,$psw,$content['title'],$content['description'],$content['tags']);
            error($post);
            
        }else{
            error('The content is null');
        }
        die;
    }
    
	
	

function loadCurrencyRates($base, $date){
        $our = [];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://api.fixer.io/latest?base=".$base."&date=".$date);
        //curl_setopt($ch, CURLOPT_URL, "http://api.fixer.io/".$date);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        $response = curl_exec($ch);
        curl_close($ch);
        $out = json_decode($response);
        return $out;
}

function posted()
{
    return false;
}

function createContent($rates)
{   
    $today = '2016-12-02'; //date("Y-m-d");
    $masks = file('files/currency_mask.txt');
    $content = [];
    var_dump($rates->date);
    
    if (date("Y-m-d",strtotime($rates->date)) === $today) {
        $list_currency = listCurrency();
        $arrayRates = arrayRates($list_currency,$rates->rates);
        $tags = tags();
        foreach ($list_currency as $codeFrom => $currency) {
            $codeTo = randCode($codeFrom);

            
            $mask = trim($masks[rand(0,count($masks)-1)]);
            if (strpos($mask,'cur1') < strpos($mask,'cur2')) {
                $cur1 = $codeFrom;
                $cur2 = $codeTo;
                $key = str_replace('cur1', $cur1, $mask);
                $key = str_replace('cur2', $cur2, $key);
            }else{
                $cur1 = $codeTo;
                $cur2 = $codeFrom;
                $key = str_replace('cur1', $cur2, $mask);
                $key = str_replace('cur2', $cur1, $key);
            }
            

            $rateC = calculateRate($cur1,$cur2,$rates->rates);

            //info($cur1.' - '.$cur2.' : '.$rateC.' - '.$key);
            preg_match("|\d+|", $key, $m);
            $amount = (empty($m[0]) === false) ? $m[0] : 1;

            $h3 = str_replace($cur1, $list_currency[$cur1], $key);
            $h3 = str_replace($cur2, $list_currency[$cur2], $h3);
            
            $content[$codeFrom]['title'] = $key.' on '.date("M d, Y");
            $content[$codeFrom]['description'] = '<h3>';  
            $content[$codeFrom]['description'] .= $h3.' on '.date("M d, Y");  
            $content[$codeFrom]['description'] .= '</h3>';  
            $content[$codeFrom]['description'] .= '<div class = "row rate jumbotron">';
            $content[$codeFrom]['description'] .= '<div class = "col-sm-4">';            
            $content[$codeFrom]['description'] .= '<select class="form-control" name = "code-from">'; 
            foreach ($list_currency as $c_code => $c_title) {
                $selected = ($c_code === $cur1) ? 'selected' : '';
                $content[$codeFrom]['description'] .= '<option value = "'.$c_code.'" '.$selected.' >'.$c_title.'</option>';            
            }           
            $content[$codeFrom]['description'] .= '</select>';            
            $content[$codeFrom]['description'] .= '</div>';            
            $content[$codeFrom]['description'] .= '<div class = "col-sm-2" name = "amount">';            
            $content[$codeFrom]['description'] .= '<input type="text" class="form-control" value = "'.$amount.'">';   
            $content[$codeFrom]['description'] .= '</div>';            
            $content[$codeFrom]['description'] .= '<div class = "col-sm-2" name = "value">';            
            $content[$codeFrom]['description'] .= '<span class="label label-primary">'.number_format($rateC*$amount,2).'</span>'; 
            $content[$codeFrom]['description'] .= '</div>';            
            $content[$codeFrom]['description'] .= '<div class = "col-sm-4">';            
            $content[$codeFrom]['description'] .= '<select class="form-control" name = "code-to">';            
            foreach ($list_currency as $c_code => $c_title) {
                $selected = ($c_code === $cur2) ? 'selected' : '';
                $content[$codeFrom]['description'] .= '<option value = "'.$c_code.'" '.$selected.' >'.$c_title.'</option>';            
            }           
            $content[$codeFrom]['description'] .= '</select>'; 
            $content[$codeFrom]['description'] .= '</div>';            
                       
            $content[$codeFrom]['description'] .= '<script type="text/javascript">'; 
            $content[$codeFrom]['description'] .= 'var rates= '.json_encode($arrayRates); 
            $content[$codeFrom]['description'] .= '</script>'; 
                 

            $content[$codeFrom]['description'] .= '</div>';
            $content[$codeFrom]['tags'] = [$tags[$cur1],$tags[$cur2]];
        }
        return $content;
        
    }else{
        error('There are not data of currency rates for todat');
        return;
    }
    
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

function connectDb()
{
	require 'config/db2.php';
	
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

function updateTable($table, $condr, $data)
{
	connectDb()->update($table, $condr, $data);	
}

function listCurrency()
{
    return [
        'USD' => 'United States Dollar',
        'AUD' => 'Australia Dollar',
        'BGN' => 'Bulgaria Lev',
        'BRL' => 'Brazil Real',
        'CAD' => 'Canada Dollar',
        'CHF' => 'Switzerland Franc',
        'CNY' => 'China Yuan Renminbi',
        'CZK' => 'Czech Republic Koruna',
        'DKK' => 'Denmark Krone',
        'GBP' => 'United Kingdom Pound',
        'HKD' => 'Hong Kong Dollar',
        'HRK' => 'Croatia Kuna',
        'HUF' => 'Hungary Forint',
        'IDR' => 'Indonesia Rupiah',
        'ILS' => 'Israel Shekel',
        'INR' => 'India Rupee',
        'JPY' => 'Japan Yen',
        'KRW' => 'Korea (South) Won',
        'MXN' => 'Mexico Peso',
        'MYR' => 'Malaysia Ringgit',
        'NOK' => 'Norway Krone',
        'NZD' => 'New Zealand Dollar',
        'PHP' => 'Philippines Peso',        
        'PLN' => 'Poland Zloty',
        'RON' => 'Romania New Leu',
        'RUB' => 'Russia Ruble',
        'SEK' => 'Sweden Krona',
        'SGD' => 'Singapore Dollar',    
        'THB' => 'Thailand Baht',
        'TRY' => 'Turkey Lira',
        'ZAR' => 'South Africa Rand',
        'EUR' => 'Euro',
        

    ];
}

function tags()
{
    return [
        'USD' => '2',
        'AUD' => '3',
        'BGN' => '4',
        'BRL' => '5',
        'CAD' => '6',
        'CHF' => '7',
        'CNY' => '8',
        'CZK' => '9',
        'DKK' => '10',
        'GBP' => '11',
        'HKD' => '12',
        'HRK' => '13',
        'HUF' => '14',
        'IDR' => '15',
        'ILS' => '16',
        'INR' => '17',
        'JPY' => '18',
        'KRW' => '19',
        'MXN' => '20',
        'MYR' => '21',
        'NOK' => '22',
        'NZD' => '23',
        'PHP' => '24',        
        'PLN' => '25',
        'RON' => '26',
        'RUB' => '27',
        'SEK' => '28',
        'SGD' => '29',    
        'THB' => '30',
        'TRY' => '31',
        'ZAR' => '32',
        'EUR' => '33',
        

    ];
}

function randCode($code)
{
    $out = $code;
    while ($out == $code) {
        $list = listCurrency();
        $keys = array_keys($list);
        $out = $keys[rand(0,count($keys)-1)];

    }
    return $out;
}

function calculateRate($codeFrom,$codeTo,$rates)
{
    if ($codeFrom === 'USD') {
        return $rates->$codeTo;
    }else{
        if ($codeTo === 'USD') {
            return 1/$rates->$codeFrom;
        }else{
            $rate_from = $rates->$codeFrom;
            $rate_to = $rates->$codeTo;
            return $rate_to/$rate_from;    
        }
        
    }
    
}

function arrayRates($codes,$rates)
{
    $out = [];
    foreach ($codes as $code => $title) {
        if ($code !== 'USD') {
            $out[$code] = $rates->$code;
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