$(function () {

  $("#menu-toggle").click(function (e) {
    e.preventDefault();
    $("#wrapper").toggleClass("toggled");
  });
  $("#menu-toggle-2").click(function (e) {
    e.preventDefault();
    $("#wrapper").toggleClass("toggled-3");
    $('#menu ul').hide();
  });

  //Меню
  var a = $('#sidebar-wrapper');
  $(window).scroll(function () {

    $(window).scrollTop() > $('.category').height() + $('.navbar').height() && $('#page-content-wrapper').height() + $('.navbar').height() + $('.category').height() > a.height() ? a.addClass("fixed") : a.removeClass("fixed")
  });

  $(document).on("submit", '.ticket-message-form', function (e) {
    e.preventDefault();
    var form = $(this);
    var id = form.data('id');
    $.ajax({
      url: form.attr('action'),
      type: 'post',
      data: form.serialize()
    }).done(function (result) {
      if (result['result'] == true) {
        form.parent('div').before(result['message']);
        $('.redactor-editor').html('');
        $('#files' + id).val('');
        $('#images' + id).html('');
      }
    });
  });

  $(document).on("click", '.delete-file', function (e) {
    e.preventDefault();
    var id = $(this).data('id');
    $.ajax({
      url: $(this).data('url')
    }).done(function (result) {
      if (result == true) {
        id ? $('#images' + id).empty() : $('#ticket-images').empty();
      }
    });
  });


  $('.steps__footer a').click(function() {
    $('a[href="#'+$(this).data('show')+'"]').tab('show');
  });

  $('.steps .nav-tabs li a').click(function (e) {
    e.preventDefault();
  });

  var url = document.location.toString();
  if (url.match('#')) {
    $('.nav-tabs a[href=#'+url.split('#')[1]+']').tab('show') ;
  }

  $('.settings, .code').click(function(e) {
    e.preventDefault();

    var wrapper = $(this).parents('.links-item__head'),
      container = wrapper.next('.links-item__hidden'),
      dataTarget = $(this).data('action'),
      dataSource = $(this).data('source'),
      hasActive = wrapper.hasClass(dataTarget);

    if (hasActive) {
      container.slideUp(300, function(){
      }).removeClass(dataTarget);
      wrapper.removeClass(dataTarget);

    } else {
      container.slideUp(300).removeClass('scale code');
      wrapper.removeClass('scale code');
      $.ajax({
        url: "/partners/sources/" + dataTarget + "/",
        type: 'post',
        data: {
          'source' : dataSource
        },
        success: function(data){
          container.html(data);
          wrapper.addClass(dataTarget);
          container.slideDown(500);
          $('.adstype_label').bind('click', function(e) {
            var radio = $('input[name=adstype]:checked');
            var adstype = radio.val(),
              source = radio.data('source');
            $.ajax({
              url: "/partners/sources/edit/",
              type: 'post',
              data: {
                'source' : source,
                'adstype' : adstype
              }
            });            
          });
        }
      });
    }
  });

  $('.collapse_tr-toggle').click(function(e) {
    e.preventDefault();

    var source = $(this).data('source'),
      $this = $(this),
      parentTr = $this.closest('tr');

    if ($this.hasClass('active')) {
      $this.toggleClass('active');
      parentTr.next('tr.collapse_tr').remove();
    } else {
      $.ajax({
        url: "/partners/links/get-link/",
        type: 'post',
        data: {
          'source' : source
        },
        success: function(result){
          parent_tr = $this.parents('tr');
          $('.collapse_tr').remove();
          $('.collapse_tr-toggle').not(this).removeClass('active');
          $this.hasClass('active') ? parent_tr.next('tr').remove() : parent_tr.after(result);
          $this.toggleClass('active');
          var clipboard = new Clipboard('.copy-button');
        }
      });
    }
  });
});