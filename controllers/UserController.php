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
 * UserController implements the CRUD actions for User model.
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
     * Lists all User models.
     *
     * @return array
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
     * Displays a single User model.
     * @param int $id ID
     * @return array
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
     * Creates a new User model.
     * If creation is successful, the browser will return the new product as JSON.
     *
     * @return array
     * @throws \yii\base\Exception
     * @throws \Exception
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

            $data = $model->attributes;
            unset($data['password_hash']);
            return $data;
        } catch (Exception $e) {
            Yii::$app->response->statusCode = 500;
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['error' => 'Error during saving'];
        }
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will return the updated product as JSON.
     *
     * @param int $id ID
     * @return array
     * @throws \yii\base\Exception
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

            $data = $model->attributes;
            unset($data['password_hash']);
            return $data;
        } catch (Exception $e) {
            Yii::$app->response->statusCode = 500;
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['error' => 'Error while saving.'];
        }
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will return success status as JSON.
     *
     * @param int $id ID
     * @return array
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
