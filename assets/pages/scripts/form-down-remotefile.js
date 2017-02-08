var FormDownRemoteFile = function () {

	 return {
        //main function to initiate the module
        init: function () { 
			
			$('.down-form').validate({
            	errorElement: 'span', //default input error message container
            	errorClass: 'help-block', // default input error message class
            	focusInvalid: false, // do not focus the last invalid input
            	rules: {
                	url: {
                    	required: true,
						url: true
                	},
            	},

            	messages: {
                	url: {
                    	required: "Please insert an url."
                	}
            	},

            	highlight: function(element) { // hightlight error inputs
                	$(element)
                    	.closest('.form-group').addClass('has-error'); // set error class to the control group
            	},

            	success: function(label) {
                	label.closest('.form-group').removeClass('has-error');
                	label.remove();
            	},

            	errorPlacement: function(error, element) {

					if (element.closest('.input-icon').size() === 1) {
                    	error.insertAfter(element.closest('.input-icon'));
                	} else {
                    	error.insertAfter(element);
                	}

            	},

            	submitHandler: function(form) {
					
					$('#btn-down-remote').hide();
					$('.progress-bar-down').show();
					$('.alert.alert-danger').fadeOut(300);
				
					$.ajax({
           				type: "POST",
           				url: "/applib/downloadRemote.php",
           				data: $('.down-form').serialize(),
						xhrFields: {
							onprogress: function(e) {
								var output = e.target.responseText.split(/\n/);
								$('.progress-bar-down .progress-bar').css('width', output[output.length - 2] + '%');
							}
						}, 
           				success: function(data) {
							var output = data.split(/\n/);
							if((output[output.length - 2]) == 'ok') {
								$('.progress-bar-down .progress-bar').css('width', '100%');
								setTimeout(function(){ alert('file successfully downloaded'); }, 500);	
								//setTimeout(function(){ location.href="uploadForm2.php"; }, 500);	
							}else{
								$('#btn-down-remote').show();
								$('.progress-bar-down').hide();
								$('.alert.alert-danger').fadeIn(300);
							}
						}
         			});

            	}
        	});
		}
	}

}();

jQuery(document).ready(function() {    
   FormDownRemoteFile.init();
});
