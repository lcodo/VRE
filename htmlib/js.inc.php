<!--[if lt IE 9]>
<script src="/assets/global/plugins/respond.min.js"></script>
<script src="/assets/global/plugins/excanvas.min.js"></script>
<![endif]-->
<?php
switch(pathinfo($_SERVER['PHP_SELF'])['filename']){
	case 'adminUsers':
	case 'index':
	case 'dashboard':
	case 'uploadForm':
	case 'uploadForm2':
	case 'editFile':
	?>
	<script src="/htmlib/globals.js.inc.php"></script>
	<?php break; 
}
?>
        <!-- BEGIN CORE PLUGINS -->
        <script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
        <script src="/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
        <script src="/assets/global/plugins/js.cookie.min.js" type="text/javascript"></script>
        <script src="/assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
        <script src="/assets/global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
        <script src="/assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js" type="text/javascript"></script>
        <!-- END CORE PLUGINS -->
        <!-- BEGIN PAGE LEVEL PLUGINS -->
		<?php
		switch(pathinfo($_SERVER['PHP_SELF'])['filename']){
			case 'resetPassword':
			case 'index': ?>
			<?php if(dirname($_SERVER['PHP_SELF']) != '/workspace'){ ?>	
			<script src="/assets/global/plugins/jquery-validation/js/jquery.validate.min.js" type="text/javascript"></script>
        	<script src="/assets/global/plugins/jquery-validation/js/additional-methods.min.js" type="text/javascript"></script>
			<script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
			<?php } else { ?>
			<script src="/assets/global/scripts/jquery.dataTables.min.js" type="text/javascript"></script>
        	<script src="/assets/global/scripts/dataTables.treeTable.js" type="text/javascript"></script>
        	<script src="/assets/global/scripts/jquery.treetable.js" type="text/javascript"></script>
        	<script src="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js" type="text/javascript"></script>
			<script src="/assets/global/plugins/jquery-knob/js/jquery.knob.js" type="text/javascript"></script>
			<script src="/assets/global/plugins/ngl.js" type="text/javascript"></script>
			<script src="/assets/global/plugins/bootstrap-toastr/toastr.min.js" type="text/javascript"></script>
			<?php } ?>
			<?php break; 
			case 'lockScreen': ?>		
			<script src="/assets/global/plugins/jquery-validation/js/jquery.validate.min.js" type="text/javascript"></script>
        	<script src="/assets/global/plugins/jquery-validation/js/additional-methods.min.js" type="text/javascript"></script>
			<?php break; 
			case 'repositoryList': ?>
			<script src="/assets/global/scripts/datatable.js" type="text/javascript"></script>
    		<script src="/assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
	        <script src="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js" type="text/javascript"></script>
			<?php break; 	
			case 'usrProfile': ?>
			<script src="/assets/global/plugins/jquery-validation/js/jquery.validate.min.js" type="text/javascript"></script>
        	<script src="/assets/global/plugins/jquery-validation/js/additional-methods.min.js" type="text/javascript"></script>
			<script src="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js" type="text/javascript"></script>
			<?php break; 
			case 'uploadForm': ?>
			<script src="/assets/global/plugins/dropzone/dropzone.min.js" type="text/javascript"></script>	
			<script src="/assets/global/plugins/jquery-validation/js/jquery.validate.min.js" type="text/javascript"></script>
			<?php break; 
			case 'uploadForm2':
			case 'editFile': ?>
			<script src="/assets/global/plugins/jquery-validation/js/jquery.validate.min.js" type="text/javascript"></script>
			<?php break;
			case 'adminUsers': ?>
			<script src="/assets/global/scripts/datatable.js" type="text/javascript"></script>
        	<script src="/assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
        	<script src="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js" type="text/javascript"></script>
			<?php break;
			case 'dashboard': ?>
			<script src="/assets/global/plugins/counterup/jquery.waypoints.min.js" type="text/javascript"></script>
        	<script src="/assets/global/plugins/counterup/jquery.counterup.min.js" type="text/javascript"></script>
        	<script src="/assets/global/plugins/jquery-knob/js/jquery.knob.js" type="text/javascript"></script>
        	<script src="/assets/global/plugins/flot/jquery.flot.min.js" type="text/javascript"></script>
        	<script src="/assets/global/plugins/flot/jquery.flot.resize.min.js" type="text/javascript"></script>
        	<script src="/assets/global/plugins/flot/jquery.flot.categories.min.js" type="text/javascript"></script>
			<script src="/assets/global/plugins/flot/jquery.flot.threshold.min.js" type="text/javascript"></script>
			<script src="/assets/global/scripts/datatable.js" type="text/javascript"></script>
        	<script src="/assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
			<script src="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js" type="text/javascript"></script>
			<script src="/assets/global/plugins/jquery-easypiechart/jquery.easypiechart.min.js" type="text/javascript"></script>
			<script src="/assets/global/plugins/jquery.sparkline.min.js" type="text/javascript"></script>
			<?php break;
			case 'input':?>
				<?php if(dirname($_SERVER['PHP_SELF']) == '/tools/pydock'){ ?>
				<script src="/assets/global/plugins/jquery-validation/js/jquery.validate.min.js" type="text/javascript"></script>
				<script src="/assets/global/plugins/jquery-validation/js/additional-methods.min.js" type="text/javascript"></script>
				<script src="/assets/global/plugins/ngl.js" type="text/javascript"></script>
				<script src="/tools/pydock/assets/js/input.js" type="text/javascript"></script>
				<?php } ?>
				<?php if(dirname($_SERVER['PHP_SELF']) == '/tools/nucldynwf'){ ?>
				<script src="/assets/global/plugins/jquery-validation/js/jquery.validate.min.js" type="text/javascript"></script>
        		<script src="/assets/global/plugins/jquery-validation/js/additional-methods.min.js" type="text/javascript"></script>
        		<script src="/tools/nucldynwf/assets/js/input.js" type="text/javascript"></script>
				<?php } ?>

			<?php break;
			case 'output': ?>
				<?php if(dirname($_SERVER['PHP_SELF']) == '/tools/pydock'){ ?>
				<script src="/assets/global/plugins/ngl.js" type="text/javascript"></script>
				<script src="/assets/global/scripts/datatable.js" type="text/javascript"></script>
				<script src="/assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
				<script src="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js" type="text/javascript"></script>
				<script src="/tools/pydock/assets/js/output.js" type="text/javascript"></script>
				<?php } ?>
			<?php break;?>
		<?php } ?>
        <!-- END PAGE LEVEL PLUGINS -->
        <!-- BEGIN THEME GLOBAL SCRIPTS -->
        <script src="/assets/global/scripts/app.min.js" type="text/javascript"></script>
        <!-- END THEME GLOBAL SCRIPTS -->
        <!-- BEGIN PAGE LEVEL SCRIPTS -->
		<?php
		switch(pathinfo($_SERVER['PHP_SELF'])['filename']){
			case 'resetPassword': ?>
			<script src="/assets/pages/scripts/resetPassword.js" type="text/javascript"></script>	
			<?php break; 
			case 'index': ?>
			<?php if(dirname($_SERVER['PHP_SELF']) != '/workspace'){ ?>		
			<script src="/assets/pages/scripts/login.js" type="text/javascript"></script>
			<?php } else { ?>
			<script src="/assets/pages/scripts/datatables-page.js" type="text/javascript"></script>
			<script src="/assets/pages/scripts/components-knob-dials.js" type="text/javascript"></script>
			<script src="/assets/pages/scripts/run-tools.js" type="text/javascript"></script>
			<script src="/assets/pages/scripts/ngl-home.js" type="text/javascript"></script>
			<script src="/assets/pages/scripts/actions-home.js" type="text/javascript"></script>
			<?php } ?>
			<?php break; 
			case 'lockScreen': ?>	
			<script src="/assets/pages/scripts/lock.js" type="text/javascript"></script>	
			<?php break; 
			case 'usrProfile': ?>
			<script src="/assets/pages/scripts/profile.js" type="text/javascript"></script>
			<?php break; 
			case 'repositoryList': ?>
			<script src="/assets/pages/scripts/table-repository.js" type="text/javascript"></script>	
			<?php break;
			case 'uploadForm': ?>	
			<script src="/assets/pages/scripts/form-dropzone.js" type="text/javascript"></script>
			<script src="/assets/pages/scripts/form-down-remotefile.js" type="text/javascript"></script>
			<?php break;
			case 'uploadForm2': 
			case 'editFile': 
			?>	
			<script src="/assets/pages/scripts/form-validatefiles.js" type="text/javascript"></script>
			<?php break;
			case 'adminUsers': ?>
			<script src="/assets/pages/scripts/table-datatables-editable.js" type="text/javascript"></script>	
			<?php break; 
			case 'dashboard': ?>
			<script src="/assets/pages/scripts/dashboard.js" type="text/javascript"></script>	
			<?php break;?>
		<?php } ?>
        <!-- END PAGE LEVEL SCRIPTS -->
        <!-- BEGIN THEME LAYOUT SCRIPTS -->
        <?php
		switch(pathinfo($_SERVER['PHP_SELF'])['filename']){
			case 'index': 
			case 'help1': 
			case 'repositoryList':
			case 'experiment':
			case 'usrProfile':
			case 'uploadForm':
			case 'uploadForm2': 
			case 'editFile': 
			case 'adminUsers': 
			case 'dashboard':
			case 'input':
			case 'output': ?>
			<script src="/assets/layouts/layout/scripts/layout.min.js" type="text/javascript"></script>
			<script src="/assets/layouts/layout/scripts/main.js" type="text/javascript"></script>
			<?php break; ?>
		<?php } ?>
		<!-- END THEME LAYOUT SCRIPTS -->
    </body>

</html>

