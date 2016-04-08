<?php
/**
 * Created by PhpStorm.
 * User: phobos
 * Date: 11/4/15
 * Time: 11:06 AM
 */

namespace app\controllers;

use app\models\ContactStatusHistory;
use yii\filters\AccessControl;
use Yii;
use yii\db\Query;
use app\models\User;
use app\models\Call;
use app\models\CallManager;
use app\models\ContactShow;
use app\models\ContactVisit;
use app\models\Contact;
use app\components\widgets\ReportsWidget;


class ReportsController extends BaseController
{
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'getdata'],
                        'allow' => true,
                        'roles' => ['reports'],
                    ]
                ],
            ],
        ];
    }
    
    public function actionIndex() {
        $managers = User::find()
            ->where(['role' => User::ROLE_MANAGER])
            ->orWhere(['role' => User::ROLE_OPERATOR])
            ->all();
        return $this->render('index',['managers' => $managers]);
    }

    public function actionGetdata() {
        $request_data = Yii::$app->request->get();

        //three-row select from call
        $selectQuery1 =  '`u`.`id` AS `id`, `u`.`firstname` AS `name`,`c`.`type` AS `selector`, COUNT(*) AS `count`';
        $query1 = new Query();
        $query1->select($selectQuery1)
            ->from(Call::tableName() . ' as `c`')
            ->where('`c`.`status` <> \'new\' AND (`u`.`role` = 1 OR `u`.`role` = 5)')
            ->join("LEFT OUTER JOIN", CallManager::tableName().' `cm`','`cm`.`call_id` = `c`.`id`')
            ->join("LEFT OUTER JOIN", User::tableName().' `u`','`cm`.`manager_id` = `u`.`id`')
            ->groupBy('`c`.`type`, `u`.`id`');
        $dump = $query1->prepare(Yii::$app->db->queryBuilder)->createCommand()->rawSql;

        //three-row select from  contact
        $selectQuery2 = '`csh`.`manager_id` as `id`, `u`.`firstname` AS `name`,`csh`.`status` as `selector`, COUNT(*) as `count`';
        $query2 = new Query();
        $query2->select($selectQuery2)
            ->from(ContactStatusHistory::tableName() . ' as csh')
            ->join("LEFT JOIN", '(SELECT `role`,`id`,`firstname` from `user`) AS `u`','`u`.`id` = `csh`.`manager_id`')
            ->join('LEFT JOIN', Contact::tableName() . ' `c`', '`c`.`id` = `csh`.`contact_id`')
            ->where('(`u`.`role` = 1 OR `u`.`role` = 5) AND `c`.is_deleted = 0')
            ->groupBy('`id`,`selector`');
        $dump = $query2->prepare(Yii::$app->db->queryBuilder)->createCommand()->rawSql;

        // Получить количество успешных входящих
        $selectQuery3 =  '`u`.`id` AS `id`, `u`.`firstname` AS `name`, CONCAT_WS("_", `c`.`status`, `c`.`type`) AS `selector`, COUNT(*) AS `count`';
        $query3 = new Query();
        $query3->select($selectQuery3)
            ->from(Call::tableName() . ' as `c`')
            ->where('`c`.`status` = \'answered\' AND `c`.`type` = \'incoming\' AND (`u`.`role` = 1 OR `u`.`role` = 5)')
            ->join("LEFT OUTER JOIN", CallManager::tableName().' `cm`','`cm`.`call_id` = `c`.`id`')
            ->join("LEFT OUTER JOIN", User::tableName().' `u`','`cm`.`manager_id` = `u`.`id`')
            ->groupBy('`c`.`type`, `u`.`id`');
        $dump = $query3->prepare(Yii::$app->db->queryBuilder)->createCommand()->rawSql;


        //filtering on datatable request
        if (!empty($request_data['columns'][0]['search']['value'])) {
            $query1->andWhere(['`u`.`id`' => $request_data['columns'][0]['search']['value']]);
            $query2->where(['`u`.`id`' => $request_data['columns'][0]['search']['value']]);
        }
        if (!empty($request_data['columns'][1]['search']['value']) and (!empty($request_data['columns'][2]['search']['value']))) {
            $query1->andWhere(['between', '`c`.`date_time`', $request_data['columns'][1]['search']['value'], $request_data['columns'][2]['search']['value']]);
            $query2->andWhere(['between', '`csh`.`date_time`', $request_data['columns'][1]['search']['value'], $request_data['columns'][2]['search']['value']]);
        }
        //union all the queries
        $query1
            ->union($query2)
            ->union($query3);
        //$dump = $query1->prepare(Yii::$app->db->queryBuilder)->createCommand()->rawSql;
        $reportSummary = $query1->all();
        $report_widget = new ReportsWidget();
        $report_widget->reports = $reportSummary;
        $data = $report_widget->run();
        $json_data = array(
            "draw" => intval($request_data['draw']),
            "recordsTotal" => intval(0),
            "recordsFiltered" => intval(0),
            "data" => $data   // total data array
        );
        echo json_encode($json_data);
        die;
    }
}