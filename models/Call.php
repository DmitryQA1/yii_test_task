<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "call".
 *
 * @property integer $id
 * @property string $date_time
 * @property string $type
 * @property integer $contact_id
 * @property integer $phone_number
 * @property string $record
 * @property string $unical_id
 * @property string $manager_int_id
 */
class Call extends \yii\db\ActiveRecord {

    //const CALL_NEW = 'new';
    const CALL_INCOMING = 'incoming';
    const CALL_OUTGOING = 'outgoing';
    const CALL_STATUS_MISSED = 'missed';
    const CALL_STATUS_FAILURE = 'failure';
    const CALL_STATUS_ANSWERED = 'answered';
    const CALL_STATUS_NEW = 'new';

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'call';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['date_time', 'type', 'unique_id'], 'required'],
            [['date_time', 'attitude_level', 'call_order_token', 'tag_id'], 'safe'],
            [['type', 'status', 'unique_id', 'call_order_token'], 'string'],
            [['contact_id', 'phone_number', 'total_time', 'answered_time', 'attitude_level', 'tag_id'], 'integer'],
            [['record'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'date_time' => 'Date Time',
            'type' => 'Type',
            'contact_id' => 'Contact ID',
            'phone_number' => 'Phone Number',
            'record' => 'Record',
        ];
    }

    public static function getTableColumns() {
        return [
            'id' => '`c`.`id`',
            'date' => 'DATE_FORMAT(`c`.`date_time`, "%Y-%m-%d")',
            'time' => 'DATE_FORMAT(`c`.`date_time`, "%H-%i-%S")',
            'type' => '`c`.`type`',
            'manager' => '`u`.`firstname`',
            'contact_id' => '`c`.`contact_id`',
            'phone_number' => '`c`.`phone_number`',
            'contact' => '`ct`.`name`',
            'record' => '`c`.`record`',
            'status' => '`c`.`status`',
            //'missed_call_id' => '`mc`.`id`',
        ];
    }

    public static function getCallStatuses()
    {
        return [
            ['name' => self::CALL_STATUS_ANSWERED.'_'.self::CALL_STATUS_MISSED.'|'.self::CALL_INCOMING, 'label' => 'Исходящий'],
            ['name' => self::CALL_STATUS_ANSWERED.'|'.self::CALL_OUTGOING, 'label' => 'Входящий'],
            ['name' => self::CALL_STATUS_MISSED.'|'.self::CALL_OUTGOING, 'label' => 'Пропущенный'],
            ['name' => self::CALL_STATUS_FAILURE.'|'.self::CALL_INCOMING.'_'.self::CALL_OUTGOING, 'label' => 'Сбой'],
        ];
    }

    public static function getAttitudeLevels()
    {
        return [
            ['name' => 1, 'label' => '--'],
            ['name' => 2, 'label' => '-'],
            ['name' => 3, 'label' => '+-'],
            ['name' => 4, 'label' => '+'],
            ['name' => 5, 'label' => '++'],
        ];
    }

    public static function getAttitubeLevelLabel($level) {
        switch ($level) {
            case '1':
                $label = '-2';
                break;
            case '2':
                $label = '-1';
                break;
            case '3':
                $label = '0';
                break;
            case '4':
                $label = '1';
                break;
            case '5':
                $label = '2';
                break;
        }
        return $label;
    }

    public static function buildSelectQuery() {
        $columns = self::getTableColumns();
        $select = [];
        foreach ($columns as $alias => $column) {
            $select[] = $column . " as " . $alias;
        }
        return $select;
    }

    public function incoming($unique_id, $contact_id, $phone_number, $call_order_token) {
        $this->unique_id = $unique_id;
        $this->date_time = date('Y-m-d H:i:s');
        $this->type = Call::CALL_INCOMING;
        $this->phone_number = $phone_number;
        $this->contact_id = $contact_id;
        $this->status = Call::CALL_STATUS_NEW;
        $this->call_order_token = $call_order_token;
        return $this->save();
    }

    public function outgoing($unique_id, $contact_id, $phone_number) {
        $this->unique_id = $unique_id;
        $this->date_time = date('Y-m-d H:i:s');
        $this->type = Call::CALL_OUTGOING;
        $this->phone_number = $phone_number;
        $this->contact_id = $contact_id;
        $this->status = Call::CALL_STATUS_NEW;
        return $this->save();
    }

    public static function getByUniquelId($unique_id) {
        return self::find()->where(['unique_id' => $unique_id])->one();
    }

    /*
     *  $status NO ANSWER | FAILED | BUSY | ANSWERED | UNKNOWN | CONGESTION
     */

    public function callEnd($date_time, $total_time, $answered_time, $record_file, $status, $managers_id) {
        $this->date_time = date('Y-m-d H:i:s', strtotime($date_time));
        if ($status == 'ANSWERED') {
            $this->total_time = $total_time;
            $this->answered_time = $answered_time;
            $this->record = $record_file;
            $this->status = Call::CALL_STATUS_ANSWERED;
        } else if ($status == 'NO ANSWER') {
            $this->status = Call::CALL_STATUS_MISSED;
        } else {
            $this->status = Call::CALL_STATUS_FAILURE;
        }
        $this->setManagersForCall($managers_id, $status);

        return $this->save();
    }

    public static function sendToCRM($call_order_token, $manager_id) {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $url = "http://localhost/post.php";

        $call = Call::find()->where(['call_order_token' => $call_order_token])->one();
        
        $calls['phone_number'] = $call->phone_number;
        $calls['type'] = $call->type;
        $calls['date_time'] = $call->date_time;
        $calls['status'] = $call->status;
        $calls['total_time'] = $call->total_time;
        $calls['comment'] = $call->comment;
        $calls['emotion'] = Call::getAttitubeLevelLabel($call->attitude_level); 

        $manager = User::find('int_id')->where(['id' => $manager_id])->one();
        $calls['internal_no'] = $manager->int_id;

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('calls' => json_encode($calls)));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec ($ch);

//        $log_data = date("Y-m-d H:i:s"). "\n";
//        $log_data .= "\n$url\n";
//        $log_data .= "data=".$data.'&token='.$token;
//        $log_data .= "\nResponse: \n" . $server_output;
//        $log_data .= "\n\n===============\n\n";
//        file_put_contents(Yii::getAlias('@webroot').'/json.txt', $log_data, FILE_APPEND);

        curl_close ($ch);
    }


    public function setManagersForCall($managers_id, $status) {
        $contact_manager = null;
        $managers_id_array = array_map('trim', explode(',', $managers_id));
        $managers = User::find()->where(['int_id' => $managers_id_array])->all();
        foreach ($managers as $manager) {
            if ($status === "NO ANSWER") {
                $missed_call = new MissedCall();
                $missed_call->add($this->id, $manager->id);
                $manager_notification = new ManagerNotification();
                $manager_notification->add($this->date_time, 'call_missed', $manager->id, $this->phone_number, $this->contact_id);
            }            
            $call_manager = new CallManager();
            $call_manager->call_id = $this->id;
            $call_manager->manager_id = $manager->id;
            $call_manager->save();
        }
        if ($this->contact_id) {
            $contact_manager = Contact::getManagerById($this->contact_id);
            if ($contact_manager) {
                if ($status !== "NO ANSWER") {
                    if (array_search($contact_manager->int_id, $managers_id_array) === false) {
                        $manager_notification = new ManagerNotification();
                        $manager_notification->add($this->date_time, 'call_missed', $contact_manager->id, $this->phone_number, $this->contact_id);
                    }
                }
            }
        }
    }

    public static function getCallAttitudeLabel(Call $call)
    {
        $res = '';
        $attitude = $call->attitude_level;
        $mediana = 3;
        if ($attitude === $mediana) {
            $res = '+-';
        } else if ($attitude > $mediana) {
            for ($i = $mediana; $i < $attitude; $i++) {
                $res .= '+';
            }
        } else if ($attitude < $mediana) {
            for ($i = $mediana; $i > $attitude; $i--) {
                $res .= '-';
            }
        }
        return $res;
    }

    public static function getCallStatusLabel(Call $call)
    {
        $res = '';
        switch ($call->status) {
            case "answered":
                switch ($call->type) {
                    case "incoming":
                        $res = "Исходящий";
                        break;
                    case "outgoing":
                        $res = "Входящий";
                        break;
                }
                break;
            case "missed":
                switch ($call->type) {
                    case "incoming":
                        $res = "Исходящий";
                        break;
                    case "outgoing":
                        $res = "Пропущенный";
                        break;
                }
                break;
            case "failure":
                switch ($call->type) {
                    case "incoming":
                        $res = "Исходящий - сбой";
                        break;
                    case "outgoing":
                        $res = "Входящий - сбой";
                        break;
                }
                break;
        }
        return $res;
    }
    
    public function setContactIdByPhone($phone, $contact_id) {
        $this->updateAll(['contact_id' => $contact_id], ['phone_number' => $phone]);
    }

    public function getTag() {
        return $this->hasOne(Tag::className(), ['id' => 'tag_id']);
    }

    public function getMissedCall() {
        return $this->hasOne(MissedCall::className(), ['call_id' => 'id']);
    }

    public function getContact() {
        return $this->hasOne(Contact::className(), ['id' => 'contact_id']);
    }

    public function getCallManagers() {
        return $this->hasMany(CallManager::className(), ['call_id' => 'id']);
    }


}
