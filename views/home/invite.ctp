<? if(isset($message)) echo $message; ?>

<fb:request-form
	action="/<?=$GLOBALS['appPath'];?>/"
	method="post"
	type="FlickrImportr"
	content="<? echo htmlentities($content,ENT_COMPAT,'UTF-8'); ?>">

	<fb:multi-friend-selector
		actiontext="If you think some of your friends would find FlickrImportr useful, why not invite them?"
		exclude_ids="<? echo $friends; ?>" />
</fb:request-form>