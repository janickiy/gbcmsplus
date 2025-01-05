<?php

namespace tests\codeception\site\unit\page_content;

use tests\codeception\site\unit\DbTestCase;
use Codeception\Specify;

class SampleTest extends DbTestCase
{
    public function testMeFirstCase()
    {
        expect('sample test', true)->true();
    }
}
