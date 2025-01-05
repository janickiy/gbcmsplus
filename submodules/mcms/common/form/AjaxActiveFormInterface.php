<?php

namespace mcms\common\form;

interface AjaxActiveFormInterface
{
    const AJAX_FORM_ATTRIBUTE = 'data-ajax-enabled';
    const AJAX_SUCCESS_REPLACE = '\'{{ajaxSuccess}}\'';
    const AJAX_ERROR_REPLACE = '\'{{ajaxError}}\'';
    const AJAX_BEFORE_SEND_REPLACE = '\'{{ajaxBeforeSend}}\'';
    const AJAX_COMPLETE_REPLACE = '\'{{ajaxComplete}}\'';
    const FORM_ID_REPLACE = '{{id}}';

    function registerClientFunctions();
}