<?php $this->start('gestion'); ?>
<div id="tabs">
	<ul class="d-flex justify-content-center">
		<li><a href="#niveau">NIVEAU</a></li>
		<li><a href="#annee_scolaire">ANNEE SCOLAIRE</a></li>
		<li><a href="#classe">CLASSE/SERIE</a></li>
		<li><a href="#section">SECTION</a></li>
		<li><a href="#examen">EXAMEN</a></li>
	</ul>
	<div id="niveau"><?= $this->element('niveau'); ?></div>
	<div id="annee_scolaire"><?= $this->element('annee_scolaire'); ?></div>
	<div id="classe"><?= $this->element('classe'); ?></div>
	<div id="section"><?= $this->element('section'); ?></div>
	<div id="examen"><?= $this->element('examen'); ?></div>
</div>
<!--<h6 class="text-center text-danger">... Faites attentions aux tentatives de suppression ....</h6>-->
<script type="text/javascript">
	$(document).ready(function(){
		$('#tabs').tabs();
});
</script>
<?php $this->end(); ?>