<script type="text/javascript">
	jobs.active_jobs = <?=json_encode($ajax_jobs);?>;
	
	jobs.status(jobs.active_jobs.join(','));
</script>

<h2>Active Jobs (<?=count($uncompleted)?>)</h2>
<?
if(count($uncompleted) > 0) {
?>
<div class="top_border"></div>
<?
	foreach($uncompleted as $job):
?>
<div id="job_<?=md5($job['Job']['id']);?>" class="job">
<?=$this->element('jobs/status', array('job' => $job)); ?>
</div>
<? endforeach; } else {?>
You have no active jobs.
<? } ?>
<hr />
<?=$this->element('ad');?>
<hr />
<h2 id="completed_header" style="background: url('http://<?=$_SERVER['HTTP_HOST'];?>/img/arrow_down.png') no-repeat; padding-left:20px;cursor: pointer;" onclick="jobs.toggle_completed();">Completed Jobs (<?=count($completed)?>)</h2>
<?
if(count($completed) > 0) { ?>
<div id="completed_jobs" style="display: none;">
<div class="top_border"></div>
<?
	foreach($completed as $job):
?>
<div id="job_<?=md5($job['Job']['id']);?>" class="job">
<?=$this->element('jobs/status', array('job' => $job)); ?>
</div>
<? endforeach;
?> </div>
<hr />
<?
} else {?>
You have no completed jobs.
<? } ?>

<fb:js-string var="bug_report">
If you would like to add any comments to your report that might help us solve the problem, leave them below. Otherwise, just click the submit button.
<textarea rows="3" id="bug_report_input" style="width: 95%"></textarea>
</fb:js-string>