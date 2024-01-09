<?php

namespace app\controllers;

use app\models\User;
use Firebase\JWT\JWT;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Response;

class AuthController extends \yii\web\Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'get-token' => ['post'],
                    'register' => ['post'], // Include register in allowed actions
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionGetToken()
    {
        try {
            $login = Yii::$app->request->post('login');
            $password = Yii::$app->request->post('password');

            $user = User::findOne(['login' => $login]);

            if ($user && Yii::$app->security->validatePassword($password, $user->password_hash)) {
                $token = $this->generateJwtToken($user);
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['token' => $token];
            } else {
                Yii::$app->response->statusCode = 401; // Unauthorized HTTP status code
                return ['error' => 'Invalid login or password'];
            }
        } catch (\Exception $e) {
            Yii::$app->response->statusCode = 500; // Internal Server Error HTTP status code
            return ['error' => $e->getMessage()];
        }
    }

    private function generateJwtToken($user): string
    {
        $key = $_ENV['SECRET_KEY'];
        $payload = [
            'user_id' => $user->id,
            'exp' => time() + 3600,
        ];

        return JWT::encode($payload, $key, $_ENV['TOKEN_ENCODE_ALG']);
    }

    public function actionRegister()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $name = Yii::$app->request->post('name');
            $login = Yii::$app->request->post('login');
            $password = Yii::$app->request->post('password');
            $role = Yii::$app->request->post('role');

            $user = new User();
            $user->name = $name;
            $user->login = $login;
            $user->role = $role;
            $user->password_hash = Yii::$app->security->generatePasswordHash($password);

            if (!$user->save()) {
                Yii::$app->response->statusCode = 422; // Unprocessable Entity HTTP status code
                return ['error' => 'Unable to save user. Validation failed.', 'errors' => $user->errors];
            }

            $user->access_token = $this->generateJwtToken($user);

            return $user;
        } catch (\Exception $e) {
            Yii::$app->response->statusCode = 500; // Internal Server Error HTTP status code
            return ['error' => $e->getMessage()];
        }
    }
}
