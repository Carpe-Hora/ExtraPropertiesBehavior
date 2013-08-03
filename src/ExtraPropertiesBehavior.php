<?php
/**
 * This file declare the ExtraPropertiesBehavior class.
 *
 * @copyright (c) Carpe Hora SARL 2011
 * @since 2011-11-25
 * @license     MIT License
 */

require_once __DIR__ . '/ExtraPropertiesBehaviorObjectBuilderModifier.php';
require_once __DIR__ . '/ExtraPropertiesBehaviorQueryBuilderModifier.php';
require_once __DIR__ . '/ExtraPropertiesBehaviorPeerBuilderModifier.php';
require_once __DIR__ . '/Inflector.php';

/**
 * @author Julien Muetton <julien_muetton@carpe-hora.com>
 * @package propel.generator.behavior.extra_properties
 */
class ExtraPropertiesBehavior extends Behavior
{
    /** parameters default values */
    protected $parameters = array(
      'properties_table'      => null,
      'property_name_column'  => 'property_name',
      'property_value_column' => 'property_value',
      'default_properties'    => '',
      'normalize'             => 'true',
      'throw_error'           => 'true'
    );

    protected
      $propertyTable,
      $objectBuilderModifier,
      $queryBuilderModifier,
      $peerBuilderModifier;

    public function modifyDatabase()
    {
        foreach ($this->getDatabase()->getTables() as $table) {
            if ($table->hasBehavior($this->getName())) {
              // don't add the same behavior twice
              continue;
            }
            if (property_exists($table, 'isExtraPropertyTable')) {
              // don't add the behavior to property tables
              continue;
            }
            $b = clone $this;
            $table->addBehavior($b);
      }
    }

    public function modifyTable()
    {
        $this->addPropertyTable();
        $this->addForeignKeyIfNone();
    }

    protected function addPropertyTable()
    {
        $table = $this->getTable();
        $database = $table->getDatabase();
        $propertyTableName = $this->getParameter('properties_table')
                              ? $this->getParameter('properties_table')
                              : $table->getName() . '_extra_property';

        if (!$database->hasTable($propertyTableName)) {
            $propertyTable = $database->addTable(array(
                  'name'      => $propertyTableName,
                  'phpName'   => $this->getPropertyTableName(),
                  'package'   => $table->getPackage(),
                  'schema'    => $table->getSchema(),
                  'namespace' => $table->getNamespace() ? '\\' . $table->getNamespace() : null,
            ));
            $propertyTable->isExtraPropertyTable = true;
            // add id column
            $pk = $propertyTable->addColumn(array(
                'name'					=> 'id',
                'autoIncrement' => 'true',
                'type'					=> 'INTEGER',
                'primaryKey'    => 'true'
            ));
            $pk->setNotNull(true);
            $pk->setPrimaryKey(true);
            // every behavior adding a table should re-execute database behaviors
            foreach ($database->getBehaviors() as $behavior) {
                $behavior->modifyDatabase();
            }
            // add columns
            $propertyTable->addColumn(array(
                'name'      => $this->getParameter('property_name_column'),
                'type'      => 'VARCHAR',
                'required'  => 'true'
            ));
            $propertyTable->addColumn(array(
                'name'      => $this->getParameter('property_value_column'),
                'type'      => 'LONGVARCHAR',
            ));

            $this->propertyTable = $propertyTable;
        }
        else {
            $this->propertyTable = $database->getTable($propertyTableName);
        }
    }

    protected function addForeignKeyIfNone()
    {
        $table = $this->getTable();
        foreach ($this->propertyTable->getForeignKeys() as $fk)
        {
          if ($table->getCommonName() === $fk->getForeignTableCommonName())
          {
            return ;
          }
        }
        // create the foreign key
        $fk = new ForeignKey();
        $fk->setForeignTableCommonName($table->getCommonName());
        $fk->setForeignSchemaName($table->getSchema());
        $fk->setOnDelete('CASCADE');
        $fk->setOnUpdate(null);
        $tablePKs = $table->getPrimaryKey();
        $tableName = $table->getName();
        foreach ($table->getPrimaryKey() as $key => $column) {
          $ref_column = $column->getAttributes();
          $ref_column['name'] = sprintf('%s_%s', $this->getSingularizedTableName($tableName), $ref_column['name']);
          $ref_column['phpName'] = null;
          $ref_column['required'] = 'true';
          $ref_column['primaryKey'] = 'false';
          $ref_column['autoIncrement'] = 'false';
          $ref_column = $this->propertyTable->addColumn($ref_column);
          $fk->addReference($ref_column, $column);
        }
        $this->propertyTable->addForeignKey($fk);
    }

    public function getSingularizedTableName($name) {

        $nameChunks = explode('_', $name);
        $endChunk = array_pop($nameChunks);

        array_push($nameChunks, Inflector::singularize($endChunk));
        
        return implode('_',$nameChunks);

    }

    public function getPropertyTable()
    {
      return $this->propertyTable;
    }

    protected function getPropertyTableName()
    {
      return $this->getTable()->getPhpName() . 'ExtraProperty';
    }

    public function getObjectBuilderModifier()
    {
      if (is_null($this->objectBuilderModifier))
      {
        $this->objectBuilderModifier = new ExtraPropertiesBehaviorObjectBuilderModifier($this);
      }
      return $this->objectBuilderModifier;
    }

    public function getQueryBuilderModifier()
    {
      if (is_null($this->queryBuilderModifier))
      {
        $this->queryBuilderModifier = new ExtraPropertiesBehaviorQueryBuilderModifier($this);
      }
      return $this->queryBuilderModifier;
    }

    /**
     *
     * @param type $parameter
     * @return type
     */
    public function getPeerBuilderModifier()
    {
      if (is_null($this->peerBuilderModifier))
      {
        $this->peerBuilderModifier = new ExtraPropertiesBehaviorPeerBuilderModifier($this);
      }
      return $this->peerBuilderModifier;
    }

    public function getPropertyColumnForParameter($parameter)
    {
      return $this->propertyTable->getColumn($this->getParameter($parameter));
    }
} // END OF ExtraPropertiesBehavior
