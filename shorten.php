<?php
/*
 * First authored by Brian Cray
 * License: http://creativecommons.org/licenses/by/3.0/
 * Contact the author at http://briancray.com/
 * Modify by Artem Konyakov
 * Contact the author at https://konyakov.ru and konyakov@yandex.ru
 */
 
ini_set('display_errors', 0);

$url_to_shorten = get_magic_quotes_gpc() ? stripslashes(trim($_REQUEST['longurl'])) : trim($_REQUEST['longurl']);

if(!empty($url_to_shorten) && preg_match('|^https?://|', $url_to_shorten))
{
	require('config.php');

	// check if the client IP is allowed to shorten
	if($_SERVER['REMOTE_ADDR'] != LIMIT_TO_IP)
	{
		die('You are not allowed to shorten URLs with this service.');
	}
	
	// check if the URL is valid
	if(CHECK_URL)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url_to_shorten);
		curl_setopt($ch,  CURLOPT_RETURNTRANSFER, TRUE);
		$response = curl_exec($ch);
		$response_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if($response_status == '404')
		{
			die('Not a valid URL');
		}
		
	}
	
	// check if the URL has already been shortened
	$already_shortened = mysqli_result(mysqli_query($link, 'SELECT id FROM ' . DB_TABLE. ' WHERE long_url="' . mysqli_real_escape_string($link, $url_to_shorten) . '"'), 0, 0);
	if(!empty($already_shortened))
	{
		// URL has already been shortened
		$shortened_url = getShortenedURLFromID($already_shortened);
	}
	else
	{
		// URL not in database, insert
		mysqli_query($link, 'LOCK TABLES ' . DB_TABLE . ' WRITE;');
		mysqli_query($link, 'INSERT INTO ' . DB_TABLE . ' (long_url, created, creator) VALUES ("' . mysqli_real_escape_string($link, $url_to_shorten) . '", "' . time() . '", "' . mysqli_real_escape_string($link, $_SERVER['REMOTE_ADDR']) . '")');
		$shortened_url = getShortenedURLFromID(mysqli_insert_id($link));
		mysqli_query($link, 'UNLOCK TABLES');
	}
	echo BASE_HREF . $shortened_url;
}

function getShortenedURLFromID ($integer, $base = ALLOWED_CHARS)
{
	$length = strlen($base);
	while($integer > $length - 1)
	{
		$out = $base[fmod($integer, $length)] . $out;
		$integer = floor( $integer / $length );
	}
	return $base[$integer] . $out;
}
