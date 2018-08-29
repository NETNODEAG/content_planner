(function ($) {

  $(document).ready(function(){

    /*
      Drag n Drop functionality
      Inpired by https://neliosoftware.com/blog/native-drag-and-drop-with-html5/
     */
    $('.kanban-entry').draggable({
      helper: 'clone'
    });


    $('.kanban-column').droppable({

      accept: '.kanban-entry',
      hoverClass: 'hovering',

      //on drop
      drop: function( ev, ui ) {

        ui.draggable.detach();
        $( this ).append( ui.draggable );

        //Get NID from draggable object
        var nid = $(ui.draggable[0]).data('nid');

        //Get state_id from target column
        var stateID = $(this).data('state_id');

        if(stateID && nid) {

          //Generate URL for AJAX call
          var url = '/admin/content-kanban/update-node-workflow-state/' + nid + '/' + stateID;


          $.ajax({
            'url': url,
            'success': function(result) {

              if(!result.success) {
                alert(result.message);
              }
            },
            'error': function(xhr, status, error) {
              alert('An error occured during the update of the desired node. Please consult the watchdog.');
            }
          });

        }

      }
    });

  });



})(jQuery);
