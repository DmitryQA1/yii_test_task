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

    var $name;
    var $surname;
    var $middle_name;

    var $phones;
    var $emails;

    var $first_phone;
    var $second_phone;
    var $third_phone;
    var $fourth_phone;

    var $first_email;
    var $second_email;

    var $tags_str;
    var $tags;

    var $country;
    var $region;
    var $area;
    var $city;
    var $street;
    var $house;
    var $flat;

    var $edited_id;

    public static function getAllCols() {
        return [
            'surname',
            'name',
            'phones',
            'middle_name',
            'emails',
            'country',
            'region',
            'area',
            'city',
            'street',
            'house',
            'flat'
        ];
    }

    /*'name',
    'surname',
    'middle_name',
    'first_phone',
    'second_phone',
    'third_phone',
    'fourth_phone',
    'first_email',
    'second_email',
    'country',
    'region',
    'area',
    'city',
    'street',
    'house',
    'flat',
    'status'
    */

    public function rules()
    {
        return [
            [['name', 'surname', 'phones'], 'requiredForContact'],
            [['phones'], 'phoneArray'],
            [['emails'], 'emailArray'],

            [['name', 'surname'], 'match', 'pattern' => "/^[\p{Cyrillic}\-]*$/u", 'message' => '{attribute} - Недопустимые символы'],
            [['name', 'surname', 'middle_name'], 'string', 'length' => [1, 150],
                'tooShort' => '{attribute} должен содержать больше {min} символов',
                'tooLong' => '{attribute} должен содержать до {max} символов'],
            [['middle_name'], 'match', 'pattern' => "/^[\p{Cyrillic}\-\s]*$/u", 'message' => 'Недопустимые символы'],
            [['country'], 'match', 'pattern' => "/^[\p{Cyrillic}\s\-\.\(\)]*$/u", 'message' => 'Недопустимые символы'],
            [['region', 'area'], 'match', 'pattern' => "/^[\p{Cyrillic}\s\-\.\(\)]*$/u", 'message' => 'Недопустимые символы'],
            [['city'], 'match', 'pattern' => "/^[\p{Cyrillic}\s\-\.\(\)\d]*$/u", 'message' => 'Недопустимые символы'],
            [['street'], 'match', 'pattern' => "/^[\p{Cyrillic}\s\-\.\d]*$/u", 'message' => 'Недопустимые символы'],
            [['house', 'flat'], 'match', 'pattern' => "/^[\p{Cyrillic}\s\-\.\d\/]*$/u", 'message' => 'Недопустимые символы'],

            [['tags_str'], 'tagsArray'],

            [[
                'first_phone', 'second_phone', 'third_phone', 'fourth_phone',
                'first_email', 'second_email',
                'middle_name', 'region', 'area', 'city', 'street', 'house', 'flat'
            ], 'default'],
        ];
    }

    public function isUnique($value, $attr, $fields, $message_callback) {
        if ($value != null) {
            $or_where = ['or'];
            foreach ($fields as $field) {
                $or_where[] = [$field => $value];
            }
            $contact = Contact::find()->where(['is_deleted' => false])->andWhere($or_where);
            if ($this->edited_id) {
                $contact->andWhere(['!=', 'id', $this->edited_id]);
            }
            $contact = $contact->one();
            if ($contact) {
                $message_callback($attr, $value, $contact->int_id);
                return $contact->int_id;
            }
        }
        return true;
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Имя',
            'surname' => 'Фамилия',
            'middle_name' => 'Отчество',
            'phones' => 'Номер телефона',
            'emails' => 'Email'
        ];
    }

    public function formName()
    {
        return 'contact';
    }

    public function requiredForContact($attribute, $params)
    {
        if (empty($this->name) || empty($this->surname) || empty($this->phones)) {
            $this->addCustomError($attribute, 'Необходимо заполнить ФИО и телефон');
        }
    }

    public static function dataConvert($data, $type, $action) {
        if ($action == 'explode') {
            $res_data = [];
            $data = array_map('trim', explode(',', $data));
            if ($type == 'phones') {
                $data = array_map(function($el) {
                    return preg_replace("/[^a-zA-Z0-9]/i","", $el);
                }, $data);
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
        } elseif ($action == 'implode') {
            $res_data = '';
            $res_data = implode(', ', $data);
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
        if (!preg_match("/^[\p{Cyrillic}\-]*$/u", $tag)) {
            $this->addCustomError($attribute, 'Теги содержат недопустимые символы');
        }
    }

    public function phoneArray($attribute, $params)
    {
        $phones = self::dataConvert($this->$attribute, 'phones', 'explode');
        foreach ($phones as $phone_key => $phone_val) {
            $this->checkPhone($phone_val, $attribute);
            $fields = Contact::getPhoneCols();
            $this->isUnique($phone_val, $attribute, $fields, function($attr, $value, $contact_id) {
                $this->addCustomError($attr, $value.', уже существует в базе');
            });
            $this->$phone_key = $phone_val;
        }
    }

    public function checkPhone($phone, $attribute)
    {
        if ($phone !== null) {
            if (!preg_match('/^\d*$/', $phone)) {
                $this->addCustomError($attribute, 'Телефон не должен содержать буквенные символы');
            } elseif (strlen($phone) == 10) {
                $this->addCustomError($attribute, 'Код страны не введен. Код России: 7');
            } elseif (strlen($phone) < 10 || strlen($phone) > 15) {
                $this->addCustomError($attribute, 'Телефон заполнен некорректно');
            }
//            elseif (!preg_match('/^(8|7|\+7)/', $phone)) {
//                if ($this->getFirstError($attribute) == null) {
//                    $this->addError($attribute, 'Код страны введен не верно');
//                }
//            } elseif (!preg_match('/^(8|7|\+7)((\d{10})|(\s\(\d{3}\)\s\d{3}\s\d{2}\s\d{2}))/', $phone)) {
//                if ($this->getFirstError($attribute) == null) {
//                    $this->addError($attribute, 'Телефон заполнен некорректно');
//                }
//            }
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
        $emails = self::dataConvert($this->$attribute, 'emails', 'explode');
        foreach ($emails as $email_key => $email_val) {
            $this->checkEmail($email_val, $attribute);
            $fields = Contact::getEmailCols();
            $this->isUnique($email_val, $attribute, $fields, function($attr, $value, $contact_id) {
                $this->addCustomError($attr, $value.', уже существует в базе');
            });
            $this->$email_key = $email_val;
        }
    }

    public function addCustomError($attribute, $message = '')
    {
        if ($this->getFirstError($attribute) == null) {
            $this->addError($attribute, $message);
        }
    }

}
