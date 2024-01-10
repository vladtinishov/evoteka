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
 * @SWG\Tag(
 *   name="Order",
 *   description="Operations about orders"
 * )
 *
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
                        'update-status' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * @SWG\Get(
     *     path="/orders",
     *     tags={"Order"},
     *     summary="Lists all orders",
     *     @SWG\Response(
     *         response=200,
     *         description="Successful operation",
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Resource not found"
     *     ),
     *     security={
     *          {"BearerAuth": {}}
     *      }
     * )
     *
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
     * @SWG\Get(
     *     path="/orders/{id}",
     *     tags={"Order"},
     *     summary="View order",
     *     @SWG\Parameter(
     *           name="id",
     *           in="path",
     *           type="integer",
     *           description="ID of the order",
     *           required=true
     *       ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful operation",
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Resource not found"
     *     ),
     *     security={
     *          {"BearerAuth": {}}
     *      }
     * )
     *
     * Lists all Order models.
     *
     * @param $id
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
     * @SWG\Post(
     *     path="/orders/create",
     *     tags={"Order"},
     *     summary="Creates a new Order",
     *     @SWG\Parameter(
     *           name="body",
     *           in="body",
     *           required=true,
     *           @SWG\Schema(
     *               required={"user_id"},
     *               @SWG\Property(property="user_id", type="integer"),
     *               @SWG\Property(
     *                   property="products",
     *                   type="array",
     *                   @SWG\Items(
     *                       type="object",
     *                       @SWG\Property(property="id", type="integer"),
     *                       @SWG\Property(property="quantity", type="integer"),
     *                   )
     *               ),
     *           )
     *       ),
     *     consumes={"application/json"},
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
     *
     * Creates a new Order model.
     * If creation is successful, the browser will return the new order as JSON.
     *
     * @return array
     * @throws \Exception
     */
    public function actionCreate(): array
    {
        $requestData = Yii::$app->getRequest()->getBodyParams();
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
     * @SWG\Post(
     *     path="/orders/{id}/update",
     *     tags={"Order"},
     *     summary="Creates a new Order",
     *     @SWG\Parameter(
     *          name="id",
     *          in="path",
     *          type="integer",
     *          description="ID of the order to update payment_status",
     *          required=true
     *      ),
     *     @SWG\Parameter(
     *           name="body",
     *           in="body",
     *           required=true,
     *           @SWG\Schema(
     *               required={"user_id"},
     *               @SWG\Property(property="user_id", type="integer"),
     *               @SWG\Property(
     *                   property="products",
     *                   type="array",
     *                   @SWG\Items(
     *                       type="object",
     *                       @SWG\Property(property="id", type="integer"),
     *                       @SWG\Property(property="quantity", type="integer"),
     *                   )
     *               ),
     *           )
     *       ),
     *     consumes={"application/json"},
     *
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
     *
     * Creates a new Order model.
     * If creation is successful, the browser will return the new order as JSON.
     *
     * @param int $id
     * @return array
     * @throws \Exception
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
     * @SWG\Post(
     *     path="/orders/{id}/update-status",
     *     tags={"Order"},
     *     summary="Updates orders payment_status",
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         type="integer",
     *         description="ID of the order to update payment_status",
     *         required=true
     *     ),
     *     @SWG\Parameter(
     *         name="payment_status",
     *         in="formData",
     *         type="integer",
     *         description="Payment status of the order",
     *         required=true
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
     *
     * Update orders payment_status.
     * If update is successful, the browser will return the updated order as JSON.
     *
     * @param int $id ID
     * @return array
     */
    public function actionUpdateStatus(int $id): array
    {
        $requestData = Yii::$app->request->post();
        $paymentStatus = $requestData['payment_status'] ?? 0;

        try {
            $model = $this->findModel($id);
        } catch (NotFoundHttpException $e) {
            Yii::$app->response->statusCode = 404;
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['error' => 'The requested resource does not exist.'];
        }

        if ($paymentStatus) $model->payment_status = $paymentStatus;

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
     * @SWG\Post(path="/orders/{id}/delete",
     *     tags={"Order"},
     *     summary="Deletes order",
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         type="integer",
     *         description="ID of the order",
     *         required=true,
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Order collection response",
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
