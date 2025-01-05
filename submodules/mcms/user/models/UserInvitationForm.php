<?php

namespace mcms\user\models;

use PhpOffice\PhpSpreadsheet\Reader\Csv;
use Yii;
use yii\base\Model;
use yii\db\Exception;
use yii\web\UploadedFile;

/**
 * Class UserInvitationForm
 * @package mcms\user\models
 */
class UserInvitationForm extends Model
{
    const DATA_DELIMITER = ';';

    /**
     * @var UploadedFile
     */
    public $csvFile;

    /**
     * @var string
     */
    public $data;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['data', 'string'],
            ['csvFile', 'default', 'value' => function () {
                return UploadedFile::getInstance($this, 'csvFile');
            }],
            ['csvFile', 'file', 'extensions' => ['csv'], 'checkExtensionByMimeType' => false],
        ];
    }

    /**
     * @param $runValidation
     * @return bool
     */
    public function save($runValidation = true)
    {
        if ($runValidation && !$this->validate()) {
            return false;
        }

        if ($this->data) {
            $this->saveData();
        }

        if ($this->csvFile) {
            $this->saveFile();
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function saveData()
    {
        $rows = preg_split('/\n/', $this->data);
        $invitations = [];

        foreach ($rows as $row) {
            $parts = explode(self::DATA_DELIMITER, $row);
            if (empty($parts[0])) {
                continue;
            }

            $invitations[$parts[0]] = new UserInvitation([
                'username' => $parts[0],
                'contact' => $parts[1] ?? null,
            ]);
        }

        return $this->saveInternal($invitations);
    }

    /**
     *
     */
    protected function saveFile()
    {
        $reader = new Csv();
        $spreadsheet = $reader->load($this->csvFile->tempName);


        $worksheet = $spreadsheet->getActiveSheet();
        $values = [];

        foreach ($worksheet->getRowIterator() as $row) {
            $invitation = new UserInvitation();

            foreach ($row->getCellIterator() as $cell) {
                switch ($cell->getColumn()) {
                    case 'A':
                        $invitation->username = $cell->getValue();
                        break;
                    case 'B':
                        $invitation->contact = $cell->getValue();
                        break;
                }
            }

            $values[$invitation->username] = $invitation;
        }

        return $this->saveInternal($values);
    }

    /**
     * @param UserInvitation[] $invitations
     * @return bool
     */
    protected function saveInternal(array $invitations)
    {
        $chunks = array_chunk($invitations, 1000);

        foreach ($chunks as $chunk) {
            if (!$this->transactionalSave($chunk)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param UserInvitation[] $invitations
     * @return bool
     */
    protected function transactionalSave(array $invitations)
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $batch = [];

            foreach ($invitations as $invitation) {
                /** @var UserInvitation $invitation */
                $invitation->validate()
                && $invitation->beforeSave(true) // чтобы сработали Behaviors
                && $batch[] = $invitation->attributes;
            }

            Yii::$app->db->createCommand()->batchInsert(
                UserInvitation::tableName(),
                (new UserInvitation())->attributes(),
                $batch
            )->execute();

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();

            return false;
        }

        return true;
    }
}