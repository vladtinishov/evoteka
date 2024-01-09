<?php

namespace app\components;

use app\models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PHPUnit\Exception;
use Yii;
use yii\base\ActionFilter;
use yii\web\Response;

class TokenMiddleware extends ActionFilter
{
    public function beforeAction($action)
    {
        try {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $token = Yii::$app->request->headers->get('Authorization');

            if ($token !== null && preg_match('/^Bearer\s+(.*?)$/', $token, $matches)) {
                $bearerToken = $matches[1];

                if ($data = $this->isValidToken($bearerToken)) {
                    $user = User::findOne($data->user_id);

                    if ($user) {
                        Yii::$app->request->setQueryParams(['user' => $user]);
                        return true;
                    }
                }
            }

            throw new \Exception('');
        } catch (\Exception $e) {
            Yii::$app->response->statusCode = 401;
            Yii::$app->response->data = ['error' => 'Unauthorized'];
            return false;
        }
    }

    private function isValidToken($token)
    {
        try {
            return JWT::decode($token, new Key($_ENV['SECRET_KEY'], 'HS256'));
        } catch (Exception $e) {
            return false;
        }
    }
}