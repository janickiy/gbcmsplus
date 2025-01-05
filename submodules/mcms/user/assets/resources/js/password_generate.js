$(function () {
    var $passwordInput = $('#userform-password'),
        $generateButton = $('#pass-generate'),
        $showButton = $('#pass-show');

    $generateButton.on('click', function() {
        var pass = $.passGen({'length' : 12})
        $passwordInput.val(pass);
    });
    $showButton.on('click', function() {
        $(this).find('span').toggleClass('fa-low-vision').toggleClass('fa-eye');
        switch ($passwordInput.attr('type')) {
            case 'password' :
                $passwordInput.attr('type', 'text');
                break;
            case 'text' :
                $passwordInput.attr('type', 'password');
                break;
        }
    });
});