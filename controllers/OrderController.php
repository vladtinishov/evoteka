<?php

namespace app\controllers;

use app\models\Order;
use app\models\OrderProduct;
use PHPUnit\Exception;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\JsonParser;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * OrderController implements the CRUD actions for Order model.
 */
class OrderController extends Controller
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
     * Lists all Order models.
     *
     * @return array
     */
    public function actionIndex(): array
    {
        try {
            $dataProvider = new ActiveDataProvider([
                'query' => Order::find(),
            ]);

            return $dataProvider->models;
        } catch (NotFoundHttpException $e) {
            Yii::$app->response->statusCode = 404;
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['error' => 'The requested resource does not exist.'];
        }
    }

    /**
     * Displays a single Order model.
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
     * Creates a new Order model.
     * If creation is successful, the browser will return the new product as JSON.
     *
     * @return array
     */
    public function actionCreate(): array
    {
        $requestData = Yii::$app->request->post();
        $userId = $requestData['user_id'];

        $model = new Order();
        $model->user_id = $userId;

        try {
            $model->save();
            if (count($model->errors)) {
                Yii::$app->response->statusCode = 500;
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['errors' => $model->errors];
            }

            if (isset($requestData['products']) && is_array($requestData['products'])) {
                $this->saveOrderProducts($requestData['products'], $model->id);
            }

            return $model->attributes;
        } catch (Exception $e) {
            Yii::$app->response->statusCode = 500;
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['error' => 'Error during saving'];
        }
    }

    /**
     * Updates an existing Order model.
     * If update is successful, the browser will return the updated product as JSON.
     *
     * @param int $id ID
     * @return array
     */
    public function actionUpdate(int $id): array
    {
        $requestData = Yii::$app->request->post();
        $userId = $requestData['user_id'];
        $paymentStatus = $requestData['payment_status'] ?? 0;

        try {
            $model = $this->findModel($id);
        } catch (NotFoundHttpException $e) {
            Yii::$app->response->statusCode = 404;
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['error' => 'The requested resource does not exist.'];
        }

        if ($userId) $model->user_id = $userId;
        if ($paymentStatus) $model->payment_status = $paymentStatus;

        try {
            $model->save();
            if (count($model->errors)) {
                Yii::$app->response->statusCode = 500;
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['errors' => $model->errors];
            }

            if (isset($requestData['products']) && is_array($requestData['products'])) {
                OrderProduct::deleteAll(['order_id' => $model->id]);
                $this->saveOrderProducts($requestData['products'], $model->id);
            }

            return $model->attributes;
        } catch (Exception $e) {
            Yii::$app->response->statusCode = 500;
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['error' => 'Error during saving.'];
        }
    }

    /**
     * @throws \Exception
     */
    private function saveOrderProducts($products, $orderId)
    {
        foreach ($products as $productData) {
            $orderProduct = new OrderProduct();
            $orderProduct->order_id = $orderId;
            $orderProduct->product_id = $productData['id'];
            $orderProduct->quantity = $productData['quantity'];

            $orderProduct->save();

            if (count($orderProduct->errors)) {
                throw new \Exception('Error while saving order product');
            }
        }
    }

    /**
     * Deletes an existing Order model.
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
     * Finds the Order model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Order the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id): Order
    {
        if (($model = Order::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
