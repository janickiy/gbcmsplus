<div class="solar-system">
  <?php foreach($data as $page): ?>
    <?php
    $class = $page->getPropByCode('class')->multilang_value . ' ' . $page->getPropByCode('position')->multilang_value;

    $style = $page->getPropByCode('color') ? 'background-color:' . $page->getPropByCode('color')->multilang_value . ';' : '';
    $style .= $page->getPropByCode('image') ? 'background-image:url(' . $page->getPropByCode('image')->getImageUrl() . ');' : '';

    $style .= $page->getPropByCode('size')->multilang_value . ';';

    ?>

    <?php if($page->getPropByCode('link')):?>
      <a rel="nofollow noopener" target="_blank" href="<?=$page->getPropByCode('link')->multilang_value?>"><div class="planet <?=$class?> planet-active"></div></a>
    <?php else: ?>
      <div class="planet <?=$class?> empty" style="<?=$style?>"></div>
    <?php endif;?>
  <?php endforeach ?>

</div>
