<?php

/**
 * This file declare the ExtraPropertiesBehaviorQueryBuilderModifier class.
 *
 * @copyright (c) Carpe Hora SARL 2011
 * @since 2011-11-25
 * @license     MIT License
 */

/**
 * @author Julien Muetton <julien_muetton@carpe-hora.com>
 * @package propel.generator.behavior.extra_properties
 */
class ExtraPropertiesBehaviorQueryBuilderModifier
{
    protected $behavior, $table, $builder, $objectClassname, $peerClassname, $queryClassname;

    public function __construct($behavior)
    {
        $this->behavior = $behavior;
        $this->table = $behavior->getTable();
    }

    protected function getParameter($key)
    {
        return $this->behavior->getParameter($key);
    }

    protected function setBuilder($builder)
    {
        $this->builder = $builder;
        $this->objectClassname = $builder->getStubObjectBuilder()->getClassname();
        $this->queryClassname = $builder->getStubQueryBuilder()->getClassname();
        $this->peerClassname = $builder->getStubPeerBuilder()->getClassname();
    }

    protected function getPropertyTableName()
    {
        $propertyTable = $this->behavior->getPropertyTable();
        $propertyARClassname = $this->builder->getNewStubObjectBuilder($propertyTable)->getClassname();
        return $propertyARClassname;
    }

    public function queryMethods($builder)
    {
        $this->setBuilder($builder);
        $script = '';

        $script .= $this->addFilterByExtraProperty($builder);
        $script .= $this->addFilterByExtraPropertyWithDefault($builder);

        return $script;
    }


    protected function addFilterByExtraProperty($builder)
    {
        return $this->behavior->renderTemplate('queryFilterByExtraProperty', array(
            'peerClassName'                 => $this->peerClassname,
            'shouldNormalize'               => 'true' === $this->getParameter('normalize'),
            'queryClassName'                => $this->queryClassname,
            'joinExtraPropertyTableMethod'  => $this->getJoinExtraPropertyTableMethodName(),
            'propertyPropertyNameColName'   => $this->getPropertyColumnPhpName('property_name_column'),
            'propertyPropertyValueColName'  => $this->getPropertyColumnPhpName('property_value_column'),
        ));
    }

    protected function addFilterByExtraPropertyWithDefault($builder)
    {
        return $this->behavior->renderTemplate('queryFilterByExtraPropertyWithDefault', array(
            'peerClassName'                 => $this->peerClassname,
            'shouldNormalize'               => 'true' === $this->getParameter('normalize'),
            'queryClassName'                => $this->queryClassname,
            'joinExtraPropertyTableMethod'  => $this->getJoinExtraPropertyTableMethodName(),
            'propertyPropertyNameColName'   => $this->getPropertyColumnPhpName('property_name_column'),
            'propertyPropertyValueColName'  => $this->getPropertyColumnPhpName('property_value_column'),
        ));
    }


    protected function getJoinExtraPropertyTableMethodName()
    {
        return 'leftJoin' . $this->getPropertyTableName();
    }


    protected function getPropertyColumnPhpName($name = 'property_name_column')
    {
        return $this->behavior->getPropertyColumnForParameter($name)->getPhpName();
    }

} // END OF ExtraPropertiesBehaviorQueryBuilderModifier
