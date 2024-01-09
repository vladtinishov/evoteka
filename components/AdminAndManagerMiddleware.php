<?php

namespace app\components;

use app\models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PHPUnit\Exception;
use Yii;
use yii\base\ActionFilter;
use yii\web\Response;

class AdminAndManagerMiddleware extends ActionFilter
{
    public function beforeAction($action): bool
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $user = Yii::$app->request->getQueryParam('user');
        $user = new User($user);

        if (!$user->isAdmin() || !$user->isManager()) {
            Yii::$app->response->statusCode = 403;
            Yii::$app->response->data = ['error' => 'Access Denied'];
            return false;
        }

        return true;
    }
}