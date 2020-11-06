<div class="d-flex justify-content-around">
	<form id="Annee_scolaire_ajout">
		<table class="table">
			<thead>
				<tr><th colspan="2" class="text-center text-secondary">Nouvelle année scolaire</th></tr>
				<tr id="msg_ajout_annee_scolaire"></tr>
			</thead>
			<tbody>
				<tr>
					<td>Année scolaire:</td>
					<td>
						<input type="text" name="des_annee_scolaire" autocomplete="off" 
						 class="form-control form-control-sm">	
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<input type="submit" value="Valider" class="form-control btn-outline-secondary form-control-sm">
					</td>
				</tr>
			</tbody>
		</table>
	</form>
	<!--Liste---->
	<div>
		<div class="bg-primary text-center text-light">Liste des années scolaires</div>
		<div style="max-height: 50vh; overflow: auto;">
			<table class="table-hover table small tfilter">
				<tbody>
					<?php foreach($preparation[1] as $annee_scolaire):;?>
						<tr>
							<td class="<?= $couleur[rand(0,4)];?>"><?= $annee_scolaire;?></td>
							<td>
								<a href="#annee_scolaire" class="lien_supp_annee" data-toggle='tooltip' title='supprimer'>
									<img src="../img/annee_scolaire.ico" height="10" width="10">
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<table>
			<tfoot>
				<form>
					<tr>
						<td>
							<input type="text" placeholder="filtrer le tableau" 
							class="form-control form-control-sm ifilter" autocomplete="off" >
						</td>
					</tr>
				</form>
			</tfoot>
		</table>
	</div>
</div>
<h5 class="text-center text-danger small">
	Il faut entrer la nouvelle année scolaire si toutes 
	les notes de l'ancienne sont complètement fermées.
</h5>
<!---Script--->
<script type="text/javascript">
	//ajout niveau
$("#Annee_scolaire_ajout").submit(function(event){
    event.preventDefault(); //prevent default action 
    $.ajax({
        url : '<?php echo $this->request->base; ?>/Anneescolaires/ajouter',
        type: 'POST',
        data : new FormData(this),
		contentType: false,
		cache: false,
		processData:false
    }).done(function(response){ //
        $("#msg_ajout_annee_scolaire").html(response);
        window.location.replace('<?php echo $this->request->base; ?>/Preparations/index#annee_scolaire');
        setTimeout(function(){location.reload()}, 1000);
    });
});

	//Suppression
$('.tfilter tbody').on('click', '.lien_supp_annee', function(e){
	e.preventDefault();
	var x = $(this).closest('tr');
	$.post("<?php echo $this->request->base; ?>/Anneescolaires/supprimer",
			{
				des_annee_scolaire: $(this).closest('tr').find('td:eq(0)').text().trim(),
			}, 
			function(data){
				if(data == 1)x.remove();
				else alert(data);

		});
	});
</script>