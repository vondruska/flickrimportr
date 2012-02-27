<div class="home">
<? if(!$auth): ?>
<script>
function auth() {
document.setLocation('<?=$auth_url;?>');
}
</script>

<div onmouseover="general.rollover(this, 'over');" onmouseout="general.rollover(this, 'out');" onclick="auth(); return false;" style="cursor: pointer;">
	<a style="background: url('http://<?=$_SERVER['HTTP_HOST'];?>/img/lock.png') no-repeat transparent;" href="<?=$auth_url;?>" onclick="auth(); return false;">Authorize</a><br />
	<blockquote>Before you can use <?=$GLOBALS['appName']; ?> you need to give <?=$GLOBALS['appName']; ?> access to your flickr account so we can access your photos.</blockquote>
</div>
<? else: ?>
<div class="half" onmouseover="general.rollover(this, 'over');" onmouseout="general.rollover(this, 'out');" style="cursor: pointer;" onclick="general.go('view/photosets');">
	<a style="background: url('http://<?=$_SERVER['HTTP_HOST'];?>/img/pictures.png') no-repeat transparent;" href="view/photosets">Photosets</a><br />
	<blockquote>View all the photo sets on your account.</blockquote>
</div>
<div class="half last" onmouseover="general.rollover(this, 'over');" onmouseout="general.rollover(this, 'out');" style="cursor: pointer;" onclick="general.go('view/photos');">
	<a style="background: url('http://<?=$_SERVER['HTTP_HOST'];?>/img/picture.png') no-repeat transparent;" href="view/photos">Photos</a><br />
	<blockquote>View all the photos you have uploaded to your flickr account.</blockquote>
</div>
<? endif; ?>
<p class="clear"></p>
<div class="third" onmouseover="general.rollover(this, 'over');" onmouseout="general.rollover(this, 'out');" style="cursor: pointer;" onclick="general.go('options');">
	<a style="background: url('http://<?=$_SERVER['HTTP_HOST'];?>/img/options.png') no-repeat transparent;" href="options">Options</a><br />
	<blockquote>Tinker with the way FlickrImportr operates, as well as some options to fix possible issues.</blockquote>
</div>
<div class="third" onmouseover="general.rollover(this, 'over');" onmouseout="general.rollover(this, 'out');" style="cursor: pointer;" onclick="general.go('invite');">
	<a style="background: url('http://<?=$_SERVER['HTTP_HOST'];?>/img/invite.png') no-repeat transparent;" href="invite">Invite</a><br />
	<blockquote>Know someone that would find FlickrImportr useful? Invite them to use it!</blockquote>
</div>
<div class="third last<?=(!$auth) ? ' disabled"' : '"  onmouseover="general.rollover(this, \'over\');" onmouseout="general.rollover(this, \'out\');" style="cursor: pointer;" onclick="general.go(\'jobs\');"' ?>>
	<a style="background: url('http://<?=$_SERVER['HTTP_HOST'];?>/img/jobs<?=(!$auth) ? '_disabled': '' ?>.png') no-repeat transparent;" href="jobs">Jobs</a><br />
	<blockquote>Control and view what you currently importing, plus view what you've imported previously.</blockquote>
</div>
<p class="clear"></p>
</div>
<br />
<?=$this->element('ad');?>
