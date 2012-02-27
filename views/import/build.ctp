<?
	if(!isset($_SESSION['photos']) || !count($_SESSION['photos'])) {
		?>
		<fb:error>
	      <fb:message>No Photos Selected</fb:message>
	      You haven't selected any photos to import. <a href="index.php">Click here</a> to view your photosets or <a href="view.php?type=photos">click here</a> to view your recent photos.
 		</fb:error>
 		<?
	} else {
?>
<fb:explanation>
     <fb:message>Please Wait</fb:message>
     	FlickrImportr is obtaining information about photos in your queue. It will forward you onto your queue when it is done. Please do not navigate away from this page during this process.
     <img src="http://<?=$_SERVER['HTTP_HOST'];?>/img/loading.gif" id="loading" />
     <div id="progresswrapper"><div id="progressbar"><div id="progressinterior">0%</div></div></div>
</fb:explanation>

<div style="height: 5px;"></div>
<?=$this->element('ad'); ?>

<script>
	build.go(false);
</script>
<? } ?>