(function ($) {

  $(document).ready(function(){

    //Jump to current Calendar
    if(!document.location.hash) {
      var currentDate = new Date();
      var calendarID = currentDate.getFullYear() + '-' + (currentDate.getMonth() + 1);
      window.location.href = "#" + calendarID;
    }

    /*
      Drag n Drop functionality
      Inpired by https://neliosoftware.com/blog/native-drag-and-drop-with-html5/
     */
    $( '.calendar-entry' ).draggable({
      helper: 'clone'
    });


    $( '.content-calendar .droppable' ).droppable({

      accept: '.calendar-entry',
      hoverClass: 'hovering',

      //on drop
      drop: function( ev, ui ) {

        ui.draggable.detach();
        $( this ).append( ui.draggable );

        //Get NID from draggable object
        var nid = $(ui.draggable[0]).data('nid');

        //Get date from target cell
        var date = $(this).data('date');

        if(date && nid) {

          //Generate URL for AJAX call
          var url = '/admin/content-calendar/update-node-publish-date/' + nid + '/' + date;

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
