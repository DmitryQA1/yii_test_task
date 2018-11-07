<?php

namespace app\models\forms;

use app\models\Contact;
use app\models\Tag;
use Yii;
use yii\base\Model;
use yii\validators\EmailValidator;
use app\components\Filter;

/**
 * ContactForm is the model behind the contact form.
 */
class ContactForm extends Model
{

    public $name;
    public $surname;
    public $middle_name;

    public $phones;
    public $emails;

    public $first_phone;
    public $second_phone;
    public $third_phone;
    public $fourth_phone;

    public $first_email;
    public $second_email;

    public $tags_str;
    public $tags;


    public function attributeLabels()
    {
        return [
            'name' => 'Имя',
            'surname' => 'Фамилия',
            'middle_name' => 'Отчество',
            'phones' => 'Номер телефона',
            'emails' => 'Email',
            'street' => 'Улица'
        ];
    }
    var $country;
    var $city;

    var $status;
    var $attraction_channel_id;
    var $birthday;
    var $notification_service_id;
    var $language_id;
    var $is_broadcast;

    var $edited_id;
    var $conflict_id;
    var $manager_id;

    public static function getAllCols() {
        return [
            'surname',
            'name',
            'phones',
            'middle_name',
            'emails',
            'country',
            'city',
            'attraction_channel_id',
            'birthday',
            'is_broadcast',
            'language_id',
            'notification_service_id',
            'status',
            'manager_id'
        ];
    }

    public function rules()
    {
        return [
            [['phones'], 'required', 'message' => 'Необходимо заполнить телефон'],
            [['phones'], 'phoneArray'],
            [['emails'], 'emailArray'],

            [['name', 'surname'], 'match', 'pattern' => "/^[\s\p{Cyrillic}\-()]*$/u", 'message' => 'Ошибка: в поле {attribute} - Недопустимые символы'],
            [['name', 'surname', 'country', 'city', 'birthday', 'middle_name'], 'string', 'length' => [1, 150],
                'tooShort' => 'Ошибка: поле {attribute} должно содержать не менее {min} символов',
                'tooLong' => 'Ошибка: поле {attribute} должно содержать не более {max} символов'],
            [['middle_name'], 'match', 'pattern' => "/^[\s\p{Cyrillic}\-()]*$/u", 'message' => 'Ошибка: в поле {attribute} - Недопустимые символы'],

            [['tags_str'], 'tagsArray'],

            [[
                'first_phone', 'second_phone', 'third_phone', 'fourth_phone',
                'first_email', 'second_email',
                'middle_name', 'attraction_channel_id','manager_id','status', 'birthday', 'is_broadcast','notification_service_id', 'language_id', 'country', 'city'
            ], 'default'],
        ];
    }

    public function isUnique($value, $attr, $fields, $message_callback) {
        if ($value != null) {
            $or_where = ['or'];
            foreach ($fields as $field) {
                $or_where[] = [$field => $value];
            }
            $contact = Contact::find()->where(['is_deleted' => '0'])->andWhere($or_where);
            if ($this->edited_id) {
                $contact->andWhere(['!=', 'id', $this->edited_id]);
            }
            $contact = $contact->one();
            if ($contact) {
                $message_callback($attr, $value, $contact->int_id);
                $this->conflict_id = $contact->id;
                return $contact->int_id;
            }
        }
        return true;
    }


    public function formName()
    {
        return 'contact';
    }

    public function requiredForContact($attribute, $params)
    {
        if (empty($this->phones)) {
            $this->addCustomError($attribute, 'Необходимо заполнить телефон');
        }
    }

    public static function dataConvert($data, $type) {
        $res_data = [];
        $data = array_map('trim', explode(',', $data));
        if ($type == 'phones') {
//            $data = array_map(function($el) {
//                return preg_replace("/[^a-zA-Z0-9]/i","", $el);
//            }, $data);
            $data_cols = Contact::getPhoneCols();
        } else {
            $data_cols = Contact::getEmailCols();
        }
        $count = 0;
        foreach ($data_cols as $col) {
            if (isset($data[$count])) {
                if ($type == 'emails') {
                    $res_data[$col] = strtolower($data[$count]);
                } else {
                    $res_data[$col] = $data[$count];
                }
            } else {
                $res_data[$col] = null;
            }
            $count++;
        }
        return $res_data;
    }

    public function tagsArray($attribute, $params)
    {
        $tags = array_map('trim', explode(',', $this->$attribute));
        foreach ($tags as $tag) {
            $this->checkTag($tag, $attribute);
            $tag_obj = Tag::getByName($tag);
            $this->tags[] = $tag_obj ?: (new Tag(['name' => $tag]));
        }
    }

    public function checkTag($tag, $attribute)
    {
        if (strlen($tag) > 150) {
            $this->addCustomError($attribute, 'Длина тега не должна превышать 150 символов');
        }
    }

    public function phoneArray($attribute, $params)
    {
        $phones = self::dataConvert($this->$attribute, 'phones');
        foreach ($phones as $phone_key => $phone_val) {
            $this->checkPhone($phone_val, $attribute);
            $fields = Contact::getPhoneCols();
            $this->isUnique($phone_val, $attribute, $fields, function($attr, $value, $contact_id) {
                $this->addCustomError($attr, 'Номер - '. $value .' уже существует в базе. ID = '. $contact_id);
            });
            $this->$phone_key = $phone_val;
        }
    }

    public function checkPhone($phone, $attribute)
    {
        if ($phone !== null) {
            if (!preg_match('/^\+?\d{10,}$/', $phone)) {
                $this->addCustomError($attribute, 'Телефон должен содержать только цифры (не менее 10 цифр)');
            }
        }
    }

    public function checkEmail($email, $attribute)
    {
        if ($email !== null) {
            $email_validator = new EmailValidator();
            if (!$email_validator->validate($email)) {
                $this->addCustomError($attribute, 'Email введен не верно');
            }
        }

    }

    public function emailArray($attribute, $params)
    {
        $emails = self::dataConvert($this->$attribute, 'emails');
        foreach ($emails as $email_key => $email_val) {
            $this->checkEmail($email_val, $attribute);
            $this->{$email_key} = $email_val;
        }
    }

    public function addCustomError($attribute, $message = '')
    {
        if ($this->getFirstError($attribute) == null) {
            $this->addError($attribute, 'Ошибка: '. $message);
        }
    }

}
