<?php
/** @var array $groupedElements */
/** @var string $inputName */
?>

<div class="filter">
  <div class="filter-header"></div>
  <div class="filter-body filter-body_left">

    <div class="filter-body_selected">

    </div>
    <div class="filter-body_deselected">
      <?php $groupSelector = 0; foreach($groupedElements as $groupName => $elements):  $groupSelector++; ?>

          <section class="cb_group">

            <div class="checkbox-select-group cb_group-name">
              <label class="checkbox cb_g">
                <input type="checkbox" name="checkbox" id="cb_g_c<?= $groupSelector ?>">
                <i></i>
              </label>
              <div class="checkbox-select-group__label cb_group-name">
                <span class="checkbox-select-group__name"><?= $groupName ?></span>
                <span class="icon-down2 checkbox-select-group__icon"></span>
              </div>

            </div>

            <div class="cb_group-list"<?php if($groupSelector == 1): ?> style="display: block;"<?php endif;?>data-opened="<?=($groupSelector == 1) ? 1 : 0 ?>">
              <?php foreach($elements as $id => $name): ?>

                <div class="col col-6">
                  <label class="checkbox cb_g_c<?= $groupSelector ?>">
                    <input type="checkbox" name="<?= $inputName ?>" value="<?= $id ?>">
                    <i></i><?= $name ?></label>
                </div>
              <?php endforeach;?>
            </div>
            <div class="clearfix"></div>
          </section>

      <?php endforeach;?>
    </div>
  </div>
</div>
