<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace BitTools\SkyHub\Setup;

use BitTools\SkyHub\Model\Queue;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    use SetupHelper;

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->setup = $setup;
        $this->setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->installCustomerAttributeMappingTable();
            $this->installCustomerAttributeOptionsMappingTable();
            $this->installEntityIdTable();
            $this->installQueueTable();
        }

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @return $this
     *
     * @throws \Zend_Db_Exception
     */
    protected function installCustomerAttributeMappingTable()
    {
        $tableName = $this->getTable('bittools_skyhub_customer_attributes_mapping');

        /** Drop the table first. */
        $this->getConnection()->dropTable($tableName);

        /** @var Table $table */
        $table = $this->getConnection()
            ->newTable($tableName)
            ->addColumn('id', Table::TYPE_INTEGER, 10, [
                'identity' => true,
                'unsigned' => true,
                'primary' => true,
                'nullable' => false,
            ])
            ->addColumn('skyhub_code', Table::TYPE_TEXT, 255, [
                'nullable' => false,
            ])
            ->addColumn('skyhub_label', Table::TYPE_TEXT, 255, [
                'nullable' => true,
            ])
            ->addColumn('skyhub_description', Table::TYPE_TEXT, null, [
                'nullable' => true,
            ])
            ->addColumn('enabled', Table::TYPE_BOOLEAN, 1, [
                'nullable' => false,
                'default' => true,
            ])
            ->addColumn('cast_type', Table::TYPE_TEXT, 255, [
                'nullable' => false,
            ])
            ->addColumn('validation', Table::TYPE_TEXT, null, [
                'nullable' => true,
            ])
            ->addColumn('attribute_id', Table::TYPE_SMALLINT, 5, [
                'unsigned' => true,
                'nullable' => true,
                'default' => null,
            ])
            ->addColumn('required', Table::TYPE_BOOLEAN, 1, [
                'nullable' => false,
                'default' => true,
            ])
            ->addColumn('editable', Table::TYPE_BOOLEAN, 1, [
                'nullable' => false,
                'default' => true,
            ])
            ->addColumn('has_options', Table::TYPE_BOOLEAN, 1, [
                'nullable' => false,
                'default' => true,
            ])
            ->addColumn('created_at', Table::TYPE_DATETIME, null, [
                'nullable' => true,
                'unsigned' => true,
            ])
            ->addColumn('updated_at', Table::TYPE_DATETIME, null, [
                'nullable' => true,
                'unsigned' => true,
            ])
            ->setComment('SkyHub Customer Attributes Mapping.');;

        /** Add Unique Index */
        $this->addTableIndex($table, ['skyhub_code', 'attribute_id'], AdapterInterface::INDEX_TYPE_UNIQUE);

        /** Add Foreign Key */
        $this->addTableForeignKey($table, 'attribute_id', 'eav_attribute', 'attribute_id', Table::ACTION_SET_NULL);

        $this->getConnection()->createTable($table);

        return $this;
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @return $this
     *
     * @throws \Zend_Db_Exception
     */
    protected function installCustomerAttributeOptionsMappingTable()
    {
        $tableName = $this->getTable('bittools_skyhub_customer_attributes_mapping_options');

        /** Drop the table first. */
        $this->getConnection()->dropTable($tableName);

        /** @var Table $table */
        $table = $this->getConnection()
            ->newTable($tableName)
            ->addColumn('customer_attributes_mapping_id', Table::TYPE_INTEGER, 10, [
                'unsigned' => true,
                'nullable' => false
            ])
            ->addColumn('skyhub_code', Table::TYPE_TEXT, 255, [
                'nullable' => false,
            ])
            ->addColumn('skyhub_label', Table::TYPE_TEXT, 255, [
                'nullable' => true,
            ])
            ->addColumn('magento_value', Table::TYPE_TEXT, 255, [
                'nullable' => true,
                'default' => null
            ])
            ->setComment('SkyHub Customer Attribute Options Mapping.');

        /** Add Unique Index */
        $this->addTableIndex($table, ['customer_attributes_mapping_id', 'skyhub_code'], AdapterInterface::INDEX_TYPE_UNIQUE);

        /** Add Foreign Key */
        $this->addTableForeignKey($table, 'customer_attributes_mapping_id', 'bittools_skyhub_customer_attributes_mapping', 'id');

        $this->getConnection()->createTable($table);

        return $this;
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @return $this
     *
     * @throws \Zend_Db_Exception
     */
    protected function installEntityIdTable()
    {
        $tableName = $this->getTable('bittools_skyhub_entity_id');

        /** Drop the table first. */
        $this->getConnection()->dropTable($tableName);

        /** @var Table $table */
        $table = $this->getConnection()
            ->newTable($tableName)
            ->addColumn('id', Table::TYPE_INTEGER, 10, [
                'identity' => true,
                'unsigned' => true,
                'primary' => true,
                'nullable' => false,
            ])
            ->addColumn('entity_id', Table::TYPE_INTEGER, 10, [
                'nullable' => false,
                'primary' => true,
            ])
            ->addColumn('entity_type', Table::TYPE_TEXT, 255, [
                'nullable' => false,
            ])
            ->addColumn('store_id', Table::TYPE_SMALLINT, 5, [
                'unsigned' => true,
                'nullable' => false,
                'primary' => true,
                'default' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
            ])
            ->addColumn('force_integration', Table::TYPE_BOOLEAN, 1, [
                'unsigned' => true,
                'default' => false,
            ])
            ->addColumn('created_at', Table::TYPE_DATETIME, null, [
                'nullable' => true,
                'unsigned' => true,
            ])
            ->addColumn('updated_at', Table::TYPE_DATETIME, null, [
                'nullable' => true,
                'unsigned' => true,
            ]);

        /** Add Store ID foreign key. */
        $this->addTableForeignKey($table, 'store_id', 'store', 'store_id');

        /** Add indexes */
        $this->addTableIndex($table, 'entity_id')
            ->addTableIndex($table, 'entity_type')
            ->addTableIndex($table, ['entity_id', 'entity_type', 'store_id'], AdapterInterface::INDEX_TYPE_UNIQUE);

        $this->getConnection()->createTable($table);

        return $this;
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @return $this
     *
     * @throws \Zend_Db_Exception
     */
    protected function installQueueTable()
    {
        $tableName = $this->getTable('bittools_skyhub_queue');

        /** Drop the table first. */
        $this->getConnection()->dropTable($tableName);

        /** @var Table $table */
        $table = $this->getConnection()
            ->newTable($tableName)
            ->addColumn('id', Table::TYPE_INTEGER, 10, [
                'identity' => true,
                'unsigned' => true,
                'primary' => true,
                'nullable' => false,
            ])
            ->addColumn('entity_id', Table::TYPE_INTEGER, 10, [
                'nullable' => true,
            ])
            ->addColumn('reference', Table::TYPE_TEXT, 255, [
                'nullable' => true,
            ])
            ->addColumn('entity_type', Table::TYPE_TEXT, 255, [
                'nullable' => true,
            ])
            ->addColumn('status', Table::TYPE_INTEGER, 2, [
                'nullable' => false,
                'default' => 0,
            ])
            ->addColumn('process_type', Table::TYPE_INTEGER, 2, [
                'nullable' => false,
                'default' => Queue::PROCESS_TYPE_EXPORT,
            ])
            ->addColumn('messages', Table::TYPE_TEXT, null, [
                'nullable' => true,
            ])
            ->addColumn('additional_data', Table::TYPE_TEXT, null, [
                'nullable' => true,
            ])
            ->addColumn('can_process', Table::TYPE_INTEGER, 1, [
                'nullable' => false,
                'default' => 0,
            ])
            ->addColumn('store_id', Table::TYPE_SMALLINT, 5, [
                'unsigned' => true,
                'nullable' => false,
                'primary' => true,
                'default' => 0,
            ])
            ->addColumn('process_after', Table::TYPE_DATETIME, null, [
                'nullable' => true,
                'unsigned' => true,
            ], 'Schedule the process to run after this time if needed.')
            ->addColumn('created_at', Table::TYPE_DATETIME, null, [
                'nullable' => true,
                'unsigned' => true,
            ])
            ->addColumn('updated_at', Table::TYPE_DATETIME, null, [
                'nullable' => true,
                'unsigned' => true,
            ]);

        $this->addTableForeignKey($table, 'store_id', 'store', 'store_id');

        $this->addTableIndex($table, 'entity_id')
            ->addTableIndex($table, 'entity_type')
            ->addTableIndex($table, ['entity_id', 'entity_type', 'store_id'], AdapterInterface::INDEX_TYPE_UNIQUE);

        $this->getConnection()->createTable($table);

        return $this;
    }
}
