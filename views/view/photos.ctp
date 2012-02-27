<?php include_once($_SERVER['DOCUMENT_ROOT'] . '/vendors/paging.class.php')?>


<div id="view_photo" style="display: none; text-align: center;" >
	<img src="http://<?=$_SERVER['HTTP_HOST']; ?>/img/loading.gif" id="display_photo" /><br />
	<a href="#" onclick="view.close_full(); return false;">close without checking image</a> | <a href="#" onclick="view.close_full(true); return false;">close and check image</a>
</div>


<div id="view_all">
	<fb:explanation>
	     <fb:message>Add Photos To Queue</fb:message>
	     Click on the photo(s) you would like to add to your queue. Once you have completed adding photos to your queue, you can either continue to add photos from another photoset, or click "Review and Import" if you are completed.
	</fb:explanation>
	
	
	<form action="/<?=$GLOBALS['appPath'];?>/view/submit" method="post" id="photos">
	<input type="hidden" name="type" value="<?=$type; ?>" />
	<table border="0" cellspacing="0" style="width: 90%;" align="center">
	<tr>
	<td style="text-align: left; width: 33%">
		<div class="pagerpro pager_top">
			<? 
				if($pages > 1) {
					new Paging('page-', '', $page, $pages, Array('left' => 2, 'center' => 3, 'right' => 2));
				}
			?>
		</div>
	</td>
	<td style="text-align: center; width: 33%">
		Showing photos <?=($page == 1) ? 1 : (($page - 1) * $perpage) + 1?> - <?=($page == 1) ? $perpage : ($page * $perpage)?> of <?=$total;?>
	</td>
	<td style="text-align: right; width: 33%">
		<a href="#" onclick="view.check_all(true);return false;">Check All</a> / <a href="#" onclick="view.check_all(false);return false;">Uncheck All</a>
		<input type="submit" value="Submit" class="inputsubmit" />
	</td>
	</tr>
	<tr><td colspan="3">
		<div class="album_container">
		<? $i = 0; ?>
		<? if(!count($photos)): ?>
			<fb:error message="There are no photos to display. Make sure you have uploaded photos and they are visible to the public on flickr" />
		<? else: ?>
		<? foreach($photos as $photo) { ?>
			<div class="photo_wrapper" onmouseover="general.rollover(this, 'over');" onmouseout="general.rollover(this, 'out');">
				<div style="position: relative;">
					<div style="position: absolute; top: 2px; left: 2px; z-index: 2;">
						<input type="checkbox" name="photos[]" id="check_<?=$i;?>" value="<?=$photo['id'];?>" <? if(isset($_SESSION['photos']) && in_array($photo['id'], $_SESSION['photos'])) { echo"checked=\"checked\" "; } ?>/>
					</div>
					
					<a onclick="view.check('<?=$i;?>', '<?=$photo['id'];?>');">
						<img src="<?=$flickr->buildPhotoURL($photo, 'square');?>" border="0">
					</a>
					
					<div style="position: absolute; bottom: 2px; right: 1px; z-index: 2;">
						<a href="#" onclick="view.view_full('<?=$flickr->buildPhotoURL($photo); ?>', '<?=$i; ?>'); return false;" style="font-size: 90%; color: #333; background-color: #fff; opacity: .6">view</a>
					</div>
				</div>
			</div>
			<? ++$i; ?>
			<? } ?>
			<? endif; ?>
		<div class="clear"></div>
		</div>
	</td></tr>
	<tr>
	<td style="text-align: left; width: 33%"">
		<div class="pagerpro pager_bottom">
			<? 
				if($pages > 1) {
					new Paging('page-', '', $page, $pages, Array('left' => 2, 'center' => 3, 'right' => 2));
				}
			?>
		</div>
	</td>
	<td style="text-align: center; width: 33%">
		Showing photos <?=($page == 1) ? 1 : (($page - 1) * $perpage) + 1?> - <?=($page == 1) ? $perpage : ($page * $perpage)?> of <?=$total;?>
	</td>
	<td style="text-align: right; width: 33%">
		<a href="#" onclick="view.check_all(true);return false;">Check All</a> / <a href="#" onclick="view.check_all(false);return false;">Uncheck All</a>
		<input type="submit" value="Submit" class="inputsubmit" />
	</td>
	</tr>
	</table>
		<input type="hidden" name="page_number" id="page_number" />
	</form>
</div>