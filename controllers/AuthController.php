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

    /**
     * @SWG\Post(
     *     path="/auth/get-token",
     *     tags={"Authentication"},
     *     summary="Get JWT token",
     *     description="Endpoint to get a JWT token by providing a valid login and password.",
     *     @SWG\Parameter(
     *         name="login",
     *         in="formData",
     *         type="string",
     *         description="User login",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="password",
     *         in="formData",
     *         type="string",
     *         description="User password",
     *         required=true,
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="JWT token is generated successfully",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(property="token", type="string", description="JWT token"),
     *         ),
     *     ),
     *     @SWG\Response(
     *         response=401,
     *         description="Unauthorized. Invalid login or password.",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(property="error", type="string", description="Error message"),
     *         ),
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(property="error", type="string", description="Error message"),
     *         ),
     *     )
     * )
     */
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

    /**
     * @SWG\Post(
     *     path="/auth/register",
     *     tags={"Authentication"},
     *     summary="Register a new user",
     *     description="Endpoint to register a new user with the provided information.",
     *     @SWG\Parameter(
     *         name="name",
     *         in="formData",
     *         type="string",
     *         description="User name",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="login",
     *         in="formData",
     *         type="string",
     *         description="User login",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="password",
     *         in="formData",
     *         type="string",
     *         description="User password",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="role",
     *         in="formData",
     *         type="string",
     *         description="User role",
     *         required=true,
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="User is registered successfully",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(property="id", type="integer", description="User ID"),
     *             @SWG\Property(property="name", type="string", description="User name"),
     *             @SWG\Property(property="login", type="string", description="User login"),
     *             @SWG\Property(property="role", type="string", description="User role"),
     *             @SWG\Property(property="access_token", type="string", description="JWT token"),
     *         ),
     *     ),
     *     @SWG\Response(
     *         response=422,
     *         description="Unprocessable Entity. Unable to save user. Validation failed.",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(property="error", type="string", description="Error message"),
     *             @SWG\Property(property="errors", type="object", description="Validation errors"),
     *         ),
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(property="error", type="string", description="Error message"),
     *         ),
     *     )
     * )
     */
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
