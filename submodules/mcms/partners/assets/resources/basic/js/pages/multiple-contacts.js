$(document).ready(function () {
  var replaceNewFieldsName = function (node, number) {
    var name = node.attr('name');
    name = name.split('][');
    name[1] = 'n' + number;
    node.attr('name', name.join(']['));
  };

  var contactsWrapper = $('#contacts-wrapper');
  contactsWrapper.find('[data-id=""]').each(function () {
    var number = $(this).attr('data-number');
    $(this).find('[name]').each(function () {
      replaceNewFieldsName($(this), number);
    });
  });

  $('#add-contact').click(function (e) {
    e.preventDefault();

    var contactsTemplate = $('#contact-form-template').clone();

    var number = contactsWrapper.find('[data-number]:last').attr('data-number') || 0;
    number = parseInt(number) + 1;

    contactsTemplate.find('[data-number]').attr('data-number', number);

    contactsTemplate.find('.selectpicker').selectpicker('destroy');
    contactsTemplate.find('[data-name]').each(function (item) {
      var name = $(this).attr('data-name');
      name = name.split('][');
      name[1] = 'n' + number;
      $(this).attr('name', name.join(']['));
    });

    contactsTemplate.find('.selectpicker2').removeClass('selectpicker2').addClass('new-selectpicker');
    $('#add-contact').parent().before(contactsTemplate.html());
    $('.new-selectpicker').selectpicker();
  });

  contactsWrapper.on('click', '.close', function (e) {
    e.preventDefault();

    var contact = $(this).closest('.row');
    contact.remove();
  });
});