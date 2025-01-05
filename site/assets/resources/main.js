(function($){
    $(document).on('click', '.btn-ajax-modal', function () {

        //check if the modal is open. if it's open just reload content not whole modal
        //also this allows you to nest buttons inside of modals to reload the content it is in
        //the if else are intentionally separated instead of put into a function to get the
        //button since it is using a class not an #id so there are many of them and we need
        //to ensure we get the right button and content.
        if ($($(this).data('target')).data('bs.modal').isShown) {
            $($(this).data('target')).find('#modalContent')
                .load($(this).attr('value'));

            $($(this).data('target') + ' .modal-header h3').html($(this).data('title'));
        } else {
            //if modal isn't open; open it and load content
            $($(this).data('target')).modal('show');
                //.find('#modalContent')
                //.load($(this).attr('url'));

            $($(this).data('target') + ' .modal-header h3').html($(this).data('title'));
            $($(this).data('target') + ' .modal-body').load($(this).data('url'));
        }
    });

})(jQuery);