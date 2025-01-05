<?php
/**
 * @var $data \mcms\pages\models\Page[]
 * @var $category \mcms\pages\models\Category
 */

?>
<?php $counter = 0;
foreach (array_chunk($data, 4) as $pages): $counter++; ?>
    <div class="row">
        <?php foreach ($pages as $page):
            $image = $page->getPropByCode('image');
            $email = $page->getPropByCode('email');
            $image = $image ? $image->getImageUrl() : null;
            $email = $email ? $email->multilang_value->getCurrentLangValue() : null;
            ?>
            <div class="col-sm-3 col-xs-12 <?php if ($counter == 2): $counter = 0; ?>col-sm-offset-3<?php endif; ?>">
                <div class="team-member-container">
                    <?php if ($image): ?>
                        <img src="<?= $image ?>" alt="Team member" class="team-member img-responsive">
                    <?php endif ?>
                    <div class="team-member-info vertical-center-sm">
                        <h3><?= $page->name ?></h3>
                        <h3><?= $page->text ?></h3>
                        <?php if (!empty($email)): ?>
                            <br/>
                            <h3><a href="mailto:<?= $email ?>"><?= $email ?></a></h3>
                        <?php endif; ?>
                    </div>
                    <div class="clicked_area"></div>
                </div>
            </div>
        <?php endforeach ?>
    </div>
<?php endforeach ?>
