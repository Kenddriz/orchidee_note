<?php $this->start('gestion'); ?>
<div id="accordion" style="margin-left: 6vw;">
	<h4>Efforts et points forts d'un élève</h4>
	<div>
		<div class="row">
			<div class="col">
				<h6 class="text-center text-secondary">Récapitulation des notes par matière</h6>
				<table class="table table-sm small">
					<tbody>
						<tr>
							<td class="text-center">Sélectionner un examen</td>
							<td>
								<select class="form-control form-control-sm recherche_select" id="code_exam">
									<?php foreach($calibrage[0] as $cle => $examen): ;?>
										<?php if($cle != 'MG'):;?>
										<option value="<?= $cle; ?>"><?= $examen; ?></option>
									<?php endif;?>
									<?php endforeach; ?>		
								</select>
							</td>
						</tr>
						<tr>
							<td id="histo_matiere" style="height: 40vh;" colspan="2"></td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="navbar navbar-nav bg-secondary"></div>
			<div class="col">
				<h6 class="text-center text-secondary">Récapitulation des examens</h6>
				<div id="histo_examen" style="height: 40vh;"></div>
			</div>
		</div>
		<!--Infos trouvés"-->
		<div class="d-flex justify-content-center" id="infos"></div>
		<div class="d-flex justify-content-center small jumbotron">
			<select class="form-control form-control-sm recherche_select" id="annee_scolaire">
				<?php foreach($calibrage[2] as $cle => $annee): ;?>
					<option value="<?= $cle; ?>"><?= $annee; ?></option>	
				<?php endforeach; ?>			
			</select>
			<div class="navbar navbar-nav"></div>
			<input type="text" placeholder="N°Matricule" id="search" 
			 class="form-control form-control-sm text-center num_matricule" autocomplete="off">
			 <div class="navbar navbar-nav"></div>
			<button class="form-control form-control-sm btn-outline-secondary num_matricule">
				Tracer
			</button>
		</div>
	</div>
	<!---!---->
	<h4 class="d-flex justify-content-between">
		Bulletin des notes<input type="text" class="form-control ifilter"
		 style="max-width: 30vw ; max-height: 20px; font-size: 12px" placeholder="Filtrer le tableau">
	</h4>
	<div class="row">
		<div class="col-sm-2 jumbotron">
			<table class="table table-sm small">
				<tbody>
					<tr><td class="text-center">Options</td></tr>
					<tr>
						<td>
							<select class="form-control form-control-sm recherche_select" id="b_scolaire">
								<?php foreach($calibrage[2] as $cle => $annee): ;?>
									<option value="<?= $cle; ?>"><?= $annee; ?></option>	
								<?php endforeach; ?>			
							</select>
						</td>
					</tr>
					<tr>
						<td>
							<select class="form-control form-control-sm recherche_select" id="b_classe">
								<?php foreach($calibrage[1] as $cle => $classe): ;?>
									<option value="<?= $cle; ?>"><?= $classe; ?></option>	
								<?php endforeach; ?>			
							</select>
						</td>
					</tr>
					<!--<tr>
						<td>
							<select class="form-control form-control-sm recherche_select" id="b_section">
								<?php foreach($calibrage[2] as $cle => $section): ;?>
									<option value="<?= $cle; ?>"><?= $section; ?></option>	
								<?php endforeach; ?>			
							</select>
						</td>
					</tr>-->
					<tr>
						<td>
							<select class="form-control form-control-sm recherche_select" id="b_exam">
								<?php foreach($calibrage[0] as $cle => $examen): ;?>
									<option value="<?= $cle; ?>"><?= $examen; ?></option>	
								<?php endforeach; ?>			
							</select>
						</td>
					</tr>
					<tr>
						<td>
							<button class="form-control form-control-sm btn-outline-secondary" id="btn_bulletin">
								Voir
							</button>
						</td>
					</tr>
				</tbody>
				<tfoot>
					<tr>
						<td>
					      	  <a href="<?= $this->request->base;?>/Suivis/bulletin_eleve"
					      	  	class="text-danger" data-toggle="tooltip" title="Consulter les bulletins des notes">
					      	  	<img src="../img/notes.ico" width="25px" height="25px">
					      	  	BULLETINS
					      	  </a>
						</td>
					</tr>
				</tfoot>
			</table>
		</div>
		<div class="col" id="div_bulletin" style="max-height: 60vh; overflow: auto;"></div>
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function(){
		$('#accordion').accordion({active: 0});
		//Histogramme par défaut
		$.get('<?php echo $this->request->base; ?>/Suivis/histogramme', function(data){
			var array=JSON.parse(data);
			$('#infos').html(array[0].infos);
			drawChartMat(array[0].hist1);
			drawChartExam(array[0].hist2);	
		});
		//Bulletin par défaut
		$.get('<?php echo $this->request->base; ?>/Suivis/defaultPageSuivi', 
			function(data){
				$('#div_bulletin').html(data);
		});

	});
	$('.num_matricule').on('keypress click', function(event){
		matricule = $('#search').val().trim();
		if((event.which == 13 || event.type == 'click') && matricule!='') {
			$.post('<?php echo $this->request->base; ?>/Suivis/histogramme',
				{
					num_matricule: matricule,
					code_exam: $('#code_exam').val().trim(),
					annee_scolaire: $('#annee_scolaire').val().trim()
				}, 
				function(data) {
					var array=JSON.parse(data);
					$('#infos').html(array[0].infos);
					drawChartMat(array[0].hist1);
					drawChartExam(array[0].hist2);
			});
			$('#search').css('border-color', 'black');
		}
		else $('#search').css('border-color', 'red');
	});
	//Charter
function drawChartMat(donnees) {
	new CanvasJS.Chart("histo_matiere", {
	theme: "dark1", // "light2", "dark1", "dark2"
	animationEnabled: true, // change to true		
		data: [
			{
				type: "pie",
				toolTipContent: "{label}: <strong>{y}</strong>",
				indexLabel: "{label}  {y}",
				dataPoints: donnees
			}
		]
	}).render();
}
function drawChartExam(donnees) {
	new CanvasJS.Chart("histo_examen", {
	theme: "light2", // "light2", "dark1", "dark2"
	animationEnabled: true, // change to true
	axisY: {
		title: "Moyenne (sur 20)",
		//prefix: "$",
		//suffix:  "k"
	},
	axisX: {
		title: 'Les examens'
	},	
	data: [
		{
			type: "column",
			toolTipContent: "{label}: <strong>{y}</strong>",
			indexLabel: "{y}",
			indexLabelPlacement: "outside",
			indexLabelOrientation: "horizontal",
			dataPoints: donnees
		}
	]}).render();
    
      }
//Bulletin
$("#btn_bulletin").click(function(){
	var num_exam = $('#b_exam').val().trim();
	if(num_exam=='MG') {
		$.post('<?php echo $this->request->base; ?>/Suivis/moyenne_generale', {
			des_annee_scolaire: $('#b_scolaire').val(),
			des_classe: $('#b_classe').val(),
			//code_section: $('#b_section').val()
		}, function(data){
			$('#div_bulletin').html(data);
		});
	}
	else {
		$.post('<?php echo $this->request->base; ?>/Suivis/faire_bulletin', {
			des_annee_scolaire: $('#b_scolaire').val(),
			des_classe: $('#b_classe').val(),
			//code_section: $('#b_section').val(),
			num_exam: num_exam
		}, function(data){
			$('#div_bulletin').html(data);
		});
	}
});
//Table filter
</script>
<style type="text/css">
	#accordion .ui-accordion-content {
    height: 66vh;
}
</style>
<?php $this->end(); ?>