<fb:explanation>
     <fb:message>Select A Photoset</fb:message>
     Select the photoset you would like to pick photos from, or choose the photos tab above to see all the pictures in your photostream
</fb:explanation>

<script>
	var dialogBox = false;
	function submit(id) {
		if(dialogBox === false) 
			document.setLocation('/<?=$GLOBALS['appPath'];?>/view/photosets/'+id);
	}
</script>
<? $i = 0; ?>
<table width="100%" class="album_wrapper">
<? foreach($photosets['photoset'] as $photo): ?>

	<? if($i == 0) echo "<tr>"; ?>
	<td width="33%" class="album" onclick="submit('<?=$photo['id']?>')" onmouseover="general.rollover(this, 'over');" onmouseout="general.rollover(this, 'out');" style="cursor: pointer;">
		<div style="position: relative;">
			<div style="float: left;width: 35%;">
				<a href="/<?=$GLOBALS['appPath'];?>/view/photosets/<?=$photo['id']?>">
					<img src="http://farm<?=$photo['farm'];?>.static.flickr.com/<?=$photo['server'];?>/<?=$photo['primary']?>_<?=$photo['secret']?>_s.jpg" align="left" style="border: 1px solid #eee;" />
				</a>
			</div>
			<div style="float: left;width: 65%;">
				<a style="font-weight: bold;"><?=$photo['title'];?></a><br /><?=(!empty($photo['description'])) ? $text->truncate($photo['description'], 125, '...', false) : "<i>no description</i>" ?>
			</div>
			<span style="position: absolute; bottom: 0; right: 0;"><a href="#" onclick="return photosets.quickimport('<?=$photo['id'];?>', '<?=count($_SESSION['photos']);?>'); return false;">quickimport</a></span>
			<div class="clear"></div>
		</div>
	</td>
	
	<? $i++; if($i == 3) { echo "</tr><tr><td colspan=\"3\" height=\"2\"></td></tr>"; $i = 0; }?>
<? endforeach; ?>
</table>