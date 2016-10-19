<?php
require 'vendor/autoload.php';

//use SimpleXMLElement;
use Simplon\Mysql\Mysql;
use Sabre\Xml\Reader;

for (;;){
	$wp_data = explode('|', file('wp_login.txt')[1]);

	$url = rtrim($wp_data[0]);
	$user = rtrim($wp_data[1]);
	$psw = rtrim($wp_data[2]);

	$content = contentDatabase2();
	base64_to_jpeg($content['base64'],__DIR__.'/runtime/pic.jpg');
	//var_dump($content);
	$media = loadMedia($url,$user,$psw,$content,__DIR__.'/runtime/pic.jpg');
	$post = post($url,$user,$psw,$content['title'],$content['description'],[],$media['id']);
	var_dump($post);
	/*while ($content !== null) {
		var_dump($content);
		$post = post($url,$user,$psw,$content['title'],$content['description'],[]);
		error($post);
		$content = contentDatabase();
	}*/
	die;
	info('Sleep 15 min');
	sleep(900);
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

function loadMedia($url,$user,$psw,$content,$path_file)
{
	$url_post = $url.'/xmlrpc.php';
	$file = file_get_contents($path_file);
	xmlrpc_set_type($file,'base64');
    $content_wp = array(
        'name' => 'name.jpg',
        'type' => 'image/jpeg',
        'bits' => $file,
    );
    $response = xmlRpc($url_post, $content_wp, $user, $psw, 'wp.uploadFile');
    $errors = getResponseError($response);
    if (empty($errors) === false) {
    	error($errors);
    	return;
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
    $ifp = fopen($output_file, "wb"); 
    $data = explode(',', $base64_string);
    fwrite($ifp, base64_decode($data[1])); 
    fclose($ifp); 
    return $output_file; 
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