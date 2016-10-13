<?php
require 'vendor/autoload.php';

//use SimpleXMLElement;
use Simplon\Mysql\Mysql;

for (;;){
	$wp_data = explode('|', file('wp_login.txt')[0]);

	$url = rtrim($wp_data[0]);
	$user = rtrim($wp_data[1]);
	$psw = rtrim($wp_data[2]);

	$content = contentDatabase();
	while ($content !== null) {
		var_dump($content);
		$post = post($url,$user,$psw,$content['title'],$content['description'],[]);
		error($post);
		$content = contentDatabase();
	}
	info('Sleep 15 min');
	sleep(900);
}


function post($url,$user,$psw,$title,$content,$tags){
    $url_post = $url.'/xmlrpc.php';
    $content_wp = array(
        'post_type' => 'post',
        'post_content' => $content,
        'post_title' => $title,
        'post_status' => 'publish',
        'terms' => [
               'post_tag' => $tags,
        ],
    );
    return getResponseError(xmlRpc($url_post, $content_wp, $user, $psw));
}

function xmlRpc($url, $content, $user, $psw){
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
        
        $params = xmlrpc_encode_request('wp.newPost', $params,array('encoding' => 'utf-8', 'escaping'=>'markup'));
        
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