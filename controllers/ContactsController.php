<?php

namespace app\controllers;

use app\components\Filter;
use app\components\widgets\ContactTableWidget;
use app\models\Contact;
use app\models\ContactComment;
use app\models\ContactHistory;
use app\models\ContactRingRound;
use app\models\ContactScheduledCall;
use app\models\ContactScheduledEmail;
use app\models\ContactStatusHistory;
use app\models\ContactTag;
use app\models\forms\CommentForm;
use app\models\forms\ContactForm;
use app\models\User;
use app\models\Call;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

class ContactsController extends BaseController
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => [
//                            'index',
                            'view',
                            'history',
                            'addcomment',
//                            'getdata',
                            'edit',
//                            'hide-columns',
                            'get-contact-by-phone',
                            'get-contact-by-phone',
//                            'search',
//                            'link-with',
                            'objectschedulecall',
                            'objectscheduleemail',
                            'ring-round',
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => [
                            'save',
                        ],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => [
                            'index',
                            'getdata',
                            'hide-columns',
                            'search',
                            'link-with',
                        ],
                        'allow' => false,
                        'roles' => ['operator'],
                    ],
                    [
                        'actions' => ['delete', 'delete-filtered'],
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                    [
                        'actions' => [
                            'remove-tag',
                            'index',
                            'getdata',
                            'hide-columns',
                            'search',
                            'link-with',
                        ],
                        'allow' => true,
                        'roles' => ['admin', 'manager'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'edit' => ['post'],
                    'delete' => ['post'],
                    'delete-filtered' => ['post'],
                    'addcomment' => ['post'],
                    'objectschedulecall' => ['post'],
                    'objectscheduleemail' => ['post'],
                    'ring-round' => ['post'],
                    'link-with' => ['post'],
                    'search' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $session = Yii::$app->session;
        $hide_columns = $session->get('contact_hide_columns');
        if (!$hide_columns) {
            $hide_columns = ["surname", "name", "middle_name", "emails", "country", "region", "area", "delete_button"];
        }
        $table_cols = Contact::getColsForTableView();
        $filter_cols = Contact::getColsForTableView();
        unset($filter_cols['id']);
        return $this->render('index', ['hide_columns' => $hide_columns, 'table_cols' => $table_cols, 'filter_cols' => $filter_cols]);
    }

    public function actionGetdata()
    {
        $request_data = Yii::$app->request->get();
        $contact_tableName = Contact::tableName();
        $query = Contact::find()->with('manager', 'tags')->distinct($contact_tableName . '.id');
        $query->where([$contact_tableName . '.is_deleted' => '0']);
        $columns = Contact::getColsForTableView();
        $user_id = Yii::$app->user->identity->getId();
        $user_role = Yii::$app->user->identity->getUserRole();

        //Sorting
        if (isset($request_data['order'])) {
            $order_by_sort = $request_data['order'][0]['dir'] == 'asc' ? SORT_ASC : SORT_DESC;
            $sort_column = array_keys($columns)[$request_data['order'][0]['column']];
            if (isset($columns[$sort_column]['db_cols'])) {
                $sort_column = $columns[$sort_column]['db_cols'][0];
            }

            $sorting = [
                $contact_tableName . '.' . $sort_column => $order_by_sort,
            ];
        } else {
            $sorting = [
                $contact_tableName . '.id' => SORT_DESC,
            ];
        }

        if ($user_role == 'manager' || $user_role == 'operator') {
            $query->joinWith('tags.users')->andWhere(['user.id' => $user_id]);
        }
        $query_total = clone $query;
        $total_count = $query_total->count();

        //Filtering
        foreach ($request_data['columns'] as $column) {
            if (!empty($column['search']['value'])) {
                if (isset($columns[$column['name']]['db_cols'])) {
                    $db_cols_where = ['or'];
                    foreach ($columns[$column['name']]['db_cols'] as $db_col_i => $db_col_v) {
                        $db_cols_where[] = ['like', $contact_tableName . '.' . $db_col_v, $column['search']['value']];
                    }
                    $query->andWhere($db_cols_where);
                } elseif ($column['name'] == 'tags') {
                    $query->joinWith('tags')->andWhere(['like', 'tag.name', $column['search']['value']]);
                } else {
                    $query->andWhere(['like', $contact_tableName . '.' . $column['name'], $column['search']['value']]);
                }
            }
        }


        $total_filtering_count = $query->count();

//        $query_ids = clone $query;
//        $contact_ids = $query_ids->asArray()->all();
//        $contact_ids = implode(',', array_map(function($item) { return $item['id']; }, $contact_ids));

        $query
            ->orderBy($sorting)
            ->limit($request_data['length'])
            ->offset($request_data['start']);

        $dump = $query->createCommand()->rawSql;
        $contacts = $query->all();
        $contact_widget = new ContactTableWidget();
        $contact_widget->contacts = $contacts;
        $contact_widget->user_id = $user_id;
        $contact_widget->user_role = $user_role;
        $data = $contact_widget->run();

        $json_data = [
            "draw" => intval($request_data['draw']),
            "recordsTotal" => intval($total_count),
            "recordsFiltered" => intval($total_filtering_count),
            "data" => $data,   // total data array
            //"contact_ids" => $contact_ids
        ];
        echo json_encode($json_data);
        die;
    }

    public function actionEdit()
    {
        $post = Yii::$app->request->post();
        $contact_form = new ContactForm();
        if ($post['id']) {
            $contact_form->edited_id = $post['id'];
        }
        $contact_form->attributes = $post;

//        $call_order = Yii::$app->params['call_order_script'];

//        $contact_form->tags_str = 'полисмен, комбайнер';
//        $contact_form->tags_str = 'трактарист, бизнесмен, учитель';

        if ($contact_form->validate()) {
            try {
                $contact = NULL;
                if (isset($post['id']) && !empty($post['id'])) {
                    $contact = Contact::getById($post['id']);
                    if (!Yii::$app->user->can('updateContact', ['contact' => $contact])) {
                        $this->json(false, 403, 'Недостаточно прав для редактирования');
                    }
                    //if contact is deleted then make alive
                    if ($contact->is_deleted) {
                        $contact->is_deleted = 0;
                    }
                } else {
                    $contact = new Contact();
                    $contact->manager_id = Yii::$app->user->identity->id;
                }

                unset($post['_csrf']);
                unset($post['id']);
//                $contact->setTags($contact_form->tags);
                $contact->attributes = $contact_form->attributes;
                $contact->remove_tags = true;

                if ($contact->edit([])) {
                    $contact->sendToCRM();
                    $this->json(['id' => $contact->id], 200);
                } else {
                    $this->json(false, 415, $contact->getErrors());
                }
            } catch (\Exception $ex) {
                $this->json(false, 500, $ex->getMessage());
            }
        } else {
            $errors = $contact_form->getErrors();
            $this->json(false, 415, $errors);
        }
    }

    public function actionSave()
    {
        $post = Yii::$app->request->post();
        $contact_form = new ContactForm();
        $contact_form->attributes = $post;

        if ($contact_form->validate()) {
            try {
                $contact = new Contact();

                $contact->first_phone = $post['phone1'];
                $contact->second_phone = $post['phone2'];
                $contact->third_phone = $post['phone3'];
                $contact->fourth_phone = $post['phone4'];
                $contact->first_email = $post['email1'];
                $contact->second_email = $post['email2'];
                $contact->name = $post['first_name'];
                $contact->surname = $post['last_name'];
                $contact->middle_name = $post['middle_name'];
                $contact->country = $post['country'];
                $contact->region = $post['region'];
                $contact->area = $post['area'];
                $contact->city = $post['city'];
                $contact->street = $post['street'];
                $contact->house = $post['house'];
                $contact->flat = $post['flat'];
                $contact->int_id = $post['internal_no'];

                if ($contact->save()) {
                    $contact_history = new ContactHistory();
                    $contact_history->add($this->id, 'создан контакт (API)', 'new_contact');
                    $contact_history->save();
                    $contactStatusHistory = new ContactStatusHistory();
                    $contactStatusHistory->add($this->id, $this->manager_id, 'lead');
                    $contactStatusHistory->save();
                    $this->json(['id' => $contact->id], 200);
                } else {
                    $this->json(false, 415, $contact->getErrors());
                }
            } catch (\Exception $ex) {
                $this->json(false, 500);
            }
        } else {
            $errors = $contact_form->getErrors();
            $this->json(false, 415, $errors);
        }
    }


    public function actionSearch()
    {
        $search_term = Yii::$app->request->post('search_term');
        $id = Yii::$app->request->post('id');

        $query = Contact::find()->select(['id', 'int_id', 'surname', 'name', 'middle_name', 'first_phone', 'second_phone', 'third_phone', 'fourth_phone', 'first_email', 'second_email']);

        $contact_tableName = Contact::tableName();
        $query = Contact::find()->with('manager', 'tags')->distinct($contact_tableName . '.id');

        $query->andWhere(['like', $contact_tableName . '.first_phone', $search_term])
            ->orWhere(['like', $contact_tableName . '.second_phone', $search_term])
            ->orWhere(['like', $contact_tableName . '.third_phone', $search_term]);

        $query->andWhere(['is_deleted' => '0']);

//        $dump = $query->createCommand()->rawSql;

        $user_id = Yii::$app->user->identity->getId();
        $user_role = Yii::$app->user->identity->getUserRole();

        if ($user_role == 'manager' || $user_role == 'operator') {
            $query->joinWith('tags.users')->andWhere(['user.id' => $user_id]);
        }

        $contacts = $query->asArray()->all();

        foreach ($contacts as $key => &$contact) {
            // don't show user for himself
            if ($contact['id'] == $id) {
                unset($contacts[$key]);
                continue;
            }

            $contact['fio'] = implode(" ", array_filter([$contact['surname'], $contact['name'], $contact['middle_name']]));
            $contact['phones'] = implode("<br>", array_filter([$contact['first_phone'], $contact['second_phone'], $contact['third_phone'], $contact['fourth_phone']]));
            $contact['emails'] = implode("<br>", array_filter([$contact['first_email'], $contact['second_email']]));
        }

        if (count($contacts) > 0) {
            $json_data = [
                "status" => 200,
                "data" => $contacts,
            ];
        } else {
            $json_data = [
                "status" => 404,
            ];
        }

        echo json_encode($json_data);
        die;
    }

    public function actionLinkWith()
    {
        $linked_contact_id = Yii::$app->request->post('linked_contact_id');
        $link_to_contact_id = Yii::$app->request->post('link_to_contact_id');

        $linked_contact = Contact::find()->where(['id' => $linked_contact_id])->one();
        $link_to_contact = Contact::find()->where(['id' => $link_to_contact_id])->one();

        if ($link_to_contact->mergeTogether($linked_contact)) {
            if ($link_to_contact->save()) {
                $linked_contact->is_deleted = 1;
                $linked_contact->save();
                $this->json(false, 200);
            } else {
                $this->json(false, 415, $link_to_contact->getErrors());
            }
        } else {
            $this->json(false, 415, $link_to_contact->getErrors());
        }
    }

    public function actionView()
    {
        $user_id = Yii::$app->user->identity->getId();
        $contact_id = Yii::$app->request->get('id');
        $contact = Contact::find()->with('tags')->where(['id' => $contact_id]);
        $contact2 = clone $contact;
        $contact2 = $contact2->one();
        $contact_arr = $contact->asArray()->one();
        $contact_data = array_intersect_key($contact_arr, array_flip(Contact::$safe_fields));

        $contact_data['phones'] = Filter::dataImplode($contact2->getPhoneValues());

        $contact_data['emails'] = Filter::dataImplode($contact2->getEmailValues());

        if (count($contact_arr['tags']) > 0) {
            $contact_data['tags'] = $contact_arr['tags'];
//            $dummp_c = $contact2->getTags()->joinWith(['users'])->where([User::tableName().'.id' => $user_id]);
//            $dump = $dummp_c->createCommand()->rawSql;
            $manager_tags = $contact2->getTags()->joinWith(['users'])->where([User::tableName() . '.id' => $user_id])->asArray()->all();
            $manager_tags = array_map(function ($item) {
                return $item['name'];
            }, $manager_tags);
            $contact_data['manager_tags'] = $manager_tags;
        }

        $contact_manager = User::find()->where(['id' => $contact_data['manager_id']])->one();
        $contact_data['manager_name'] = $contact_manager['firstname'];
        $this->json($contact_data, 200);
    }

    public function actionHistory()
    {
        $contact_id = Yii::$app->request->get('id');
        $history = ContactHistory::getByContactId($contact_id);
        $this->json($history, 200);
    }

    public function actionAddcomment()
    {
        $post = Yii::$app->request->post();
        $comment_form = new CommentForm();
        $comment_form->load($post);
        if ($comment_form->validate()) {
            $contact_id = Yii::$app->request->post('id');
            $comment_text = $comment_form->comment;
            $contact_comment = new ContactComment();
            if ($contact_comment->add($contact_id, $comment_text)) {
                $contact_history = new ContactHistory();
                $comment_text = "комментарий - " . $comment_text;
                $contact_history->add($contact_id, $comment_text, 'comment', $contact_comment->datetime);
                $response_date = [
                    'text' => $comment_text,
                    'datetime' => date("d-m-Y G:i:s", strtotime($contact_comment->datetime)),
                ];
                $this->json($response_date, 200);
            } else {
                $this->json(false, 500);
            }
        } else {
            $errors = $comment_form->getErrors();
            $this->json(false, 415, $errors);
        }
    }

    public function actionDelete()
    {
        if (Contact::deleteById(Yii::$app->request->post('id'))) {
            $this->json(false, 200);
        }
    }

    public function actionDeleteFiltered()
    {
        $contact_tableName = Contact::tableName();
        $query = Contact::find()->with('manager', 'tags')->distinct($contact_tableName . '.id');
        $query->where([$contact_tableName . '.is_deleted' => '0']);
        $columns = Contact::getColsForTableView();

        //Filtering
        foreach (Yii::$app->request->post() as $column => $value) {
            if (!empty($value) && isset($columns[$column])) {
                if (isset($columns[$column]['db_cols'])) {
                    $db_cols_where = ['or'];
                    foreach ($columns[$column]['db_cols'] as $db_col_v) {
                        $db_cols_where[] = ['like', $contact_tableName . '.' . $db_col_v, $value];
                    }
                    $query->andWhere($db_cols_where);
                } elseif ($column == 'tags') {
                    $query->joinWith('tags')->andWhere(['like', 'tag.name', $value]);
                } else {
                    $query->andWhere(['like', $contact_tableName . '.' . $column, $value]);
                }
            }
        }

        if (Contact::deleteById(ArrayHelper::map($query->all(), 'id', 'id'))) {
            $this->json(false, 200);
        }
    }

    public function actionHideColumns()
    {
        $hide_columns = Yii::$app->request->get('hide_columns');
        Yii::$app->session->set('contact_hide_columns', $hide_columns);
        $this->json(false, 200);
    }

    public function actionRingRound()
    {
        $contact_id = Yii::$app->request->post('id');
        $action_comment_text = Yii::$app->request->post('action_comment');
        $call_order_token = Yii::$app->request->post('call_order_token');
        $attitude_level = Yii::$app->request->post('attitude');
        $contact_ring_round = new ContactRingRound();
        $contact_ring_round->manager_id = Yii::$app->user->identity->getId();
        if ($contact_ring_round->add($contact_id, $action_comment_text, $call_order_token, $attitude_level)) {
            $history_text = $contact_ring_round->getHistoryText();
            $response_date = [
                'id' => $contact_ring_round->id,
                'system_date' => date('d-m-Y G:i:s', strtotime($contact_ring_round->system_date)),
                'history' => $history_text,
            ];
//            $call = Call::find(['call_order_token' => $call_order_token])->one();
//            $call->sendToCRM(Yii::$app->user->identity, $call_order_token);
            $this->json($response_date, 200);
        }
        $this->json(false, 500);
    }

    public function actionObjectschedulecall()
    {
        $contact_id = Yii::$app->request->post('id');
        $schedule_date = Yii::$app->request->post('schedule_date');
        $action_comment_text = Yii::$app->request->post('action_comment');
        $call_order_token = Yii::$app->request->post('call_order_token');
        $attitude_level = Yii::$app->request->post('attitude');
        $contact_schedule_call = new ContactScheduledCall();
        $contact_schedule_call->manager_id = Yii::$app->user->identity->getId();
        if ($contact_schedule_call->add($contact_id, $schedule_date, $action_comment_text, $call_order_token, $attitude_level)) {
            $history_text = $contact_schedule_call->getHistoryText();
            $response_date = [
                'id' => $contact_schedule_call->id,
                'system_date' => date('d-m-Y G:i:s', strtotime($contact_schedule_call->system_date)),
                'history' => $history_text,
            ];
            $this->json($response_date, 200);
        }
        $this->json(false, 500);
    }

    public function actionObjectscheduleemail()
    {
        $contact_id = Yii::$app->request->post('id');
        $schedule_date = Yii::$app->request->post('schedule_date');
        $action_comment_text = Yii::$app->request->post('action_comment');
        $contact_schedule_email = new ContactScheduledEmail();
        $contact_schedule_email->manager_id = Yii::$app->user->identity->id;
        if ($contact_schedule_email->add($contact_id, $schedule_date, $action_comment_text)) {
            $history_text = $contact_schedule_email->getHistoryText();
            $response_date = [
                'id' => $contact_schedule_email->id,
                'system_date' => date('d-m-Y G:i:s', strtotime($contact_schedule_email->system_date)),
                'history' => $history_text,
            ];
            $this->json($response_date, 200);
        }
        $this->json(false, 500);
    }


    public function actionGetContactByPhone()
    {
        $phone = Yii::$app->request->get('phone');

        if ($contact = Contact::getContactByPhone($phone)) {
            $this->json(['contact_id' => $contact['id']], 200);
        } else {
            $this->json(false, 404);
        }
    }

    public function actionRemoveTag()
    {
        $contact_id = Yii::$app->request->post('id');
        $tag_id = Yii::$app->request->post('tag_id');
        //$tag = Tag::getById($tag_id);
        if ($tag_id && $contact_id) {
            ContactTag::deleteAll(['contact_id' => $contact_id, 'tag_id' => $tag_id]);
            $this->json([], 200);
        }
        $this->json([], 415, 'Tag not found');
    }
}
