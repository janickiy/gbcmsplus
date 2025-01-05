<?php
use mcms\partners\components\api\GetProjectName;
$zipName = (new GetProjectName())->getWcZipFileName();
?>

<div class="collapse-content">
	<div class="code__partial-title">
		<span><?= Yii::_t('sources.single_javascript') ?></span>
		<small class="small_text"><i class="icon-like"></i> <?= Yii::_t('sources.recommended') ?></small>
	</div>
	<div class="row">
		<div class="col-xs-6">
			<div class="">

				<p><?= Yii::_t('sources.single_javascript_text_1') ?></p>
				<p class="small_text"><?= Yii::_t('partners.sources.single_javascript_text_2') ?></p>
			</div>
		</div>
		<div class="col-xs-6">
			<code>
				&lt;script&gt;
          (function(i, s, o, g, r, a, m) { i[r] = i[r] || function() { (i[r].q = i[r].q || []).push(arguments) };
            a = s.createElement(o), m = s.getElementsByTagName(o)[0]; a.async = 1; a.src = g; m.parentNode.insertBefore(a, m)
          })
          (window, document, 'script', '<?= str_replace(["http://","https://"],"//",$domain) ?>js/embed.js?hash=<span class="hash"><?= isset($hash) ? $hash : '' ?></span>', 'wc');
          wc('start', '<span class="hash"><?= isset($hash) ? $hash : '' ?></span>', {});
        &lt;/script&gt;
      </code>
		</div>
	</div>
<br>
	<div class="code__partial-title">
		<span><?= Yii::_t('sources.single_php') ?></span>
	</div>
	<div class="row">
		<div class="col-xs-6">

			<div class="code__partial-list">
				<ol>
					<li><?= Yii::_t('sources.download_our_script') ?>
						<div class="code__partial-load">
							<i class="icon-zipicon2"></i>
							<a target="_blank" href="<?= Yii::getAlias('@uploadUrl/promo/') . $zipName ?>" data-pjax="0"><?= Yii::_t('sources.download_wapclick_zip', ['fileName' => $zipName]) ?></a>
						</div>

					</li>
					<li><span><?= Yii::_t('sources.single_php_text_1') ?></span>
					</li>
					<li><span><?= Yii::_t('sources.single_php_text_2') ?></span>
					</li>
					<li><span><?= Yii::_t('sources.check_script_work') ?> <a class="check" target="_blank" href="<?= isset($url) ? $url : '' ?>/wc/check.php"><?= isset($url) ? $url : '' ?>/wc/check.php</a></span>
					</li>
				</ol>
			</div>
		</div>
		<div class="col-xs-6">
			<code>
				include 'wc/WC.php';
				WC::redirect('<?= $domain ?>', '<?= isset($hash) ? $hash : '' ?>');
			</code>
		</div>
	</div>
</div>