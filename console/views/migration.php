<?php
/**
 * This view is used by console/controllers/MigrateController.php
 * The following variables are available in this view:
 */
/* @var $className string the new migration class name without namespace */
/* @var $namespace string the new migration class namespace */

echo "<?php\n";
if (!empty($namespace)) {
    echo "\nnamespace {$namespace};\n";
}
?>

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class <?= $className ?> extends Migration
{
use PermissionTrait;
/**
*/
public function up()
{

}

/**
*/
public function down()
{
echo "<?= $className ?> cannot be reverted.\n";

return true;
}
}
