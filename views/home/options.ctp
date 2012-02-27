<!--<div style="text-align: center; margin-bottom: 25px;">
<input type="checkbox" id="authorize" onclick="options.authorizeUpload(this)" <?=(isset($permission)) ? "checked=\"checked\"" : "";?> /> <label for="authorize">Let FlickrImportr to automatically approve photos uploaded using this application.</label><br />
</div>-->

<?=$this->element('ad');?>

<div class="third" style="padding: 14px;">
	<h2>Clear Queue</h2>
	If you are having issues getting to the review screen, or having issues importing your photos, you can clear you queue. Doing this should solve most issues you are having with the application, but <i>it will remove all photos from your queue</i>.<br />
	<input type="button" name="reset" value="Clear Queue" class="inputsubmit" onclick="options.clearQueue()"/>
</div>
<div class="third" style="padding: 14px;">
	<h2>Deauthorize FlickrImportr</h2>
	In case you want to use FlickrImportr with another Flickr account for any reason, or remove the ability for FlickrImportr to access your Flickr account, click the "Deauthorize" button below which you force FlickrImportr to forget the associated Flickr account.<br />
	<form action="/<?=$GLOBALS['appPath'];?>/options" method="post" id="deauthorize">
	<input type="hidden" name="deauthorize" value="true" />
	<input type="button" value="Deauthorize" class="inputsubmit" onclick="options.deauthorize();" />
	</form>
</div>
<div class="third last"  style="padding: 14px">
	<h2>Uninstall FlickrImportr</h2>
	You can completely uninstall and remove yourself from this applicaation by clicking this button below.<br /><b>Note:</b> This will only uninstall the application from Facebook and not revoke access through Flickr.<br />
	<form action="/<?=$GLOBALS['appPath'];?>/options" method="post" id="uninstall">
	<input type="hidden" name="uninstall" value="true" />
	<input type="button" value="Uninstall Application" class="inputsubmit" onclick="options.uninstall();" />
	</form>
</div>
<p class="clear"></p>
<? pr($_SESSION); ?>
