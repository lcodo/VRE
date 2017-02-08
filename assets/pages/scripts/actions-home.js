// delete files / folders
var fileName = '';
var option = '';

function deleteFile(file){
  $('#modalDelete .modal-body').html('Are you sure you want to delete the selected file?');
  $('#modalDelete').modal({ show: 'true' });
  fileName = file;
  option = 'deleteSure';
}

function deleteFolder(folder){
  $('#modalDelete .modal-body').html('Are you sure you want to delete the selected folder and <strong>ALL</strong> its content?');
  $('#modalDelete').modal({ show: 'true' });
  fileName = folder;
  option = 'deleteDirOk';
}


// Open modal with analysis parameters
callShowSHfile = function(tool, sh) {

	$('#modalAnalysis').modal('show');
	$('#modalAnalysis .modal-body').html('Loading data...');

	$.ajax({
		type: "POST",
		url: "/applib/showSHfile.php",
		data: "fn=" + sh + "&tool=" + tool, 
		success: function(data) {
			$('#modalAnalysis .modal-body').html(data);
		}
	});

}

toggleVis = function(layer) {
	$('#' + layer).slideToggle();
}


$(document).ready(function() {

	$('#modalDelete').find('.modal-footer .btn-modal-del').on('click', function(){
		$('#modalDelete').find('.modal-footer .btn-modal-del').prop('disabled', true);
		$('#modalDelete').find('.modal-footer .btn-modal-del').html('Deleting...');

		$.ajax({
			type: "GET",
			url: "/workspace/workspace.php",
			data: "op=" + option + "&fn=" + fileName, 
			success: function(data) {
				$('#modalDelete').modal('toggle');	
				location.href="/workspace/";
			}
		});

	});


	// TOOLS
	// PDNA DOCKING!!!

	$('#pdna-docking').on('click', function() {
		var query = "";
		for(i in allFiles){
			if(allFiles[i].checked) {
				query += 'fn[]=' + allFiles[i].fileId + '&';
			}
		} 
		query = query.slice(0, -1);
		location.href = "/tools/pydock/input.php?" + query;
	});

	// NUCLEOSOME DYNAMICS!!!

	$('#nucleosome-dynamics').on('click', function() {
		var query = "";
		for(i in allFiles){
			if(allFiles[i].checked) {
				query += 'fn[]=' + allFiles[i].fileId + '&';
			}
		} 
		query = query.slice(0, -1);
		location.href = "/tools/nucldynwf/input.php?" + query;
	});

});
