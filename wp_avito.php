<?php
require 'vendor/autoload.php';

//use SimpleXMLElement;
use Simplon\Mysql\Mysql;
use Sabre\Xml\Reader;

$path_avito_runtime_file = '../parser-avito/runtime/';


for (;;){
	$wp_data = explode('|', file('wp_login.txt')[3]);

	$url = rtrim($wp_data[0]);
	$user = rtrim($wp_data[1]);
	$psw = rtrim($wp_data[2]);

	$content = contentForPost($path_avito_runtime_file);
	if (empty($content) === false) {
		//base64_to_jpeg($content['base64'],__DIR__.'/runtime/pic.jpg');
		$file_pic = $path_avito_runtime_file.$content['item_id'].'.jpg';
		info('Post item ID: '.$content['item_id']);
		if (file_exists($file_pic)) {
			if (filesize($file_pic) > 20000) {
				$media = loadMedia($url,$user,$psw,$content,$file_pic);
				if (empty($media['id']) === false) {
					$category = (empty($content['category']) === false) ? getCategory($content['category'],$url,$user,$psw) : [];
					$post = post($url,$user,$psw,$content['title'],$content['description'],[],$category,$media['id']);
					var_dump($post);
				}else{
					error('Pic is not loaded');
				}
				
			}else{
				error('Pic is small');
			}
		}else{
			error('Pic is not exists');
		}
	}
	$rand_sleep = rand(10,15);
	info('Sleep '.($rand_sleep).' sec');
	sleep($rand_sleep);
}

function contentForPost($path)
{
	$out = '';
	$content =  contentDatabase2();
	if (empty($content)) {
		return;
	}
	if (filteN(10)) {
		info('Skippting');
		if (file_exists($path.$content['item_id'].'.jpg')) {
			unlink($path.$content['item_id'].'.jpg');
		}
		return;
	}else{
		info('Posting');
	}

	$out['item_id'] = $content['item_id'];
	$out['title'] = createTitle($content['title'],$content['price'],$content['address']);
	//(empty($content['address']) === false) ? $content['title'].'. '.$content['address'] : $content['title'];
	$out['category'] = $content['category'];
	$header = '<table class = "table datapost"><tbody>';
	$name = '<tr><td class = "title">Имя</td><td>'.$content['saler_name'].'</td></tr>';
	$price = (empty($content['price']) === false) ? '<tr><td class = "title">Цена</td><td>'.$content['price'].'</td></tr>' : '';
	$phone = (empty($content['saler_phone']) === false) ? '<tr><td class = "title">Телефон</td><td><img src="'.$content['saler_phone'].'"></td></tr>' : '';
	$city = (empty($content['city']) === false) ? '<tr><td class = "title">Город</td><td>'.$content['city'].'</td></tr>' : '';
	$metro = (empty($content['metro']) === false) ? '<tr><td class = "title">Метро</td><td>'.$content['metro'].'</td></tr>' : '';
	$address = (empty($content['address']) === false) ? '<tr><td class = "title">Адрес</td><td>'.$content['address'].'</td></tr>' : '';
	
	
	$description = '<tr><td colspan = "2">'.$content['description'].'</td></tr>';
	$footer = '</tbody></table>';
	if (empty($content["lat"])|| empty($content["lon"])) {
		$script_js = '';
		$map = '';
	}else{
		$map = '<tr><td colspan = "2"><div id="map" style="width: 600px; height: 400px"></div></td></tr>';
		$script_js = '<script type="text/javascript">
		ymaps.ready(init);
	    var myMap,myPlacemark;
	    function init(){     
	        myMap = new ymaps.Map("map", {
	            center: ['.$content["lat"].', '.$content["lon"].'], 
	            zoom: 17
	        });
	        myPlacemark = new ymaps.Placemark(['.$content["lat"].', '.$content["lon"].'], { hintContent: "'.$out['title'].'", balloonContent: "'.$content['address'].'" });
	        myMap.geoObjects.add(myPlacemark);
	    }</script>';
	}
	
	$out['description'] = $script_js.$header.$price.$name.$phone.$city.$metro.$address.$map.$description.$footer;
	return $out;
}

function getCategory($cat)
{
	$out = [];
	require 'config/wp_category.php';
	if (empty($category)) {
		return;
	}
	foreach ($category as $key => $item) {
		if ($item === $cat) {
			$out[] = $key;
		}
	}
	return $out;
}

function createTitle($title,$price,$address)
{
	if (fiterTitle($title) > 0) {
		return $title.' '.$address;
	}
	$price = str_replace('месяц','месяц',$price,$m);
	$price = str_replace('сутки','сутки',$price,$s);
	
	if ($m > 0) {
		return  mb_ucfirst(str_replace('TEMP',mb_strtolower($title, 'UTF-8'),randonTitle1()).'. '.$address);
	}
	if ($s > 0) {
		return  mb_ucfirst(str_replace('TEMP',mb_strtolower($title, 'UTF-8'),randonTitle2()).'. '.$address);
	}

	return  mb_ucfirst(str_replace('TEMP',mb_strtolower($title, 'UTF-8'),randonTitle3()).'. '.$address);
}

function mb_ucfirst($text) {
    return mb_strtoupper(mb_substr($text, 0, 1)) . mb_substr($text, 1);
}

function fiterTitle($title)
{
	$title = str_replace(['Сниму','Сдаются','Куплю'],'',$title,$out);
	return $out;
}

function randonTitle1()
{
	$data = [
		'аренда TEMP без посредников',
		'длительная аренда TEMP',
		'аренда TEMP на длительный срок',
		'аренда длительная TEMP без посредников',
		'аренда TEMP на длительный срок без посредников',
		'аренда TEMP от хозяина',
		'аренда TEMP от собственника',
		'долгосрочная аренда TEMP',
		'сдача TEMP в аренду',
		'недвижимость аренда TEMP',
		'аренда TEMP без посредников от хозяина',
		'аренда TEMP от хозяина на длительный срок',
		'аренда TEMP недорого',
		'снять TEMP в аренду',
		'долгосрочная аренда TEMP без посредников',
		'аренда TEMP без агентства',
		'недвижимость без посредников аренда TEMP',
		'частное аренда TEMP',
		'свежие TEMP аренда',
		'нужна TEMP аренду',
		'аренда TEMP помесячно',
		'долгосрочная аренда TEMP хозяина',
		'аренда TEMP недорого без посредников',
		'аренда TEMP дешево',
		'сдача TEMP в аренду от собственника',
		'снять TEMP без посредников',
		'снять TEMP на длительный срок',
		'снять TEMP от хозяина',
		'снять TEMP недорого',
		'TEMP снять от посредника',
		'снять TEMP без посредников от хозяина',
		'сниму TEMP без посредников на длительный',
		'снять TEMP без посредников на длительный срок',
		'снять TEMP недорого без посредников',
		'снять TEMP от хозяина недорого',
		'снять TEMP без посредников от хозяина недорого',
		'сниму TEMP длительно от хозяина',
		'сниму TEMP на длительный срок от хозяина',
		'недвижимость снять TEMP',
		'снять TEMP недорого на длительный срок',
		'снять TEMP на месяц',

		];
	return $data[rand(0,count($data)-1)];
}

function randonTitle2()
{
	$data = [
		'посуточная аренда TEMP',
		'аренда TEMP на сутки',
		'посуточная аренда TEMP без посредников',
		'снять TEMP посуточно',
		'снять TEMP посуточно без посредников',
		'снять TEMP посуточно недорого',



		
		];
	return $data[rand(0,count($data)-1)];
}

function randonTitle3()
{
	$data = [
		'продам TEMP',
		'продать TEMP',
		'продать TEMP без посредников',
		'купить TEMP',

		
		];
	return $data[rand(0,count($data)-1)];
}

function post($url,$user,$psw,$title,$content,$tags,$category,$post_thumbnail = null){
    $url_post = $url.'/xmlrpc.php';
    $content_wp = array(
        'post_type' => 'post',
        'post_content' => $content,
        'post_title' => $title,
        'post_status' => 'publish',
        'post_thumbnail' => $post_thumbnail,
        'terms' => [
               'post_tag' => $tags,
               'category' => $category,
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

function loadMedia($url,$user,$psw,$content,$path_file)
{
	$url_post = $url.'/xmlrpc.php';
	$file = file_get_contents($path_file);
	xmlrpc_set_type($file,'base64');
    $content_wp = array(
        'name' => 'real.jpg',
        'type' => 'image/jpeg',
        'bits' => $file,
        'overwrite' => true,
    );
    $response = xmlRpc($url_post, $content_wp, $user, $psw, 'wp.uploadFile');
    $errors = getResponseError($response);
    if (empty($errors) === false) {
    	error($errors);
    	return;
    }
    if (file_exists($path_file)) {
    	unlink($path_file);
    }
    return getResponseLoadFile($response);
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

function getTerms($url, $user, $psw,$taxonomy)
    {
    	$url_post = $url.'/xmlrpc.php';
        // initialize curl
        $ch = curl_init();
        // set url ie path to xmlrpc.php
        curl_setopt($ch, CURLOPT_URL, $url_post);
        // xmlrpc only supports post requests
        curl_setopt($ch, CURLOPT_POST, true);
        // return transfear
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // setup post data
       
        // parameters are blog_id, username, password and content
        $params = array(1, $user, $psw, $taxonomy);
        $params = xmlrpc_encode_request('wp.getTerms', $params);
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
	require 'config/db.php';
	
	return new Mysql(
	    $config['host'],
	    $config['user'],
	    $config['password'],
	    $config['database']
	);
}

function contentDatabase()
{
	$post = connectDb()->fetchRow('SELECT * FROM post WHERE status = \'parsed\''); 
	if (empty($post)) {
		info('There are not post for posting');
		return;
	}
	if (empty($post['id']) || empty($post['title'])) {
		error('Post id or title is null');
		return;
	}
	$image = connectDb()->fetchRow('SELECT * FROM image WHERE post_id = :id',[
		':id' => $post['id']
		]); 
	$image_code = (empty($image['url']) === false) ? '<p><img src = "'.$image['url'].'" alt = "'.$post['title'].'"></p>' : null;

	changeStatus($post['id']);

	return [
		'id' => $post['id'],
		'title' => $post['title'],
		'description' => '<p>'.$post['text'].'</p>'.$image_code,
		
	];
}

function contentDatabase2()
{
	$post = connectDb()->fetchRow('SELECT * FROM post WHERE status = \'parsed\''); 
	if (empty($post)) {
		info('There are not post for posting');
		return;
	}
	
	changeStatus($post['id']);

	return $post;
}

function changeStatus($id)
{
	$condr = [
		'id' => $id,		
	];
	$data = [
		'status' => 'posted',
	];
	updateTable('post', $condr, $data);
}

function insertTable($table,$data)
{
	return connectDb()->insertMany($table, $data);	
}

function updateTable($table, $condr, $data)
{
	connectDb()->update($table, $condr, $data);	
}

function base64_to_jpeg($base64_string, $output_file) {
	if (file_exists($output_file)) {
		unlink($output_file);
	}
    $ifp = fopen($output_file, "wb"); 
    $data = explode(',', $base64_string);
    fwrite($ifp, base64_decode($data[1])); 
    fclose($ifp); 
    return $output_file; 
}

function filteN($n)
{
	if (rand(1,$n) === 1) {
		return false;
	}else{
		return true;
	}
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