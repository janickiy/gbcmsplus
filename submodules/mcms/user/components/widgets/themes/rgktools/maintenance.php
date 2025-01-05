<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#fff"/>
    <meta name="description" content="">
    <meta name="keywords" content="">
    <title>RGKTools</title>
    <link media="all" rel="stylesheet" href="/css/maintenance/rgktools.css">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700" rel="stylesheet">
</head>
<body>
<div class="e-page">
    <div class="e-logo"><img src="<?= Yii::$app->getModule('pages')->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'logo',
            'view' => 'prop_img'
        ])->getResult() ?>" alt=""></div>
    <div class="e-popup">
        <div class="e-popup__text">На данный момент ведутся технические работы.</div>
        <div class="e-popup__text e-popup__text2">Приносим извинения за неудобства</div>
    </div>
</div>
</body>
</html>