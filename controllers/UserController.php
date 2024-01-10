<?php

namespace app\controllers;

use app\models\User;
use PHPUnit\Exception;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * @SWG\Tag(
 *   name="Order",
 *   description="Operations about orders"
 * )
 *
 * @SWG\SecurityScheme(
 *     securityDefinition="BearerAuth",
 *     type="apiKey",
 *     in="header",
 *     name="Authorization",
 *     description="Bearer {token}"
 * )
 * OrderController implements the CRUD actions for Order model.
 */
class UserController extends Controller
{
    /**
     * @throws BadRequestHttpException
     */
    public function beforeAction($action): bool
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    /**
     * @inheritDoc
     */
    public function behaviors(): array
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                        'create' => ['POST'],
                        'update' => ['POST'],
                        'index' => ['GET'],
                        'view' => ['GET'],
                    ],
                ],
            ]
        );
    }

    /**
     * @SWG\Get(
     *     path="/users",
     *     tags={"User"},
     *     summary="Retrieves the collection of User resources.",
     *     @SWG\Response(
     *         response = 200,
     *         description = "User collection response",
     *     ),
     *     security={
     *         {"BearerAuth": {}}
     *     }
     * )
     */
    public function actionIndex(): array
    {
        try {
            $dataProvider = new ActiveDataProvider([
                'query' => User::find(),
            ]);

            return $dataProvider->models;
        } catch (NotFoundHttpException $e) {
            Yii::$app->response->statusCode = 404;
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['error' => 'The requested resource does not exist.'];
        }
    }

    /**
     * @SWG\Get(path="/users/{id}",
     *     tags={"User"},
     *     summary="Retrieves the collection of User resources.",
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         type="integer",
     *         description="ID of the user",
     *         required=true,
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="User collection response",
     *     ),
     *     security={
     *          {"BearerAuth": {}}
     *      }
     * )
     */
    public function actionView($id): array
    {
        try {
            return $this->findModel($id)->toArray();
        } catch (NotFoundHttpException $e) {
            Yii::$app->response->statusCode = 404;
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['error' => 'The requested resource does not exist.'];
        }
    }

    /**
     * @SWG\Post(
     *    path="/users/create",
     *    tags={"User"},
     *    summary="Creates a new User",
     *    description="If creation is successful, the browser will return the new user as JSON.",
     *    @SWG\Parameter(
     *        name="name",
     *        in="formData",
     *        type="string",
     *        description="User's name",
     *        required=true,
     *    ),
     *    @SWG\Parameter(
     *        name="login",
     *        in="formData",
     *        type="string",
     *        description="User's login",
     *        required=true,
     *    ),
     *    @SWG\Parameter(
     *        name="password",
     *        in="formData",
     *        type="string",
     *        description="User's password",
     *        required=true,
     *    ),
     *      @SWG\Parameter(
     *         name="role",
     *         in="formData",
     *         type="integer",
     *         description="User's role",
     *         required=true,
     *         enum={1, 2, 3},
     *         @SWG\Items(
     *             type="integer",
     *             enum={1, 2, 3},
     *         )
     *     ),
     *
*          @SWG\Response(
 *          response = 200,
 *          description = "User collection response",
 *      ),
     *     security={
     *          {"BearerAuth": {}}
     *      }
     * )
     */
    public function actionCreate(): array
    {
        try {
            $name = Yii::$app->request->post('name');
            $login = Yii::$app->request->post('login');
            $password = Yii::$app->request->post('password');
            $role = Yii::$app->request->post('role');

            $model = new User();
            $model->name = $name;
            $model->login = $login;
            $model->role = $role;
            $model->password_hash = Yii::$app->security->generatePasswordHash($password);

            $model->save();

            if (count($model->errors)) {
                Yii::$app->response->statusCode = 500;
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['errors' => $model->errors];
            }

            return $model->toArray(['id', 'name', 'login', 'role']);
        } catch (Exception $e) {
            Yii::$app->response->statusCode = 500;
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['error' => 'Error during saving'];
        }
    }

    /**
     * @SWG\Post(
     *    path="/users/{id}/update",
     *    tags={"User"},
     *    summary="Creates a new User",
     *    description="If creation is successful, the browser will return the new user as JSON.",
     *     @SWG\Parameter(
     *          name="id",
     *          in="path",
     *          type="integer",
     *          description="ID of the user",
     *          required=true,
     *      ),
     *    @SWG\Parameter(
     *        name="name",
     *        in="formData",
     *        type="string",
     *        description="User's name",
     *        required=true,
     *    ),
     *    @SWG\Parameter(
     *        name="login",
     *        in="formData",
     *        type="string",
     *        description="User's login",
     *        required=true,
     *    ),
     *    @SWG\Parameter(
     *        name="password",
     *        in="formData",
     *        type="string",
     *        description="User's password",
     *        required=true,
     *    ),
     *      @SWG\Parameter(
     *         name="role",
     *         in="formData",
     *         type="integer",
     *         description="User's role",
     *         required=true,
     *         enum={1, 2, 3},
     *         @SWG\Items(
     *             type="integer",
     *             enum={1, 2, 3},
     *         )
     *     ),
     *          @SWG\Response(
     *          response = 200,
     *          description = "User collection response",
     *      ),
     *     security={
     *          {"BearerAuth": {}}
     *      }
     * )
     */
    public function actionUpdate(int $id): array
    {
        $name = Yii::$app->request->post('name');
        $login = Yii::$app->request->post('login');
        $password = Yii::$app->request->post('password');
        $role = Yii::$app->request->post('role');

        try {
            $model = $this->findModel($id);
        } catch (NotFoundHttpException $e) {
            Yii::$app->response->statusCode = 404;
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['error' => 'The requested resource does not exist.'];
        }

        try {
            if ($name) $model->name = $name;
            if ($login) $model->login = $login;
            if ($password) $model->password_hash = Yii::$app->security->generatePasswordHash($password);
            if ($role) $model->role = $role;

            $model->save();
            if (count($model->errors)) {
                Yii::$app->response->statusCode = 500;
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['errors' => $model->errors];
            }

            return $model->toArray(['id', 'name', 'login', 'role']);
        } catch (Exception $e) {
            Yii::$app->response->statusCode = 500;
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['error' => 'Error while saving.'];
        }
    }

    /**
     * @SWG\Post(path="/users/{id}/delete",
     *     tags={"User"},
     *     summary="Retrieves the collection of User resources.",
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         type="integer",
     *         description="ID of the user",
     *         required=true,
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="User collection response",
     *     ),
     *     security={
     *          {"BearerAuth": {}}
     *      }
     * )
     */
    public function actionDelete($id): array
    {
        try {
            $model = $this->findModel($id);
            $model->delete();

            return ['status' => 'success'];
        } catch (NotFoundHttpException $e) {
            Yii::$app->response->statusCode = 404;
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['error' => 'The requested resource does not exist.'];
        }
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id): User
    {
        if (($model = User::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
