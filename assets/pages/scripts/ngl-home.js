
var fileName = '';

function openNGL(path, label){
  $('#viewport').html('');
  $('#modalNGL .modal-title').html(label);
  $('#modalNGL').modal({ show: 'true' });
  fileName = '/files/' + path;
}

$(document).ready(function() {

  $('#modalNGL').on('shown.bs.modal', function (e) {
    stage = new NGL.Stage( "viewport", { backgroundColor:"#ddd" } );
    stage.removeAllComponents();
    stage.loadFile( fileName, { defaultRepresentation: false } ).then( function( o ){

      o.addRepresentation( "cartoon", {
        color: "residueindex", aspectRatio: 4, scale: 1
      } );
      o.addRepresentation( "base", {
        sele: "*", color: "resname"
      } );
      o.addRepresentation( "ball+stick", {
        sele: "hetero and not(water or ion)", scale: 3, aspectRatio: 1.5
      } );
      o.centerView(!1);

    } );
  });

  $('#modalNGL').on('hidden.bs.modal', function (e) {
    $('#viewport').html('');
  });

  function handleResize(){ if(typeof stage != 'undefined') stage.handleResize(); }
  window.addEventListener( "resize", handleResize, false );

});
