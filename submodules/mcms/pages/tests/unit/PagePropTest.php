<?php
namespace mcms\pages\tests\unit;

use mcms\common\codeception\TestCase;
use mcms\pages\models\PageProp;

class PagePropTest extends TestCase
{
  public function _fixtures()
  {
    return $this->convertFixtures([
      'pages.page_props'
    ]);
  }

  public function testGetImageUrl()
  {
    // is_multivalue = 0
    $pageProp1 = PageProp::findOne(1);
    // is_multivalue = 1
    $pageProp2 = PageProp::findOne(2);


    $this->assertTrue($pageProp2->getImageUrl() ===  [], 'getImageUrl must return empty array');
    $this->assertFalse($pageProp1->getImageUrl(), 'getImageUrl must return false');
  }
}