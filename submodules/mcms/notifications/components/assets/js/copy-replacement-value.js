$(function () {
  function getActiveOneRedactorElement() {
    var activeTab = $('.nav-tabs .active a').attr('href');
    return $(activeTab).find('textarea').first();
  }

  $(document)
    .on('redactor:replacements:paste', function (e, text) {
      tinymce.get(getActiveOneRedactorElement().attr('id')).insertContent(text);
    })
    .on('click', '.copy-replacements-value', function (e) {
      e.preventDefault();
      var $this = $(this);
      var isCollapse = $this.parents('.collapse').length > 0;
      $(document).trigger('redactor:replacements:paste', $this.data('text'));
      if (!isCollapse) {
        $('.modal-header button.close').trigger('click');
      }
    })
  ;

  $(document).on("show.bs.modal", function() {
    $('.modal-dialog').css('width', $('.btn-replacement').data('width'));
  });
});