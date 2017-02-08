var ComponentsBootstrapSwitch = function () {

    var handleBootstrapSwitch = function() {

        // generic block switches
        $('.switch-block').on('switchChange.bootstrapSwitch', function (event, state) {
            var id = parseInt($(this).attr('id').substring(11,13));
            if(id == 1) var oid = 2;
			else var oid = 1;
			// 1: receptor || 2: ligand
            if(state == true) {
            	$("#switch-pdna" + oid).bootstrapSwitch("state", false);
            	if(id == 1){
					$('input#param-receptor').val($('#file-input' + id).val());
					$('input#param-ligand').val($('#file-input' + oid).val());
				}else{
					$('input#param-receptor').val($('#file-input' + id).val());
					$('input#param-ligand').val($('#file-input' + oid).val());
				}
            }else{ 
            	$("#switch-pdna" + oid).bootstrapSwitch("state", true);
				if(id == 1){
					$('input#param-receptor').val($('#file-input' + oid).val());
					$('input#param-ligand').val($('#file-input' + id).val());
				}else{
					$('input#param-receptor').val($('#file-input' + oid).val());
					$('input#param-ligand').val($('#file-input' + id).val());
				}
            }
        });
    }
    
    return {
        //main function to initiate the module
        init: function () {
            handleBootstrapSwitch();
        }
    };

}();



var ValidateForm = function() {

    var handleForm = function() {

        $('#pdna-docking').validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: [],
            rules: {
                project: {
                    required: true
                }
            },

            invalidHandler: function(event, validator) { //display error alert on form submit
                $('.err-nd', $('#pdna-docking')).show();
                $('.warn-nd', $('#pdna-docking')).hide();
            },

            highlight: function(element) { // hightlight error inputs
                $(element)
                    .closest('.form-group').addClass('has-error'); // set error class to the control group
            },

            success: function(label, e) {
                $(e).parent().removeClass('has-error');
                $(e).parent().parent().parent().removeClass('has-error');
            },

            errorPlacement: function(error, element) {
                return true;
            },

            submitHandler: function(form) {
                $('.warn-nd', $('#pdna-docking')).hide();
                $('.err-nd', $('#pdna-docking')).hide();
                var data = $('#pdna-docking').serialize();
				data = data.replace(/%5B/g,"[");
                data = data.replace(/%5D/g,"]");
                //console.log(data);
                location.href = "/tools/compute.php?" + data;
            }
        });

        // rules by ID instead of NAME
        /*$("#params_nuclr_width").rules("add", {required:true});
        $("#params_nuclr_minoverlap").rules("add", {required:true});*/

        $('#npdna-docking input').keypress(function(e) {
            if (e.which == 13) {
                if ($('#pdna-docking').validate().form()) {
                    $('#pdna-docking').submit(); //form validation success, call ajax form submit
                }
                return false;
            }
        });
    }

    return {
        //main function to initiate the module
        init: function() {
            handleForm();
        }

    };

}();

jQuery(document).ready(function() {
   ComponentsBootstrapSwitch.init();
   ValidateForm.init();
});
