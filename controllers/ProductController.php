<?php

namespace app\controllers;

use app\models\Product;
use PHPUnit\Exception;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * ProductController implements the CRUD actions for Product model.
 */
class ProductController extends Controller
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
     * Lists all Product models.
     *
     * @return array
     */
    public function actionIndex(): array
    {
        try {
            $dataProvider = new ActiveDataProvider([
                'query' => Product::find(),
            ]);

            return $dataProvider->models;
        } catch (NotFoundHttpException $e) {
            Yii::$app->response->statusCode = 404;
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['error' => 'The requested resource does not exist.'];
        }
    }

    /**
     * Displays a single Product model.
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
     * Creates a new Product model.
     * If creation is successful, the browser will return the new product as JSON.
     *
     * @return array
     */
    public function actionCreate(): array
    {
        $name = Yii::$app->request->post('name');
        $description = Yii::$app->request->post('description');

        $model = new Product();
        $model->name = $name;
        $model->description = $description;

        try {
            $model->save();
            return $model->attributes;
        } catch (Exception $e) {
            Yii::$app->response->statusCode = 500;
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['error' => 'Error during saving'];
        }
    }

    /**
     * Updates an existing Product model.
     * If update is successful, the browser will return the updated product as JSON.
     *
     * @param int $id ID
     * @return array
     */
    public function actionUpdate(int $id): array
    {
        $name = Yii::$app->request->post('name');
        $description = Yii::$app->request->post('description');

        try {
            $model = $this->findModel($id);
        } catch (NotFoundHttpException $e) {
            Yii::$app->response->statusCode = 404;
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['error' => 'The requested resource does not exist.'];
        }

        if ($name) $model->name = $name;
        if ($description) $model->description = $description;

        try {
            $model->save();
            return $model->attributes;
        } catch (Exception $e) {
            Yii::$app->response->statusCode = 500;
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['error' => 'Error while saving.'];
        }
    }

    /**
     * Deletes an existing Product model.
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
     * Finds the Product model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Product the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id): Product
    {
        if (($model = Product::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
