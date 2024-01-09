<?php

use yii\db\Migration;

/**
 * Class m240109_052553_add_admin_user
 */
class m240109_052553_add_admin_user extends Migration
{
    /**
     * {@inheritdoc}
     * @throws \yii\base\Exception
     */
    public function safeUp()
    {
        $this->insert('user', [
            'name' => 'admin',
            'login' => 'admin',
            'password_hash' => Yii::$app->security->generatePasswordHash('adminpassword'),
            'role' => 'admin',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('user', ['login' => 'admin']);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240109_052553_add_admin_user cannot be reverted.\n";

        return false;
    }
    */
}
