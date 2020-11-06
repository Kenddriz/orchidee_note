<div class="d-flex justify-content-around">
	<form class="jumbotron" id="examen_ajout">
		<table class="table">
			<thead>
				<tr><th colspan="2" class="text-center text-secondary">Nouvel examen</th></tr>
				<tr id="msg_ajout_exam"></tr>
			</thead>
			<tbody>
				<tr>
					<td>Numéro:</td>
					<td>
						<input type="number" value="1" name="num_exam" min="1" max="99" 
						class="form-control form-control-sm">	
					</td>
				</tr>
				<tr>
					<td>Type d'examen:</td>
					<td>
						<input type="text" name="libelle_exam" class="form-control form-control-sm" 
						id="typeExam">
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<input type="submit" value="Enregistrer" class="form-control btn-outline-secondary form-control-sm">
					</td>
				</tr>
			</tbody>
		</table>
	</form>
	<!--Liste---->
	<div>
		<div class="text-center">Liste des examens</div>
		<div>
			<table class="text-center table">
				<thead >
					<tr id="msg_mis_a_jour_exam"></tr>
				</thead>
			</table>
		</div>
		<div style="max-height: 50vh; overflow: auto;">
			<table class="table table-sm text-center tfilter">
				<thead>
					<tr>
						<th colspan="2"></th>
						<th>
							<?php echo $this->Html->image('../img/edit-class-.ico',
								array('width'=> 20, 'height'=> 20)); ?>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($preparation[4] as $numero => $libelle):;?>
						<?php $num = explode('/', $numero);?>
						<tr>
							<td>
								<a href="#examen" class="lien_supp_examen" data-toggle="tooltip" title="supprimer" id="<?=$numero;?>">
									<img src="<?= $this->request->base;?>/img/annee_scolaire.ico" height="10" width="10">
								</a>
							</td>
							<?php $lib = explode('[N°', $libelle)[0];?>
							<td class="<?= $couleur[rand(0,4)];?>">
								<?= ($num[0] > 1)? $num[0].'ème' : $num[0].
								(strtolower(substr(str_ireplace('é', 'e', trim($lib)), 5)) == 'ation' ? 'ère' : 'er');?>
							</td>
							<td contenteditable="true" id="<?=$numero;?>"
							 class="<?= $couleur[rand(0,4)];?>">
								<?=$lib;?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<input type="text" class="form-control-sm form-control ifilter" placeholder="filtrer">
	</div>
</div>
<script type="text/javascript">
	//ajout examen
$("#examen_ajout").submit(function(event){
    event.preventDefault(); //prevent default action 
    $.ajax({
        url : '<?php echo $this->request->base; ?>/Examens/ajouter',
        type: 'POST',
        data : new FormData(this),
		contentType: false,
		cache: false,
		processData:false
    }).done(function(response){ //
        $("#msg_ajout_exam").html(response);
        window.location.replace('<?php echo $this->request->base; ?>/Preparations/index#examen');
        setTimeout(function(){location.reload()}, 500);

    });
});
//Mettre à jour niveau
$('.tfilter tbody td').on('keypress', function(event){
	if(event.which == 13){
		$.post("<?php echo $this->request->base; ?>/Examens/mettre_jour",
			{
				num_exam: this.id.trim(),
				libelle_exam: $(this).text().trim()
			}, 
			function(data){
		        window.location.replace('<?php echo $this->request->base; ?>/Preparations/index#examen');
		        if(data != 1 && data != ''){
		        	$('#msg_mis_a_jour_exam').html(data);
		        	//setTimeout(function(){location.reload()}, 1000);
		    	}
		    	else location.reload();

		});
		return false;
	}
});
//Suppression
$('.tfilter tbody').on('click', '.lien_supp_examen', function(e){
	e.preventDefault();
	var x = $(this).closest('tr');
	$.post("<?php echo $this->request->base; ?>/Examens/supprimer",
			{
				num_exam: this.id.trim(),
			}, 
			function(data){
				if(data == 1)x.remove();
				else alert(data);
		});
})
</script>