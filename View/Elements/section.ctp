<div class="d-flex justify-content-around">
	<div></div>
	<form id="ajout_section">
		<table class="table">
			<thead>
				<tr><th colspan="2" class="text-center text-secondary">Nouvelle section</th></tr>
				<tr id="msg_ajout_section"></tr>
			</thead>
			<tbody>
				<tr>
					<td>Classe:</td>
					<td>
						<select class="form-control form-control-sm recherche_select" name="des_classe">
							<?php foreach($preparation[2] as $classe):;?>
								<option value="<?= $classe;?>"><?= $classe;?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<td>Section:</td>
					<td>
						<input type="text" name="code_section" autocomplete="off" 
						placeholder="Ex:A" class="form-control form-control-sm">	
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<input type="submit" class="form-control btn-outline-secondary form-control-sm">
					</td>
				</tr>
			</tbody>
		</table>	
		<div class="text-secondary text-center small">Laissez vide la section si la classe</div>
		<div class="text-secondary text-center small">choisie ne presente aucune section.</div>
	</form>
	<!--Liste---->
	<div>
		<div class="text-center">Liste des sections</div>
		<div style="max-height: 50vh; overflow: auto;">
			<table class="table-hover small text-center table tfilter">
				<tbody>
					<?php foreach($preparation[3] as $section):;?>
						<tr>
							<td class="<?= $couleur[rand(0,4)];?>"><?= $section['Section']['des_classe'];?></td>
							<td class="<?= $couleur[rand(0,4)];?>"><?= $section['Section']['code_section'];?></td>
							<td>
								<a href="#section" class="lien_supp_section" data-toggle="tooltip" title="supprimer">
									<img src="<?= $this->request->base;?>/img/annee_scolaire.ico" height="10" width="10">
								</a>
							</td>
						</tr>
					<?php endforeach; ?>

				</tbody>
			</table>
		</div>
		<input type="text" class="form-control form-control-sm ifilter" placeholder="filtrer">
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function(){

	});
	//ajout niveau
$("#ajout_section").submit(function(event){
    event.preventDefault(); //prevent default action 
    $.ajax({
        url : '<?php echo $this->request->base; ?>/Sections/ajouter',
        type: 'POST',
        data : new FormData(this),
		contentType: false,
		cache: false,
		processData:false
    }).done(function(response){ //
        $("#msg_ajout_section").html(response);
        window.location.replace('<?php echo $this->request->base; ?>/Preparations/index#section');
        setTimeout(function(){location.reload()}, 500);
       //alert(response);
    });
});

//Suppression
$('.tfilter tbody').on('click', '.lien_supp_section', function(e){
	e.preventDefault();
	window.location.replace('<?php echo $this->request->base; ?>/Preparations/index#section');
	var x = $(this).closest('tr');
	//alert($(this).closest('tr').find('td:eq(1)').html());
	$.post("<?php echo $this->request->base; ?>/Sections/supprimer",
			{	
				code_section: $(this).closest('tr').find('td:eq(1)').text().trim(),
				des_classe: $(this).closest('tr').find('td:eq(0)').text().trim(),
			}, 
			function(data){
				if(data == 1)x.remove();
				else alert(data);
			});
})
</script>