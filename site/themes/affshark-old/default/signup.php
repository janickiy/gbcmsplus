<?php
/**
 * @var \yii\web\View $this
 * @var \mcms\pages\models\Page $page
 * @var \yii\web\AssetBundle $asset
 */

use mcms\common\multilang\LangAttribute;


/** @var \mcms\partners\Module $modulePartners */
$modulePartners = Yii::$app->getModule('partners');

/** @var \mcms\pages\Module $pagesModule */
$pagesModule = Yii::$app->getModule('pages');

if ($favicon = $modulePartners->api('getFavicon')->getResult()) {
    $this->registerLinkTag(['rel' => 'shortcut icon', 'type' => 'image/x-icon', 'href' => $favicon]);
}

$this->registerCssFile($asset->baseUrl . '/css/onepage2.css', [
    'depends' => 'mcms\partners\assets\landings\affshark\LandingAsset',
    'type' => 'text/css',
]);

$this->title = $this->title instanceof LangAttribute && $this->title->getCurrentLangValue()
    ? $this->title
    : $page->getPropByCode('page_title')->multilang_value;

?>
<body id="page-top" data-spy="scroll" data-target="#fixed-collapse-navbar" data-offset="120" class="push-body"
      style="    background-image: url(<?= $asset->baseUrl ?>/bg_signup.jpg);
              background-size: cover; color:white;">

<?= $page->getPropByCode('client_css')->multilang_value ?>


    <form action="/">

        <div class="container">
            <div class="row" style=" margin-top: 30px;">
                <div class="modal-header"
                     style=" padding: 9px!important;background: rgba(21, 21, 21, 0.56);border-radius: 5px 5px 0px 0px;border-bottom:0px;">
                    <button type="button" class="modal_close close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">×</span></button>
                    <h1 class="modal-title text-center" id="myModalLabel">Sign up</h1>
                </div>
                <div class="flowlu-form"
                     style="max-width: none!important;-webkit-overflow-scrolling: touch;border-radius: 0px 0px 5px 5px;background: rgba(21, 21, 21, 0.56);">
                    <input type="hidden" name="manager_id" value=""><input type="hidden" name="source_id"
                                                                           value="1"><input type="hidden" name="name"
                                                                                            value="Wb Form Ask question 2"><input
                            type="hidden" id="flowlu_host" value="https://timoshuck.flowlu.ru/"><textarea name="nspm"
                                                                                                          style="display:none !important;"></textarea>
                    <div class="flowlu-row">
                        <table width="100%" border="0" class="flowlu-table-rows" style="border-collapse: collapse;">
                            <tr>
                                <td width="48%"><label class="flowlu-label" for="flowlu_contact_name">Name <span
                                                class="flowlu-required">*</span></label> <input type="text"
                                                                                                required="required"
                                                                                                id="flowlu_contact_name"
                                                                                                placeholder="Your name"
                                                                                                name="Name"
                                                                                                class="flowlu-input"
                                                                                                style="display: inherit;width: 100%; margin-bottom: 16px;border-radius: 5px;">
                                </td>
                                <td width="4%">&nbsp;</td>
                                <td width="48%"><label class="flowlu-label" for="flowlu_contact_skype">Skype or ICQ
                                        <span class="flowlu-required">*</span></label> <input type="text"
                                                                                              required="required"
                                                                                              name="Skype/icq"
                                                                                              placeholder="Your Skype or ICQ"
                                                                                              class="flowlu-input"
                                                                                              style="display: inherit;width: 100%; margin-bottom: 16px;border-radius: 5px;">
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="flowlu-row">
                        <table width="100%" border="0" class="flowlu-table-rows" style="border-collapse: collapse;">
                            <tr>
                                <td width="48%"><label class="flowlu-label" for="flowlu_contact_company">Currency <span
                                                class="flowlu-required">*</span></label> <select
                                            id="flowlu_contact_company" name="Currency" class="flowlu-input"
                                            style="color: rgba(255, 255, 255, 0.82);">
                                        <option value="rub">USD</option>
                                        <option value="usd" selected="">EUR</option>
                                        <option value="eur">RUB</option>
                                    </select></td>
                                <td width="4%">&nbsp;</td>
                                <td width="48%"><label class="flowlu-label" for="flowlu_contact_email">E-mail <span
                                                class="flowlu-required">*</span></label> <input type="text"
                                                                                                required="required"
                                                                                                id="flowlu_contact_email"
                                                                                                placeholder="my@mail.com"
                                                                                                name="E-mail"
                                                                                                class="flowlu-input"
                                                                                                style="display: inherit;width: 100%; margin-bottom: 16px;border-radius: 5px;">
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="flowlu-row">
                        <table width="100%" border="0" class="flowlu-table-rows" style="border-collapse: collapse;">
                            <tr>
                                <td width="100%">
                                    <div class="flowlu-row"><label class="flowlu-label" for="flowlu_contact_company">Countries
                                            you would like to promote</label>
                                        <dl class="dropdown" style=" margin-top: 0px;">
                                            <dt><a href="#" style=" height: 37px;border-radius: 5px;"> <span
                                                            class="hida" style=" font-weight: 300;">Select</span>
                                                    <p class="multiSel"></p></a></dt>
                                            <dd>
                                                <div class="mutliSelect">
                                                    <ul>
                                                        <li><input style="margin-right: 5px;" type="checkbox"
                                                                   id="Russia" value="Russia"/>Russia
                                                        </li>
                                                        <li><input style="margin-right: 5px;" type="checkbox"
                                                                   id="Azerbaijan" value="Azerbaijan"/>Azerbaijan
                                                        </li>
                                                        <li><input style="margin-right: 5px;" type="checkbox"
                                                                   id="Romania" value="Romania"/>Romania
                                                        </li>
                                                        <li><input style="margin-right: 5px;" type="checkbox"
                                                                   id="Poland" value="Poland"/>Poland
                                                        </li>
                                                        <li><input style="margin-right: 5px;" type="checkbox"
                                                                   id="Switzerland" value="Switzerland"/>Switzerland
                                                        </li>
                                                        <li><input style="margin-right: 5px;" type="checkbox" id="Czech"
                                                                   value="Czech republic"/>Czech republic
                                                        </li>
                                                        <li><input style="margin-right: 5px;" type="checkbox"
                                                                   id="Hungary" value="Hungary"/>Hungary
                                                        </li>
                                                        <li><input style="margin-right: 5px;" type="checkbox"
                                                                   id="Netherlands" value="Netherlands"/>Netherlands
                                                        </li>
                                                        <li><input style="margin-right: 5px;" type="checkbox"
                                                                   id="United" value="United Kingdom"/>United Kingdom
                                                        </li>
                                                        <li><input style="margin-right: 5px;" type="checkbox" id="Kenya"
                                                                   value="Kenya"/>Kenya
                                                        </li>
                                                        <li><input style="margin-right: 5px;" type="checkbox"
                                                                   id="Brazil" value="Brazil"/>Brazil
                                                        </li>
                                                        <li><input style="margin-right: 5px;" type="checkbox"
                                                                   id="Estonia" value="Estonia"/>Estonia
                                                        </li>
                                                        <li><input style="margin-right: 5px;" type="checkbox" id="Iraq"
                                                                   value="Iraq"/>Iraq
                                                        </li>
                                                        <li><input style="margin-right: 5px;" type="checkbox"
                                                                   id="Portugal" value="Portugal"/>Portugal
                                                        </li>
                                                        <li><input style="margin-right: 5px;" type="checkbox" id="Egypt"
                                                                   value="Egypt"/>Egypt
                                                        </li>
                                                    </ul>
                                                </div>
                                            </dd>
                                        </dl>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <input class="flowlu-input" type="hidden" name="_csrf"
                           value="<?= Yii::$app->request->getCsrfToken() ?>"/>
                    <div class="flowlu-row" style="display:none;"><input type="hidden" id="flowlu_contact_phone"
                                                                         name="Countries" class="flowlu-input" value="">
                    </div>
                    <div class="flowlu-row"><label class="flowlu-label" for="flowlu_description">Message</label>
                        <textarea id="flowlu_description" rows="5" style="width: 100%; height: 37px;border-radius: 5px;"
                                  name="description" class="flowlu-input" value=""></textarea></div>
                    Please note: fields marked with <span class="flowlu-required">*</span> should be filled
                    <div class="flowlu-notification" style=" color: #fa9d40;">&nbsp;</div>
                    <div class="flowlu-row flowlu-row-submit">
                        <button id="prov" class="btn bounce-green flowlu-submit"
                                style=" background: #fa9d40; border: none;width: 156px; text-align: center; font-size: 24px;">
                            Send
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

<?= $this->registerJs('function getSelectedValue(a) {
    return $("#" + a).find("dt a span.value").html()
}!(function(w, d, c) {
    (w[c] = w[c] || []).push(function(flowluHost) {
        try {
            new FlowluForm.init({
                id: 9084,
                token: \'2daf7af02ff3421eb9387acea646052b\',
                host: flowluHost
            });
        } catch (e) {}
    });
    var n = d.getElementsByTagName("script")[0],
        s = d.createElement("script"),
        f = function() {
            n.parentNode.insertBefore(s, n);
        };
    s.type = "text/javascript";
    s.async = true;
    s.src = "/js/affsark-form.js";
    if (w.opera == "[object Opera]") {
        d.addEventListener("DOMContentLoaded", f, false);
    } else {
        f();
    }
})(window, document, "flowlu_forms");;
var pered, desc;
$("#prov").click(function() {
    pered = $(".multiSel").text(), 0 == pered && $("#flowlu_contact_phone").val("no one countries"), $("#flowlu_contact_phone").val(pered), desc = $("#flowlu_description").val(), $("#flowlu_description").val(desc)
}), $(".dropdown dt a").on("click", function() {
    $(".dropdown dd ul").slideToggle("fast")
}), $(".dropdown dd ul li a").on("click", function() {
    $(".dropdown dd ul").hide()
}), $(document).bind("click", function(a) {
    var b = $(a.target);
    b.parents().hasClass("dropdown") || $(".dropdown dd ul").hide()
});
var title, html;
$(\'.mutliSelect input[type="checkbox"]\').on("click", function() {
    if (title = $(this).closest(".mutliSelect").find(\'input[type="checkbox"]\').val(), title = $(this).val() + ",", $(this).is(":checked")) html = \'<span title="\' + title + \'">\' + title + "</span>", $(".multiSel").append(html), $(".hida").hide();
    else {
        $(\'span[title="\' + title + \'"]\').remove();
        var a = $(".hida");
        $(".dropdown dt a").append(a)
    }
}), $(".modal_close").click(function() {
    window.location.href = "/"
}); ') ?>