<style type="text/css">
	.options small {
	color: #aaa;
	}
	
	.options label {
	font-weight: normal;
	}
</style>

<script type="text/javascript">
var queue = <?=json_encode($photos);?>;
var facebook = <?=json_encode($facebook_albums);?>;
var flickr = <?=json_encode($photosets);?>;
var pages = <?=json_encode($page_albums);?>;



document.getElementById('overlay').addEventListener('click', function(e) {
	e.preventDefault();
	review.closeOptions();
}, false);

</script>

<div id="photos">
	<form id="form_pictures" action="go" method="POST" onsubmit="return review.checkForm(this);">
		<div style="width: 90%; margin: 0 auto;">
		<div style="text-align: right;"><input type="submit" class="inputsubmit" value="Start Import" name="Start Import"/></div>
		<table class="editorkit" border="0" cellspacing="0" style="width:425px">
			<tr class="width_setter"><th style="width:75px"></th><td></td></tr>

            <? if(count($page_albums) > 0): ?>
            <tr id="upload_to"><th><label>Upload To:</label></th><td class="editorkit_row">
                <input type="radio" name="upload_to" value="personal" id="personal" style="display: inline; width: 13px;border:none;" checked="checked" onclick="review.setTypeImport('personal');"/> <label for="personal">Personal Profile</label>&nbsp;&nbsp;&nbsp;
                <!--<input type="radio" name="upload_to" value="page" id="page" style="display: inline; width: 13px;border:none;" onclick="review.setTypeImport('page');" disabled="disabled"/> <label for="page">Fan Page</label>!--> <i><a href="https://www.facebook.com/flickrimportr/posts/10150476942374809">Page import broken for now</a></i><br />
                <select id="page_selector" style="display:none;" onchange="review.changePageAlbum(this.getValue())">
                    <?
						foreach($page_albums as $page) {
							?><option value="<?=$page['id']; ?>"><?=strip_tags($page['name']); ?></option><?
						}
					?>
                </select>
				<td class="right_padding"></td>
            </tr>
            <? endif; ?>

			<tr><th class="detached_label"><label></label></th><td class="editorkit_row">
				<input type="radio" name="album_from" value="new" id="new" style="display: inline; width: 13px;border:none;" checked="checked" onclick="review.change_import_type(this.getValue());"/> <label for="new">Create New Album</label><br />
				<input type="radio" name="album_from" id="flickr" value="flickr" style="display: inline; width: 13px;border:none;" onclick="review.change_import_type(this.getValue());"/> <label for="flickr">Create Facebook Album From flickr photoset</label><br />
				<input type="radio" name="album_from" value="facebook" id="facebook" style="display: inline; width: 13px;border:none;" onclick="review.change_import_type(this.getValue());"/> <label for="facebook">Add Photos To Existing Facebook Album</label><br />
			</td><td class="right_padding"></td></tr>
			<tr id="flickr_photosets" style="display: none;"><th><label>flickr photoset:</label></th><td class="editorkit_row">
				<select onchange="review.set_album_id(this.getValue(), 'flickr')">
				<option value=""></option>
					<? foreach($photosets as $photoset) : ?>
						<option id="flickr_photo_<?=$photoset['id'];?>" value="<?=$photoset['id']; ?>"><?=strip_tags($photoset['title']); ?></option>
					<? endforeach; ?>
				</select><td class="right_padding"></td>
            </tr>
            
			<tr id="facebook_albums" style="display: none;"><th><label>Facebook Albums:</label></th><td class="editorkit_row">
                    <div id="personal_albums">
                        <select onchange="review.set_album_id(this.getValue(), 'facebook')">
                            <option value=""></option>
                            <?
                                foreach($facebook_albums as $album) :
                                    if($album['name'] != 'Profile Pictures')
                                        ?><option value="<?=$album['aid']; ?>"><?=$album['name']; ?></option><?
                                endforeach;
                            ?>
                        </select>
                    </div>
                    <div id="page_albums" style="display:none;">
                        <? foreach($page_albums as $page) : ?>
                            <? if(count($page['albums']) > 1): ?>
                            <select onchange="review.set_album_id(this.getValue(), 'page')" id="page_album_<?=$page['id'];?>" style="display:none;">
                                <option value=""></option>
                                <? foreach($page['albums'] as $album) : ?>
                                    <option value="<?=$album['aid'];?>"><?=$album['name'];?></option>
                                <? endforeach; ?>
                            </select>
                            <? else : ?>
                                <div id="page_album_<?=$page['id'];?>">
                                    <i>No albums found</i>
                                </div>
                            <? endif; ?>
                        <? endforeach; ?>
                    </div>

                <td class="right_padding"></td>
            </tr>
            
			<tr id="album_row"><th><label>Title</label></th><td class="editorkit_row"><input type="text" name="album_name" id="album_name" /></td><td class="right_padding"></td></tr>
			<tr id="desc_row"><th class="detached_label"><label>Description:</label></th><td class="editorkit_row"><textarea name="album_desc" id="album_desc"></textarea></td><td class="right_padding"></td></tr>
            <!-- <tr id="privacy_row"><th class="detached_label"><label>Facebook Album Privacy:</label></th><td class="editorkit_row">
				<select name="album_privacy">
					<option value="EVERYONE" selected="selected">Everyone</option>
					<option value="NETWORKS_FRIENDS">Networks and Friends</option>
					<option value="FRIENDS_OF_FRIENDS">Friends of Friends</option>
					<option value="ALL_FRIENDS">Friends Only</option>
				</select>
			</td><td class="right_padding"></td></tr> -->
			<tr><td>&nbsp;</td><td><a href="#" onclick="review.openOptions('all'); return false;">Options For All Pictures</a></td></tr>
		</table>
		<div id="options_all" style="display: none; position:absolute;background-color:#fff;z-index:95;border: 1px solid #333; padding: 5px;text-align: center;">
			<h3>Options</h3>
			<h5>Include in description</h5>
			<input type="checkbox" name="title" value="title" onclick="review.description('all', this.getValue(), this.getChecked())" /> Title<br />
			<input type="checkbox" name="url" value="url" onclick="review.description('all', this.getValue(), this.getChecked())" /> Orginal URL<br />
			<input type="checkbox" name="tags" value="tags" onclick="review.description('all', this.getValue(), this.getChecked())" /> Tags<br />
			<input type="checkbox" name="date" value="date" onclick="review.description('all', this.getValue(), this.getChecked())" /> Date Taken<br />
			<input type="button" class="inputsubmit" value="Close" onclick="review.closeOptions();" />
		</div>
		<div style="border-top: 1px dotted #333; width: 100%; height: 1px; margin-bottom: 10px;"></div>
		<input type="hidden" name="album_id" value="" id="album_id" />
        <input type="hidden" name="user_id" value="" id="user_id" />
		<?php
			$i = 0;
			foreach($photos as $photo) {
				?>
					<div id="photo_<?=$photo['id']; ?>" style="position:relative;float: left; margin-bottom: 15px; height: 265px; text-align: center; width: 170px;">
						<div style="background: url('<?=$photo['url']; ?>') no-repeat center center #fff; height: 150px; width: 160px; border: 1px solid #eee; position: relative;">
							<a style="background-color: #fff; opacity:.9; padding: 3px 3px; position: absolute; top: 0; right: 0; border: 1px solid #ddd;z-index: 5" href="#" onclick="review.remove_photo('<?=$photo['id']; ?>', this);return false;">
								<img src="http://<?=$_SERVER['HTTP_HOST']; ?>/img/remove.gif" alt="Remove" />
							</a>
						</div>
						<textarea id="desc_<?=$photo['id']; ?>" name="desc_<?=$photo['id']; ?>" style="height: 50px; width: 90%;" ><?=$photo['description']; ?></textarea><br />
						<a href="#" onclick="review.openOptions(<?=$photo['id']; ?>); return false;">Options</a>
						<div id="options_<?=$photo['id']; ?>" class="options">
							<h3>Options</h3>
							<h5>Include in description</h5>
							<input type="checkbox" id="title_<?=$photo['id'];?>" name="title_<?=$photo['id'];?>" value="title" onclick="review.description(<?=$photo['id']; ?>, this.getValue(), this.getChecked())" /> <label for="title_<?=$photo['id'];?>">Title<br />
							<small><?=$photo['title'];?></small></label><br />
							<input type="checkbox" id="url_<?=$photo['id'];?>" name="url_<?=$photo['id'];?>" value="url" onclick="review.description(<?=$photo['id']; ?>, this.getValue(), this.getChecked())" /> <label for="url_<?=$photo['id'];?>">Orginal URL<br />
							<small style="word-wrap: break-word"><?=$photo['flickr_url'];?></small></label><br />
							<input type="checkbox" id="tags_<?=$photo['id'];?>" name="tags_<?=$photo['id'];?>" value="tags" onclick="review.description(<?=$photo['id']; ?>, this.getValue(), this.getChecked())" /> <label for="tags_<?=$photo['id'];?>">Tags<br />
							<small><?=$photo['tags'];?></small></label><br />
							<input type="checkbox" id="date_<?=$photo['id'];?>" name="date_<?=$photo['id'];?>" value="date" onclick="review.description(<?=$photo['id']; ?>, this.getValue(), this.getChecked())" /> <label for="date_<?=$photo['id'];?>">Date Taken<br />
							<small><?=$photo['date_taken'];?></small></label><br />
							<input type="button" class="inputsubmit" value="Close" onclick="review.closeOptions();" />
						</div>
						<div class="clear"></div>
					</div>
				<?
				$i++;
			}
		?>
		<div class="clear"></div>
		<div style="border-top: 1px dotted #333; width: 100%; height: 1px; margin-bottom: 10px;"></div>
		<div style="text-align: right;"><input type="submit" class="inputsubmit" value="Start Import" name="Start Import"/></div>
		</div>
	</form>
</div>



<div id="overlay" style="position: absolute; left: 0; top: 0; z-index: 90; opacity: .5">
</div>

<? if(isset($quick)):?>
	<script type="text/javascript">
	review.set_album_id(document.getElementById('flickr_photo_<?=$quick['id'];?>').getValue(), 'flickr');
	</script>
<? endif; ?>
