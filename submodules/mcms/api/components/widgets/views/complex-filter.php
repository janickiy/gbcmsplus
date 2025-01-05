<?php



/** @var string $id */
/** @var string $label */
?>

<div class="dropdown complex-filter-widget" id="<?= $id ?>">
  <button class="btn btn-default complex-filter-dropdown-toggle" type="button"><?= $label ?> <span
      class="complex-filter-counter"></span>
    <span class="caret"></span>
  </button>
  <span class="complex-filter-widget-clear">&times;</span>
  <div class="complex-filter-dropdown-menu">
    <div class="complex-filter-wrapper">
      <div class="form-group">
        <div class="input-group">
          <input type="text" id="<?= $id ?>-search" class="complex-filter-search form-control" placeholder="search">
          <div class="input-group-btn">
            <button id="<?= $id ?>-search-button" class="btn btn-default" type="button"><span
                class="glyphicon glyphicon-search"></span></button>
          </div>
        </div>
      </div>
    </div>

    <div class="complex-filter-content">

    </div>
    <div class="complex-filter-next-page text-center">

    </div>
  </div>
</div>
