<?php
function get($url, $opts = array())
{
	$c = curl_init($url);
	curl_setopt_array($c, $opts);
	$r = curl_exec($c);
	curl_close($c);
	return $r;
}
$url = null;
$iscli = php_sapi_name() == 'cli';
$newline = $iscli ? "\n" : '<br>';
if($iscli)
{
	if($argc !== 2)
	{
		echo 'Usage: php ' . $argv[0] . ' <url>';
		exit;
	}
	$url = $argv[1];
}
if(!empty($_POST['url']))
{
	$url = $_POST['url'];
}
if($url)
{
	try
	{
		$cookie = tempnam('/tmp', 'quack_');
		$opts = array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_FOLLOWLOCATION => 1,
			CURLOPT_COOKIEJAR => $cookie,
			CURLOPT_COOKIEFILE => $cookie,
		);
		if(!($result = get($url, $opts)) || !preg_match('#http://duckcrypt\.info/ajax/auth\.php\?hash=[a-f0-9]+#', $result, $url))
		{
			throw new Exception('Hash could not be detected, bad response.');
		}
		if(!($result = get($url[0], $opts)) || !preg_match('#http://duckcrypt\.info/folder/[a-f0-9]+/[a-f0-9]+#', $result, $url))
		{
			throw new Exception('Folder could not be detected, bad response.');
		}
		if(!($result = get($url[0], $opts)) || !preg_match_all('#http://duckcrypt\.info/link/[a-f0-9]+#', $result, $url))
		{
			throw new Exception('Links could not be detected, bad response.');
		}
		if(!$iscli)
		{
			echo '<!DOCTYPE html><html><head><title>QuackQuack Error</title></head><body>';
		}
		foreach($url[0] as $link)
		{
			$result = get($link, $opts);
			if(!preg_match('#src="([&\#x0-9a-f;]+)"#', $result, $true))
			{
				echo $link . " skipped, could not decrypt." . $newline;
				continue;
			}
			echo html_entity_decode($true[1]) . $newline;
		}
		if(!$iscli)
		{
			echo '</body></html>';
		}
		unlink($cookie);
	}
	catch(Exception $e)
	{
		unlink($cookie);
		if(!$iscli)
		{
			echo '<!DOCTYPE html><html><head><title>QuackQuack Error</title></head><body>';
		}
		echo $e->getMessage();
		if(!$iscli)
		{
			echo '</body></html>';
		}
		exit;
	}
	exit;
}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>DuckCrypt Decrypter</title>
	</head>
	<body>
		<h1>DuckCrypt Decrypter</h1>
		<p>
			Decrypts DuckCrypt links just because it can. Totally awesome encryption.
			Can also be used on command line, &quot;php quackquack.php [URL]&quot;.
		</p>
		<form action="" method="post">
			URL: <input type="text" name="url" placeholder="http://duckcrypt.com/...">
			<input type="submit" value="Decrypt">
		</form>
	</body>
</html>
