<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $name
 * @property string|null $login
 * @property string $password_hash
 * @property int $role
 *
 * @property Order[] $orders
 */
class User extends \yii\db\ActiveRecord
{
    public const CLIENT = 3;
    public const MANAGER = 2;
    public const ADMIN = 1;

    public string $access_token;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'password_hash', 'role'], 'required'],
            [['name', 'login'], 'string', 'max' => 50],
            [['password_hash'], 'string', 'max' => 255],
            [['login'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'login' => 'Login',
            'password_hash' => 'Password Hash',
            'role' => 'Role',
        ];
    }

    /**
     * Gets query for [[Orders]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::class, ['user_id' => 'id']);
    }

    public function isClient(): bool
    {
        return $this->role == self::CLIENT;
    }

    public function isManager(): bool
    {
        return $this->role == self::MANAGER;
    }

    public function isAdmin(): bool
    {
        return $this->role == self::ADMIN;
    }
}
