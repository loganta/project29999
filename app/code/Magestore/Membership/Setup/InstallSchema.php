<?php

/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Membership
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Membership\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * class InstallSchema
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class InstallSchema implements InstallSchemaInterface
{
    const TABLE_MEMBERSHIP_GROUP = 'membership_group';
    const TABLE_MEMBERSHIP_GROUP_PRODUCT = 'membership_group_product';
    const TABLE_MEMBERSHIP_PACKAGE = 'membership_package';
    const TABLE_MEMBERSHIP_PACKAGE_GROUP = 'membership_package_group';
    const TABLE_MEMBERSHIP_PACKAGE_PRODUCT = 'membership_package_product';
    const TABLE_MEMBERSHIP_MEMBER = 'membership_member';
    const TABLE_MEMBERSHIP_PAYMENT_HISTORY = 'membership_payment_history';
    const TABLE_MEMBERSHIP_MEMBER_PACKAGE = 'membership_member_package';

    const TABLE_CATALOG_PRODUCT_ENTITY = 'catalog_product_entity';
    const TABLE_CUSTOMER_ENTITY = 'customer_entity';

    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        /**
         * Drop table if exist
         */

        $installer->getConnection()->dropTable($installer->getTable(self::TABLE_MEMBERSHIP_MEMBER_PACKAGE));
        $installer->getConnection()->dropTable($installer->getTable(self::TABLE_MEMBERSHIP_PACKAGE_GROUP));
        $installer->getConnection()->dropTable($installer->getTable(self::TABLE_MEMBERSHIP_PACKAGE_PRODUCT));
        $installer->getConnection()->dropTable($installer->getTable(self::TABLE_MEMBERSHIP_GROUP_PRODUCT));

        $installer->getConnection()->dropTable($installer->getTable(self::TABLE_MEMBERSHIP_GROUP));
        $installer->getConnection()->dropTable($installer->getTable(self::TABLE_MEMBERSHIP_MEMBER));
        $installer->getConnection()->dropTable($installer->getTable(self::TABLE_MEMBERSHIP_PACKAGE));
        $installer->getConnection()->dropTable($installer->getTable(self::TABLE_MEMBERSHIP_PAYMENT_HISTORY));

        $installer->startSetup();

        /*
         * Create table magestore_membership_membership_group
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable(self::TABLE_MEMBERSHIP_GROUP)
        )->addColumn(
            'group_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Group Id'
        )->addColumn(
            'group_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default' => ''],
            'Group Name'
        )->addColumn(
            'description',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            1024,
            ['nullable' => false, 'default' => ''],
            'Template Type Code'
        )->addColumn(
            'group_product_price',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            10,
            ['nullable' => false, 'default' => ''],
            'Group Product Price'
        )->addColumn(
            'group_status',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            1,
            ['unsigned' => true, 'nullable' => false, 'default' => 1],
            'Secret Key'
        )->addColumn(
            'priority',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['nullable' => false, 'unsigned' => true],
            'Priority'
        )->setComment(
            'Membership Group'
        );

        $installer->getConnection()->createTable($table);

        /**
         * create table magestore_membership_membership_group_product
         */

        $table = $installer->getConnection()->newTable(
            $installer->getTable(self::TABLE_MEMBERSHIP_GROUP_PRODUCT)
        )->addColumn(
            'group_product_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Group_Product Id'
        )->addColumn(
            'group_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['nullable' => false, 'unsigned' => true],
            'Group Id'
        )->addColumn(
            'product_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            ['nullable' => false, 'unsigned' => true],
            'Product Id'
        )->addForeignKey(
            'fk_group_id',
            'group_id',
            $installer->getTable(self::TABLE_MEMBERSHIP_GROUP),
            'group_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                self::TABLE_MEMBERSHIP_GROUP_PRODUCT,
                'product_id',
                self::TABLE_CATALOG_PRODUCT_ENTITY,
                'entity_id'
            ),
            'product_id',
            $installer->getTable(self::TABLE_CATALOG_PRODUCT_ENTITY),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Group_Product'
        );

        $installer->getConnection()->createTable($table);

        /**
         * create table magestore_membership_membership_package
         */

        $table = $installer->getConnection()->newTable(
            $installer->getTable(self::TABLE_MEMBERSHIP_PACKAGE)
        )->addColumn(
            'package_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'package_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default' => ''],
            'Package Name'
        )->addColumn(
            'package_price',
            \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
            null,
            ['nullable' => false, 'default' => 0.0000],
            'Package Price'
        )->addColumn(
            'duration',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            2,
            ['nullable' => false, 'unsigned' => true, 'default' => 0],
            'Package Duration'
        )->addColumn(
            'package_description',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            1024,
            ['nullable' => false, 'default' => ''],
            'Package Description'
        )->addColumn(
            'package_product_price',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            10,
            ['nullable' => false, 'default' => ''],
            'Package Product Price'
        )->addColumn(
            'product_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            ['nullable' => false, 'unsigned' => true],
            'Product Id'
        )->addColumn(
            'sort_order',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            3,
            ['nullable' => false, 'unsigned' => true, 'default' => 0],
            'Sort Order'
        )->addColumn(
            'is_featured',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            3,
            ['nullable' => false, 'unsigned' => true, 'default' => 0],
            'Featured Package'
        )->addColumn(
            'package_status',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            1,
            ['nullable' => false, 'default' => 1],
            'Package Status'
        )->addColumn(
            'discount_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            1,
            ['default' => 1],
            'Discount Type'
        )->addColumn(
            'time_unit',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            10,
            ['nullable' => false, 'default' => 'day'],
            'Time Unit'
        )->addColumn(
            'url_key',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true, 'default' => null],
            'Package Name'
        )->addColumn(
            'custom_option_discount',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            10,
            ['nullable' => false, 'default' => 'no'],
            'Custom Discount'
        )->addForeignKey(
            $installer->getFkName(
                self::TABLE_MEMBERSHIP_PACKAGE,
                'product_id',
                self::TABLE_CATALOG_PRODUCT_ENTITY,
                'entity_id'
            ),
            'product_id',
            $installer->getTable(self::TABLE_CATALOG_PRODUCT_ENTITY),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Membership Package'
        );

        $installer->getConnection()->createTable($table);

        /**
         * create table magestore_membership_membership_package_group
         */

        $table = $installer->getConnection()->newTable(
            $installer->getTable(self::TABLE_MEMBERSHIP_PACKAGE_GROUP)
        )->addColumn(
            'package_group_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'package_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'unsigned' => true],
            'Package Id'
        )->addColumn(
            'group_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'unsigned' => true],
            'Group Id'
        )->addIndex(
            $installer->getIdxName(
                $installer->getTable(self::TABLE_MEMBERSHIP_PACKAGE_GROUP),
                ['package_id'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['package_id'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addIndex(
            $installer->getIdxName(
                $installer->getTable(self::TABLE_MEMBERSHIP_PACKAGE_GROUP),
                ['group_id'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['group_id'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addForeignKey(
            $installer->getFkName(
                self::TABLE_MEMBERSHIP_PACKAGE_GROUP,
                'package_id',
                self::TABLE_MEMBERSHIP_PACKAGE,
                'package_id'
            ),
            'package_id',
            $installer->getTable(self::TABLE_MEMBERSHIP_PACKAGE),
            'package_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                self::TABLE_MEMBERSHIP_PACKAGE_GROUP,
                'group_id',
                self::TABLE_MEMBERSHIP_GROUP,
                'group_id'
            ),
            'group_id',
            $installer->getTable(self::TABLE_MEMBERSHIP_GROUP),
            'group_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Package_Group'
        );

        $installer->getConnection()->createTable($table);

        /**
         * create table magestore_membership_membership_package_product
         */

        $table = $installer->getConnection()->newTable(
            $installer->getTable(self::TABLE_MEMBERSHIP_PACKAGE_PRODUCT)
        )->addColumn(
            'package_product_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'package_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'unsigned' => true],
            'Package Id'
        )->addColumn(
            'product_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            ['nullable' => false, 'unsigned' => true],
            'Product Id'
        )->addIndex(
            $installer->getIdxName(
                $installer->getTable(self::TABLE_MEMBERSHIP_PACKAGE_PRODUCT),
                ['package_id'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['package_id'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addIndex(
            $installer->getIdxName(
                $installer->getTable(self::TABLE_MEMBERSHIP_PACKAGE_PRODUCT),
                ['product_id'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['product_id'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addForeignKey(
            $installer->getFkName(
                self::TABLE_MEMBERSHIP_PACKAGE_PRODUCT,
                'package_id',
                self::TABLE_MEMBERSHIP_PACKAGE,
                'package_id'
            ),
            'package_id',
            $installer->getTable(self::TABLE_MEMBERSHIP_PACKAGE),
            'package_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                self::TABLE_MEMBERSHIP_PACKAGE_PRODUCT,
                'product_id',
                self::TABLE_CATALOG_PRODUCT_ENTITY,
                'entity_id'
            ),
            'product_id',
            $installer->getTable(self::TABLE_CATALOG_PRODUCT_ENTITY),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Package_Product'
        );

        $installer->getConnection()->createTable($table);

        /**
         * create table magestore_membership_membership_member
         */

        $table = $installer->getConnection()->newTable(
            $installer->getTable(self::TABLE_MEMBERSHIP_MEMBER)
        )->addColumn(
            'member_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'unsigned' => true],
            'Customer Id'
        )->addColumn(
            'name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default' => ''],
            'Customer Name'
        )->addColumn(
            'email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default' => ''],
            'Customer Email'
        )->addColumn(
            'joined_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            null,
            ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
            'Joined Time'
        )->addColumn(
            'member_status',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            1,
            ['nullable' => false, 'default' => 1],
            'Product Id'
        )->addIndex(
            $installer->getIdxName(
                $installer->getTable(self::TABLE_MEMBERSHIP_MEMBER),
                ['customer_id'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['customer_id'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addForeignKey(
            $installer->getFkName(
                self::TABLE_MEMBERSHIP_MEMBER,
                'customer_id',
                self::TABLE_CUSTOMER_ENTITY,
                'entity_id'
            ),
            'customer_id',
            $installer->getTable(self::TABLE_CUSTOMER_ENTITY),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Membership member'
        );

        $installer->getConnection()->createTable($table);

        /**
         * create table magestore_membership_membership_member
         */

        $table = $installer->getConnection()->newTable(
            $installer->getTable(self::TABLE_MEMBERSHIP_PAYMENT_HISTORY)
        )->addColumn(
            'payment_history_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'member_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'unsigned' => true],
            'Member Id'
        )->addColumn(
            'order_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            ['nullable' => false, 'unsigned' => true],
            'Order Id'
        )->addColumn(
            'start_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            null,
            ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
            'Start Time'
        )->addColumn(
            'end_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            null,
            ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
            'End Time'
        )->addColumn(
            'duration',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            ['nullable' => false, 'unsigned' => true],
            'Duration'
        )->addColumn(
            'time_unit',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            11,
            ['nullable' => false, 'default' => 'day'],
            'Time Unit'
        )->addColumn(
            'package_product_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Package Product Id'
        )->addColumn(
            'package_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default' => ''],
            'Package Name'
        )->addColumn(
            'price',
            \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
            null,
            ['nullable' => false, 'default' => 0.0000],
            'Package Name'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Payment Status'
        )->setComment(
            'Payment History'
        );

        $installer->getConnection()->createTable($table);

        /**
         * create table magestore_membership_membership_member_package
         */

        $table = $installer->getConnection()->newTable(
            $installer->getTable(self::TABLE_MEMBERSHIP_MEMBER_PACKAGE)
        )->addColumn(
            'member_package_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'member_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'unsigned' => true],
            'Member Id'
        )->addColumn(
            'package_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'unsigned' => true],
            'Package Id'
        )->addColumn(
            'start_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            null,
            ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
            'Start Time'
        )->addColumn(
            'end_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            null,
            ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
            'End Time'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            1,
            ['nullable' => false, 'default' => 1],
            'Status'
        )->addColumn(
            'bought_item_total',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['nullable' => false, 'unsigned' => true, 'default' => 0],
            'Total Bought Items'
        )->addColumn(
            'saved_total',
            \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
            null,
            ['nullable' => false, 'default' => 0.0000],
            'Saved Total'
        )->addColumn(
            'order_ids',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['default' => null],
            'Customer Email'
        )->addIndex(
            $installer->getIdxName(
                $installer->getTable(self::TABLE_MEMBERSHIP_MEMBER_PACKAGE),
                ['member_id'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['member_id'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addIndex(
            $installer->getIdxName(
                $installer->getTable(self::TABLE_MEMBERSHIP_MEMBER_PACKAGE),
                ['package_id'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['package_id'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addForeignKey(
            $installer->getFkName(
                self::TABLE_MEMBERSHIP_MEMBER_PACKAGE,
                'member_id',
                self::TABLE_MEMBERSHIP_MEMBER,
                'member_id'
            ),
            'member_id',
            $installer->getTable(self::TABLE_MEMBERSHIP_MEMBER),
            'member_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                self::TABLE_MEMBERSHIP_MEMBER_PACKAGE,
                'package_id',
                self::TABLE_MEMBERSHIP_PACKAGE,
                'package_id'
            ),
            'package_id',
            $installer->getTable(self::TABLE_MEMBERSHIP_PACKAGE),
            'package_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Member_Package'
        );

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
