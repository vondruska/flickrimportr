<?php
$ts = md5(filemtime($_SERVER['DOCUMENT_ROOT'] . "/webroot/js/javascript.js"));
print "<script src=\"http://".$_SERVER['HTTP_HOST']."/js/javascript.js?v=$ts\"></script>";

$ts = md5(filemtime($_SERVER['DOCUMENT_ROOT'] . "/webroot/css/style.css"));
print "<link href=\"http://".$_SERVER['HTTP_HOST']."/css/style.css?v=$ts\" type=\"text/css\" rel=\"stylesheet\" />";
?>
<script type="text/javascript">
	var hostName = "<?=$_SERVER['HTTP_HOST'];?>";
	var appPath = "<?=$GLOBALS['appPath'];?>";
</script>

<fb:header><?=$GLOBALS['appName'];?></fb:header>
<fb:tabs>
	<fb:tab-item href="/<?=$GLOBALS['appPath'];?>/" title="Home" <?=($controller == 'home' && $action == 'index') ? 'selected="true"' : '' ?>/>
	<fb:tab-item href="/<?=$GLOBALS['appPath'];?>/view/photosets/" title="Photosets" <?=($controller == 'view' && $action == 'photosets') ? 'selected="true"' : '' ?> />
	<fb:tab-item href="/<?=$GLOBALS['appPath'];?>/view/photos/" title="Photos" <?=($controller == 'view' && $action == 'photos') ? 'selected="true"' : '' ?> />
	<fb:tab-item href="/<?=$GLOBALS['appPath'];?>/import/build" title="Queue (<?=(isset($_SESSION['photos'])) ? count($_SESSION['photos']) : '0';?>)" align="left" <?=($controller == 'import') ? 'selected="true"' : '' ?> />
	<fb:tab-item href="/<?=$GLOBALS['appPath'];?>/invite/" title="Invite" align="right" <?=($controller == 'home' && $action == 'invite') ? 'selected="true"' : '' ?> />
	<fb:tab-item href="/<?=$GLOBALS['appPath'];?>/options/" title="Options" align="right" <?=($controller == 'home' && $action == 'options') ? 'selected="true"' : '' ?>/>
	<fb:tab-item href="/<?=$GLOBALS['appPath'];?>/jobs/" title="Jobs" align="right" <?=($controller == 'jobs') ? 'selected="true"' : '' ?>/>
</fb:tabs>
<div style="margin-bottom: 10px;"></div>

<fb:explanation>
<fb:message>FlickrImportr's Future</fb:message>
The lone developer of FlickrImportr cannot dedicate the time needed to continue development of the application. 
Please <a href="https://www.facebook.com/flickrimportr/posts/180095088758335">read the Facebook Page post</a> about the possible future of the app.
</fb:explanation>
<div style="margin-bottom: 10px;"></div>
<? if(isset($errorMessage)): ?>
<fb:error message="<?=$errorMessage;?>" />
<? else: ?>
<?=$content_for_layout;?>
<? endif; ?>

<p style="font-size:10px;text-align:center;">
Thanks to Anonymous, <a href="http://algorithm.com.au/" target="_blank">Andre Pang</a>, <a href="http://www.bretkuhns.com" target="_blank">Bret Kuhns</a>, <a href="http://www.coreyblaz.com" target="_blank">Corey Blaz</a>, <a href="http://www.colombiabirding.com" target="_blank">Diego Calderon (COLOMBIA Birding)</a>, Mark Sheppard and  Paul Albertella for supporting FlickrImportr.<br /><br />
<span style="font-style:italic;">This product uses the Flickr API but is not endorsed or certified by Flickr.</span>
</p>
<? if(Configure::read('debug') == 0): ?>
<fb:google-analytics uacct="UA-9583159-1" />
<? endif; ?>

<fb:local-proxy />
