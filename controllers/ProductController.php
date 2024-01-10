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
 * @SWG\Tag(
 *   name="Order",
 *   description="Operations about orders"
 * )
 *
 * OrderController implements the CRUD actions for Order model.
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
     * @SWG\Get(
     *     path="/products",
     *     tags={"Product"},
     *     summary="Lists all Product models.",
     *     description="",
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="Successful operation",
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Resource not found",
     *     ),
     *     security={
     *          {"BearerAuth": {}}
     *      }
     * )
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
     * @param int $id
     *
     * @return array
     *
     * @SWG\Get(
     *     path="/products/{id}",
     *     tags={"Product"},
     *     summary="Displays a single Product model.",
     *     description="",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         type="integer",
     *         description="ID of the product",
     *         required=true,
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful operation",
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Resource not found",
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
     * @return array
     *
     * @SWG\Post(
     *     path="/products/create",
     *     tags={"Product"},
     *     summary="Creates a new Product model.",
     *     description="If creation is successful, the browser will return the new product as JSON.",
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *     @SWG\Parameter(
     *         name="name",
     *         in="formData",
     *         type="string",
     *         description="Product's name",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="login",
     *         in="formData",
     *         type="string",
     *         description="Description's login",
     *         required=true,
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful operation",
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Error during saving",
     *     ),
     *     security={
     *          {"BearerAuth": {}}
     *      }
     * )
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
            if (count($model->errors)) {
                Yii::$app->response->statusCode = 500;
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['errors' => $model->errors];
            }

            return $model->attributes;
        } catch (Exception $e) {
            Yii::$app->response->statusCode = 500;
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['error' => 'Error during saving'];
        }
    }

    /**
     * @param int $id
     *
     * @return array
     *
     * @SWG\Put(
     *     path="/products/{id}/update",
     *     tags={"Product"},
     *     summary="Updates an existing Product model.",
     *     description="If update is successful, the browser will return the updated product as JSON.",
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *     @SWG\Parameter(
     *         in="path",
     *         name="id",
     *         type="integer",
     *         description="ID of the product",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *          name="name",
     *          in="formData",
     *          type="string",
     *          description="Product's name",
     *          required=true,
     *      ),
     *      @SWG\Parameter(
     *          name="login",
     *          in="formData",
     *          type="string",
     *          description="Description's login",
     *          required=true,
     *      ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful operation",
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Resource not found",
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Error while saving",
     *     ),
     *     security={
     *          {"BearerAuth": {}}
     *      }
     * )
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
            if (count($model->errors)) {
                Yii::$app->response->statusCode = 500;
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['errors' => $model->errors];
            }

            return $model->attributes;
        } catch (Exception $e) {
            Yii::$app->response->statusCode = 500;
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['error' => 'Error while saving.'];
        }
    }

    /**
     * @SWG\Post(path="/products/{id}/delete",
     *     tags={"Product"},
     *     summary="Deletes product",
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         type="integer",
     *         description="ID of the user",
     *         required=true,
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Product collection response",
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
