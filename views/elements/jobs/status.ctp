<div>
<div class="jobinfo">
<b>Status: <?=$job['Job']['nice_status'];?></b><br />
Job Created: <?=$time->niceShort($time->fromString($job['Job']['created']) + $_SESSION['time_offset']);?><br />
<? if($job['Job']['status'] == 3 || $job['Job']['status'] == 6) { ?> Job Completed: <?=$time->niceShort($time->fromString($job['Job']['completed']) + $offset);?><br /> <? } ?>
Import To: <?=$job['Job']['import_object_name'];?><br />
Album Name: <?=$job['Job']['title'];?><br />
<? if($job['Job']['status'] != 3 && $job['Job']['status'] != 6) { ?> Progress: <?=round($job['Job']['percentage']);?>%  (<?=$job['Job']['photos_completed'];?> / <?=$job['Job']['total']?>)<br /> <? } ?>

<? if($job['Job']['status'] == 5 || $job['Job']['status'] == 6) { ?> <span style="color: #f00;">Error Message: <?=$job['Job']['error'];?></span><br /> <? } ?>
</div>
<div class="jobactions">
<? if($job['Job']['status'] == 2 || $job['Job']['status'] == 5) { ?> <a href="#" name="<?=$job['Job']['id'];?>" onclick="jobs.start('<?=$job['Job']['id'];?>'); return false;" id="link_resume_<?=md5($job['Job']['id'])?>">Resume Job</a><? } ?>
<? if($job['Job']['status'] == 2 || $job['Job']['status'] == 5) { ?> <a href="#" onclick="jobs.restart('<?=$job['Job']['id'];?>', this); return false;" id="link_restart_<?=md5($job['Job']['id']);?>">Restart Job</a> <? } ?>
<? if($job['Job']['status'] == 2 || $job['Job']['status'] == 5) { ?> <a href="#" name="<?=$job['Job']['id'];?>" onclick="jobs.remove('<?=$job['Job']['id'];?>', this); return false;" id="link_delete_<?=md5($job['Job']['id']);?>">Delete Job</a> <? } ?>
<? if($job['Job']['status'] == 1 || $job['Job']['status'] == 4) { ?><a href="#" name="<?=$job['Job']['id'];?>" onclick="jobs.stop('<?=$job['Job']['id'];?>'); return false;" id="link_stop_<?=md5($job['Job']['id']);?>">Stop/Pause Job</a> <? } ?>
<? if($job['Job']['status'] == 3 || $job['Job']['status'] == 6) { ?> <a href="<?=$job['Job']['album_link'];?>">View Photo Album</a> <? } ?>
<? if($job['Job']['reported'] == 1) { ?><div class="disabled">Job Reported</div> <? } else { ?><a href="#" name="<?=$job['Job']['id'];?>" id="link_report_<?=md5($job['Job']['id']);?>" onclick="jobs.report('<?=$job['Job']['id'];?>', this);return false;">Report Issue</a> <? } ?>
</div>
<div class="clear"></div>
</div>
