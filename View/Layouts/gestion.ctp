<!DOCTYPE html>
<html>
<head>
	<link rel="icon" href="<?php echo $this->request->base; ?>/img/logo.ico" type="image/x-icon" />
	<title>Gestion des notes</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?= $this->html->css('gestion'); ?>
	<?= $this->html->css('bootstrap'); ?>
	<?= $this->html->css('jquery-ui.css.min.css'); ?>
	<?= $this->html->script("jquery-3.4.1"); ?>
	<?= $this->html->script("jquery-ui.js.min.js"); ?>
	<?= $this->html->script('bootstrap'); ?>
	<?= $this->html->script('canvasjs.min.js'); ?>
	<?= $this->html->script('arrow-table.min.js'); ?>
	<div class="d-flex justify-content-around bg-dark navbar navbar-expand-sm small">
		<a class="text-decoration-none text-light" id="Eleves"
			href="<?php echo $this->request->base; ?>/Eleves/index">
			ELEVE
		</a>
		<a class="text-decoration-none text-light" id="Promotions"
			href="<?php echo $this->request->base; ?>/Promotions/index">
			CLASSIFICATION
		</a>
		<a class="text-decoration-none text-light" id="Notes"
			href="<?php echo $this->request->base; ?>/Notes/index">NOTE</a>
		<a class="text-decoration-none text-light" id="Suivis"
			href="<?php echo $this->request->base; ?>/Suivis/index">SUIVI</a>
		<?=$this->Html->link(
          $this->Html->image('../img/deconnexion.png'),
           // Recherche dans le dossier webroot/img
          array('controller' => 'Users', 'action' => "logout"),
      	  array('escape' => false, 'data-toggle'=>'tooltip','title'=>'Se déconnecter'));// Ceci pour indiquer de ne pas échapper les caractères HTML du lien vu qu'ici on a une image
        ?>
	</div>
</head>
<body>
	<div id="conteneur">
		<?= $this->fetch('gestion'); ?>
	</div>
	<div id="mySidenav" class="sidenav bg-dark small">
		<span class="text-light" style="margin-top: 1vw" 
			role="button" aria-pressed="true">
		&#9776;
		</span>
		<a class="text-decoration-none text-light small" id="Preparations"
		href="<?php echo $this->request->base; ?>/Preparations/index">PREPARATION</a>	

		<a class="text-decoration-none text-light small" id="Matieres"
		href="<?php echo $this->request->base; ?>/Matieres/index">MATIERE</a>	

		<a class="text-decoration-none text-light small"  id="Coefficients"
		href="<?php echo $this->request->base; ?>/Coefficients/index">COEFFICIENT</a>
		<a href="<?php echo $this->request->base; ?>/Users/compte" id="Users" 
			class="text-center text-decoration-none text-light" data-toggle="tooltip" title="Mon compte">
			<img src="<?= $this->request->base;?>/img/stock_people.png" height="20" width="35%">
			Compte
		</a>
	   <a href="javascript:void(0)">
	   		&nbsp;
		</a>
	</div>
</body>
<footer class="bg-dark">
	<!--<img src="../imglogo">-->
	<table class="table table-sm">
		<tbody>
			<tr>
				<td class="text-center text-light">
					Gestion des notes des élèves
				</td>
			</tr>
		</tbody>
	</table>
</footer>
<?php //echo $this->html->script('gestion'); ?>
</html>
<script type="text/javascript">
	$(document).ready(function(){
		//fichier
		$(".custom-file-input").on("change", function(e) {
			/*var all_files = 0;
			for(var i = 0; i < e.target.files.length; i++) 
				all_files += e.target.files[i].name +'; ';*/
			var val = e.target.files.length;
			var msg = (val == 0 || val == 1) ? val + 'fichier choisi' : val + ' fichiers choisis';
		 $(this).siblings(".custom-file-label").addClass("selected").html(msg);
		});
		
		//Filtrage
		$('.ifilter').keyup(function(){
			var value = $(this).val().toLowerCase();
   			 $(".tfilter tbody tr").filter(function() {
     			 $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
   			 });
		});

		//Multiselect
		$('.recherche_select').on("mouseover mouseout", function(event){
	        switch(event.type){
	          case 'mouseover': $(this).attr('size', 3); break;
	          default: $(this).attr('size', 1); break;
     	 }
    });
	// couleur des liens
		var lien = (window.location.pathname.split('/')[2]).trim();
		$("#" + lien).removeClass('text-light');
		$("#" + lien).addClass('badge bg-info text-warning');
	});
</script>