<?php
use yii\helpers\Html;

/* @var $content string */
/* @var $banner \mcms\promo\models\Banner */
$banner = $this->params['banner'];
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <meta name="keywords" content="">
  <title><?= Html::encode($this->title) ?></title>
</head>
<body>

<div style="
  background: rgba(0,0,0, <?= $banner->opacity ? $banner->opacity / 100 : '0.6' ?>);
  position: fixed;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  z-index: 10000;
  overflow: hidden;" id="wc_f_02234">
  <a class="close-xuz-block" id="close" href="javascript:void"
     style="position: absolute;
     <?= $banner->getCrossPosition()[0] ?>: 0;
     <?= $banner->getCrossPosition()[1] ?>: 0;
     display: none;
     width: 9%;
     height: auto;
     max-width: 50px;
     min-width: 25px;
  ">
    <img style="width: 100%; height: auto; vertical-align: top;" src="data:image/jpeg;base64,/9j/4QAYRXhpZgAASUkqAAgAAAAAAAAAAAAAAP/sABFEdWNreQABAAQAAABbAAD/4QMraHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLwA8P3hwYWNrZXQgYmVnaW49Iu+7vyIgaWQ9Ilc1TTBNcENlaGlIenJlU3pOVGN6a2M5ZCI/PiA8eDp4bXBtZXRhIHhtbG5zOng9ImFkb2JlOm5zOm1ldGEvIiB4OnhtcHRrPSJBZG9iZSBYTVAgQ29yZSA1LjMtYzAxMSA2Ni4xNDU2NjEsIDIwMTIvMDIvMDYtMTQ6NTY6MjcgICAgICAgICI+IDxyZGY6UkRGIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyI+IDxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PSIiIHhtbG5zOnhtcD0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLyIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bXA6Q3JlYXRvclRvb2w9IkFkb2JlIFBob3Rvc2hvcCBDUzYgKFdpbmRvd3MpIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjhCMUJDNkU1MTQyOTExRTZBMERFQzkxNkMwNEUwQkE4IiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOjhCMUJDNkU2MTQyOTExRTZBMERFQzkxNkMwNEUwQkE4Ij4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6OEIxQkM2RTMxNDI5MTFFNkEwREVDOTE2QzA0RTBCQTgiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6OEIxQkM2RTQxNDI5MTFFNkEwREVDOTE2QzA0RTBCQTgiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz7/7gAOQWRvYmUAZMAAAAAB/9sAhAABAQEBAQEBAQEBAgEBAQICAQEBAQICAgICAgICAwICAgICAgMDAwMEAwMDBAQFBQQEBgYGBgYHBwcHBwcHBwcHAQEBAQICAgQDAwQGBQQFBgcHBwcHBwcHBwcHBwcHBwcHBwcHBwcHBwcHBwcHBwcHBwcHBwcHBwcHBwcHBwcHBwf/wAARCAAcABwDAREAAhEBAxEB/8QAZQAAAgMBAQAAAAAAAAAAAAAAAAECBwgJCgEBAAAAAAAAAAAAAAAAAAAAABAAAAUDBAIBBAMAAAAAAAAAARECAwQSBQYAITEHQVEIYYGhIlITFxEBAAAAAAAAAAAAAAAAAAAAAP/aAAwDAQACEQMRAD8A8OShEFCWgVavegK1e9AVq96BK5UHBmAiRkHkdgEQL34DcdtB0m6owy2/OGwq/wBQko62zzAHrRYp/wAjZDcRq03yDJdagQrDkISJEZDt3opTDeQpS30hQ8QAl0Azz8h89lRHQ6BxrBpnU/WnWc2pGE5E0hN8uN5aaWy5kOTyEoSp+W6064DYAItMtqBDRp/ZQZe3MySRnSSS5Kmrkj2P3oGPKvuAj9B552440Fzdn92XzsW1Yxh8G0xME60wptAYx11i4LCC3LFhDUu6TXXKnZsyUKTcffFSyJCSSBCCznui9dlYLiWL5pZ4t9yrCRatVg7NfF1N6XY2mXENWGe6lf8AVMaaWpAtOOpU60ACgBpEkhTJhzUH8qvzUXJU+KS886CSiMaToMaKuSqEjLbjQR0BoDQf/9k=" alt="" />
  </a>
  <?= $content ?>
</div>
<script type="text/javascript">
  setTimeout('document.getElementById("close").style.display = "block"', <?= $banner->timeout ? $banner->timeout * 1000 : 0 ?>);
</script>
</body>
</html>