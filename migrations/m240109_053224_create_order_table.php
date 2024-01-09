<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%order}}`.
 */
class m240109_053224_create_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('order', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'payment_status' => $this->integer()->defaultValue(0), // 0 - не оплачено, 1 - оплачено
        ]);

        $this->addForeignKey('fk-order-user_id', 'order', 'user_id', 'user', 'id', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-order-user_id', 'order');
        $this->dropForeignKey('fk-order-product_id', 'order');

        $this->dropTable('order');
    }
}
