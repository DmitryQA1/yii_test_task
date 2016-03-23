<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "contact_call".
 *
 * @property integer $id
 * @property integer $contact_id
 * @property string $system_date
 * @property string $schedule_date
 * @property integer $manager_id
 *
 * @property Contact $contact
 * @property User $manager
 */
class ContactScheduledCall extends \yii\db\ActiveRecord {
    
    public $history_text;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'contact_scheduled_call';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['contact_id', 'system_date', 'manager_id'], 'required'],
            [['contact_id', 'manager_id'], 'integer'],
            [['system_date', 'schedule_date'], 'safe'],
            [['contact_id'], 'exist', 'skipOnError' => true, 'targetClass' => Contact::className(), 'targetAttribute' => ['contact_id' => 'id']],
            [['manager_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['manager_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'contact_id' => 'Contact ID',
            'system_date' => 'System Date',
            'schedule_date' => 'Schedule Date',
            'manager_id' => 'Manager ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContact() {
        return $this->hasOne(Contact::className(), ['id' => 'contact_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManager() {
        return $this->hasOne(User::className(), ['id' => 'manager_id']);
    }

    public function add($contact_id, $schedule_date) {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->contact_id = $contact_id;
            $this->system_date = date('Y-m-d G:i:s', time());
            $this->schedule_date = date('Y-m-d G:i:s', strtotime($schedule_date));
            $action = new Action();
            $action_type = ActionType::find()->where(['name' => 'scheduled_call'])->one();
            $action->add($contact_id, $action_type->id, [], $schedule_date);
            $action->addManagerNotification($action->id, $this->system_date, 'scheduled_call', $this->manager_id, $this->contact_id);
            $this->save();

            $contact_history = new ContactHistory();
            $history_text = $this->buildHistory($schedule_date);
            $contact_history->add($contact_id, $history_text, $this->id, 'scheduled_call', $this->system_date);
            $this->setHistoryText($history_text);
            $transaction->commit();
            return true;
        } catch (\Exception $ex) {
            $transaction->rollback();
            return false;
        }
    }

    private function buildHistory($schedule_date) {
        $history_text = "Запланирован звонок на " . date('d-m-Y G:i:s', strtotime($schedule_date));
        return $history_text;
    }

    public function setHistoryText($history_text) {
        $this->history_text = $history_text;
    }

    public function getHistoryText() {
        return $this->history_text;
    }

}