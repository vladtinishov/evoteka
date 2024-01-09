<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%order_products}}`.
 */
class m240109_054626_create_order_products_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('order_product', [
            'order_id' => $this->integer()->notNull(),
            'product_id' => $this->integer()->notNull(),
            'quantity' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey('fk-order_product-order_id', 'order_product', 'order_id', 'order', 'id', 'CASCADE');
        $this->addForeignKey('fk-order_product-product_id', 'order_product', 'product_id', 'product', 'id', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Удаление внешних ключей
        $this->dropForeignKey('fk-order_product-order_id', 'order_product');
        $this->dropForeignKey('fk-order_product-product_id', 'order_product');

        $this->dropTable('order_product');
    }
}
