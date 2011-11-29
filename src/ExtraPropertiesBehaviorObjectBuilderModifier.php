<?php

/**
 * This file declare the ExtraPropertiesBehaviorObjectBuilderModifier class.
 *
 * @copyright (c) Carpe Hora SARL 2011
 * @since 2011-11-25
 * @license     MIT License
 */

/**
 * @author Julien Muetton <julien_muetton@carpe-hora.com>
 * @package propel.generator.behavior.extra_properties
 */
class ExtraPropertiesBehaviorObjectBuilderModifier
{
  protected $behavior, $table, $builder, $objectClassname, $peerClassname;

  public function __construct($behavior)
  {
    $this->behavior = $behavior;
    $this->table = $behavior->getTable();
  }

	protected function setBuilder($builder)
	{
		$this->builder = $builder;
		$this->objectClassname = $builder->getStubObjectBuilder()->getClassname();
		$this->queryClassname = $builder->getStubQueryBuilder()->getClassname();
		$this->peerClassname = $builder->getStubPeerBuilder()->getClassname();
	}

  protected function getParameter($key)
  {
    return $this->behavior->getParameter($key);
  }

  protected function getPropertyColumnPhpName($name = 'property_name_column')
  {
    return $this->behavior->getPropertyColumnForParameter($name)->getPhpName();
  }

  /**
   * Get the getter of the column of the behavior
   *
   * @return string The related getter, e.g. 'getVersion'
   */
  protected function getPropertyColumnGetter($name = 'property_name_column')
  {
    return 'get' . $this->getPropertyColumnPhpName($name);
  }

  /**
   * Get the setter of the column of the behavior
   *
   * @return string The related setter, e.g. 'setVersion'
   */
  protected function getPropertyColumnSetter($name = 'property_name_column')
  {
    return 'set' . $this->getPropertyColumnPhpName($name);
  }

  protected function getPropertyObjectsColumn()
  {
		$propertyTable = $this->behavior->getPropertyTable();
		$propertyARClassname = $this->builder->getNewStubObjectBuilder($propertyTable)->getClassname();
		$fks = $propertyTable->getForeignKeysReferencingTable($this->table->getName());
		$relCol = $this->builder->getRefFKPhpNameAffix($fks[0], $plural = true);
    return sprintf('coll%s', ucfirst($relCol));
  }

  protected function getPropertyObjectsGetter()
  {
		$propertyTable = $this->behavior->getPropertyTable();
		$propertyARClassname = $this->builder->getNewStubObjectBuilder($propertyTable)->getClassname();
		$fks = $propertyTable->getForeignKeysReferencingTable($this->table->getName());
		$relCol = $this->builder->getRefFKPhpNameAffix($fks[0], $plural = true);
    return sprintf('get%s', ucfirst($relCol));
  }

  protected function getPropertyObjectsSetter()
  {
		$propertyTable = $this->behavior->getPropertyTable();
		$propertyARClassname = $this->builder->getNewStubObjectBuilder($propertyTable)->getClassname();
		$fks = $propertyTable->getForeignKeysReferencingTable($this->table->getName());
		$relCol = $this->builder->getRefFKPhpNameAffix($fks[0], $plural = false);
    return sprintf('add%s', ucfirst($relCol));
  }

  protected function getPropertyTableName()
  {
		$propertyTable = $this->behavior->getPropertyTable();
		$propertyARClassname = $this->builder->getNewStubObjectBuilder($propertyTable)->getClassname();
    return $propertyARClassname;
  }

  protected function getPropertyActiveRecordClassName()
  {
		$propertyTable = $this->behavior->getPropertyTable();
		$propertyARClassname = $this->builder->getNewStubObjectBuilder($propertyTable)->getClassname();
    return $propertyARClassname;
  }

	protected function getActiveRecordClassName()
	{
		return $this->builder->getStubObjectBuilder()->getClassname();
	}

  public function objectAttributes($builder)
  {
    $script = $this->getSinglePropertyScript();
    $script .= $this->getMultiplePropertyScript();

    return $script;
  }

  public function objectMethods($builder)
  {
		$this->setBuilder($builder);
    $script = $this->getCommonPropertyMethods();
    $script .= $this->getInitializePropertiesMethod();
    $script .= $this->getSinglePropertyRegistrationMethods();
    $script .= $this->getMultiplePropertyRegistrationMethods();
    return $script;
  }

  public function objectFilter(&$script)
  {
    $parser = new PropelPHPParser($script, true);
    $construct = $parser->findMethod('__construct');
    if (!strlen($construct))
    {
      $construct = <<<EOF

/**
 * Initializes internal state of {$this->getActiveRecordClassName()} object.
 */
public function __construct()
{
  parent::__construct();
}

EOF;
      $parser->addMethodBefore('initializeProperties', $construct);
    }
    $construct = $this->updateConstructFunctionWithInitialize($construct);
    $parser->replaceMethod('__construct', $construct);
    $script = $parser->getCode();
  }

  protected function updateConstructFunctionWithInitialize($currentCode)
  {
    return preg_replace('#(\s*)parent::__construct\(\);#', <<<EOF
$1parent::__construct();
$1\$this->initializeProperties();
EOF
    , $currentCode);
  }

  /**
   * add methods to define extra properties.
   * @todo add default properties method generator.
   */
  protected function getInitializePropertiesMethod()
  {
    return <<<EOF
/**
 * initialize properties.
 * called in the constructor to add default properties.
 */
protected function initializeProperties()
{
}
EOF;
  }

  protected function getSinglePropertyScript()
  {
    return <<<EOF

/** the list of all single properties */
protected \$extraProperties = array();
EOF;
  }

  protected function getMultiplePropertyScript()
  {
    return <<<EOF

/** the list of all multiple properties */
protected \$multipleExtraProperties = array();
EOF;
  }

  protected function getSinglePropertyRegistrationMethods()
  {
    return <<<EOF
/**
 * Returns the list of registered extra properties
 * that can be set only once.
 *
 * @return array
 */
public function getRegisteredSingleProperties()
{
  return array_keys(\$this->extraProperties);
}

/**
 * Register a new single occurence property \$propertyName for the object.
 * The property will be accessible through {$this->getPropertyColumnGetter('property_name_column')} method.
 *
 * @param String  \$propertyName   the property name.
 * @param Mixed   \$defaultValue   default property value.
 * @return {$this->getActiveRecordClassName()}
 */
public function registerProperty(\$propertyName, \$defaultValue = null)
{
  \$propertyName = strtoupper(\$propertyName);
  /* comment this line to remove default value update ability
  if(!array_key_exists(\$propertyName, \$this->extraProperties))
  {
    \$this->extraProperties[\$propertyName] = \$defaultValue;
  }
  /*/
  \$this->extraProperties[\$propertyName] = \$defaultValue;
  //*/
  return \$this;
}

/**
 * Set a single occurence property.
 * If the property already exists, then it is ovverriden, ortherwise
 * new property is created.
 *
 * @param String  \$name   the property name.
 * @param Mixed   \$value  default property value.
 * @return {$this->getActiveRecordClassName()}
 */
public function setProperty(\$name, \$value)
{
  \$name = strtoupper(\$name);
  if(\$this->hasProperty(\$name))
  {
    \$properties = \$this->{$this->getPropertyObjectsGetter()}();
    foreach(\$properties as \$prop)
    {
      if(\$prop->{$this->getPropertyColumnGetter('property_name_column')}() == \$name)
      {
        \$prop->{$this->getPropertyColumnSetter('property_value_column')}(\$value);
        return \$this;
      }
    }
  }
  else
  {
    \$property = new {$this->getPropertyActiveRecordClassName()}();
    \$property->{$this->getPropertyColumnSetter('property_name_column')}(\$name);
    \$property->{$this->getPropertyColumnSetter('property_value_column')}(\$value);
    \$this->{$this->getPropertyObjectsSetter()}(\$property);
  }
  return \$this;
}

/**
 * Get the value of an extra property that can appear only once.
 *
 * @param   String  \$propertyName   the name of propertyto retrieve.
 * @param   Mixed   \$defaultValue   default value if property isn't set.
 * @return  Mixed.
 */
public function getProperty(\$propertyName, \$defaultValue = null)
{
  \$properties = \$this->{$this->getPropertyObjectsGetter()}();
  \$propertyName = strtoupper(\$propertyName);
  foreach(\$properties as \$prop)
  {
    if(\$prop->{$this->getPropertyColumnGetter('property_name_column')}() == \$propertyName)
    {
      return \$prop->{$this->getPropertyColumnGetter('property_value_column')}();
    }
  }
  return is_null(\$defaultValue)
            ? isset(\$this->extraProperties[\$propertyName])
                      ? \$this->extraProperties[\$propertyName]
                      : null
            : \$defaultValue;
}
EOF;
  }

  protected function getCommonPropertyMethods()
  {
    return <<<EOF
/**
 * convert propertyname in method to property name
 *
 * @param String \$name the camelized property name
 * @return String
 */
protected function extraPropertyNameFromMethod(\$name)
{
  \$tmp = \$name;
  \$tmp = str_replace('::', '/', \$tmp);
  \$tmp = preg_replace(array('/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'),
                      array('\\1_\\2', '\\1_\\2'), \$tmp);
  return strtolower(\$tmp);
}

/**
 * checks that the event defines a property with \$propertyName
 *
 * @todo optimize to make it stop on first occurence
 * @param String \$propertyName  nmae of the property to check.
 * @return Boolean
 */
public function hasProperty(\$propertyName)
{
  return \$this->countPropertiesByName(\$propertyName) > 0;
}

/**
 * Count the number of occurences of \$propertyName.
 *
 * @param   String  \$propertyName   the property to count.
 * @return  Integer
 */
public function countPropertiesByName(\$propertyName)
{
  \$count = 0;
  \$properties = \$this->{$this->getPropertyObjectsGetter()}();
  \$propertyName = strtoupper(\$propertyName);
  foreach(\$properties as \$prop)
  {
    if(\$prop->{$this->getPropertyColumnGetter('property_name_column')}() == \$propertyName)
    {
      \$count++;
    }
  }
  return \$count;
}

/**
 * Set the property with id \$id.
 * can only be used with an already set property
 * @return {$this->getActiveRecordClassName()}|false
 */
protected function setPropertyById(\$id, \$value)
{
  \$prop = \$this->getPropertyObjectById(\$id);
  if(\$prop instanceof {$this->getPropertyTableName()})
  {
    \$prop->{$this->getPropertyColumnSetter('property_value_column')}(\$value);
    return \$this;
  }
  else
  {
    return false;
  }
}

/**
 * Retrive property objects with \$propertyName.
 *
 * @param   String  \$propertyName   the properties to look for.
 * @return  Array
 */
protected function getPropertiesObjectsByName(\$propertyName)
{
  \$ret = array();
  \$properties = \$this->{$this->getPropertyObjectsGetter()}();
  \$propertyName = strtoupper(\$propertyName);
  foreach(\$properties as \$prop)
  {
    if(\$prop->{$this->getPropertyColumnGetter('property_name_column')}() == \$propertyName)
    {
      \$ret[\$prop->getId() ? \$prop->getId() : \$propertyName.'_'.count(\$ret)] = \$prop;
    }
  }
  return \$ret;
}

/**
 * Retrieve related property with \$id.
 * If property is not saved yet, id is the list index, created this way :
 * \$propertyName.'_'.\$index.
 *
 * @param Integer|String   \$id   the id of prorty to retrieve.
 * @return {$this->getPropertyActiveRecordClassName()}
 */
protected function getPropertyObjectById(\$id)
{
  if(is_numeric(\$id))
  {
    \$properties = \$this->{$this->getPropertyObjectsGetter()}();
    foreach(\$properties as \$prop)
    {
      if(\$prop->getId() == \$id)
      {
        return \$prop;
      }
    }
  }
  else
  {
    \$propertyName = substr(\$id, 0, strrpos(\$id, '_'));
    \$properties = \$this->getPropertiesObjectsByName(\$propertyName);
    return \$properties[\$id];
  }
}

/**
 * Check wether property with \$id is
 */
protected function isPropertyWithIdA(\$id, \$propertyName)
{
  \$prop = \$this->getPropertyObjectById(\$id);
  return \$prop && \$prop->{$this->getPropertyColumnGetter('property_name_column')}() == strtoupper(\$propertyName);
}

/**
 * wrapped function on update{Property} callback
 *
 * @param string          \$name   the property to update's type
 * @param mixed           \$value  the new value
 * @param integer|string  \$id     the id of the property to update
 *
 * @return Boolean|{$this->getPropertyActiveRecordClassName()}
 */
protected function setPropertyByNameAndId(\$name, \$value, \$id)
{
  if(\$this->isPropertyWithIdA(\$id, strtoupper(\$name)))
  {
    return \$this->setPropertyById(\$id, \$value);
  }
  return false;
}

/**
 * get the property with id \$id.
 * can only be used with an already set property
 */
protected function getPropertyById(\$id, \$defaultValue = null)
{
  \$prop = \$this->getPropertyObjectById(\$id);
  if(\$prop instanceof {$this->getPropertyActiveRecordClassName()})
  {
    return \$prop->{$this->getPropertyColumnGetter('property_value_column')}();
  }
  else
  {
    return \$defaultValue;
  }
}

/**
 * wrapped function on deleteProperty callback
 */
protected function deletePropertyByNameAndId(\$name, \$id)
{
  if(\$this->isPropertyWithIdA(\$id, strtoupper(\$name)))
  {
    return \$this->deletePropertyById(\$id);
  }
  return false;
}

/**
 * delete a multiple occurence property
 */
protected function deletePropertyById(\$id)
{
  \$prop = \$this->getPropertyObjectById(\$id);
  if(\$prop instanceof {$this->getPropertyActiveRecordClassName()})
  {
    if(!\$prop->isNew())
    {
      \$prop->delete();
    }
    \$this->{$this->getPropertyObjectsColumn()}->remove(\$this->{$this->getPropertyObjectsColumn()}->search(\$prop));
    return \$prop;
  }
  else
  {
    return false;
  }
}

/**
 * delete all properties with \$name
 */
public function deletePropertiesByName(\$name)
{
  \$props = \$this->getPropertiesObjectsByName(\$name);
  foreach(\$props as \$prop)
  {
    if(\$prop instanceof {$this->getPropertyActiveRecordClassName()})
    {
      \$prop->delete();
      \$this->{$this->getPropertyObjectsColumn()}->remove(\$this->{$this->getPropertyObjectsColumn()}->search(\$prop));
    }
  }
  return \$props;
}
EOF;
  }

  protected function getMultiplePropertyRegistrationMethods()
  {
    return <<<EOF
/**
 * returns the list of registered multiple properties
 *
 * @return array
 */
public function getRegisteredMultipleProperties()
{
  return array_keys(\$this->multipleExtraProperties);
}

/**
 * Register a new multiple occurence property \$propertyName for the object.
 * The properties will be accessible through {$this->getPropertyColumnGetter('property_name_column')}s method.
 *
 * @param String  \$propertyName   the property name.
 * @param Mixed   \$defaultValue   default property value.
 * @return {$this->getActiveRecordClassName()}
 */
public function registerMultipleProperty(\$propertyName, \$defaultValue = null)
{
  \$propertyName = strtoupper(\$propertyName);
  /* comment this line to remove default value update ability
  if(!array_key_exists(\$propertyName, \$this->multipleExtraProperties))
  {
    \$this->multipleExtraProperties[\$propertyName] = \$defaultValue;
  }
  /*/
  \$this->multipleExtraProperties[\$propertyName] = \$defaultValue;
  //*/
  return \$this;
}

/**
 * adds a multiple instance property to event
 *
 * @param String  \$propertyName   the name of the property to add.
 * @param Mixed   \$value          the value for new property.
 */
public function addProperty(\$propertyName, \$value)
{
  \$property = new {$this->getPropertyActiveRecordClassName()}();
  \$property->{$this->getPropertyColumnSetter('property_name_column')}(strtoupper(\$propertyName));
  \$property->{$this->getPropertyColumnSetter('property_value_column')}(\$value);
  \$this->{$this->getPropertyObjectsSetter()}(\$property);
  return \$this;
}

/**
 * returns an array of all matching values for given property
 * the array keys are the values ID
 * @todo enhance the case an id is given
 * @todo check the case there is an id but does not exists
 *
 * @param string  \$propertyName     the name of properties to retrieve
 * @param mixed   \$default          the default value to use
 * @param Integer \$id               the unique id of the property to retrieve
 *
 * @return array  the list of matching properties (prop_id => value).
 */
public function getPropertiesByName(\$propertyName, \$default = array(), \$id = null)
{
  \$ret = array();
  \$properties = \$this->getPropertiesObjectsByName(\$propertyName);
  foreach(\$properties as \$key => \$prop)
  {
    \$ret[\$key] = \$prop->{$this->getPropertyColumnGetter('property_value_column')}();
  }
  // is there a property id ?
  if (!is_null(\$id) && isset(\$ret[\$id]))
  {
    return \$ret[\$id];
  }
  // no results ?
  if(!count(\$ret))
  {
    return \$default;
  }
  return \$ret;
}

EOF;
  }

  public function objectCall()
  {
    if(floatval(substr(Propel::VERSION,0,3)) >= 1.5)
    {
      $methodVar = '$name';
      $paramVar = '$params';
    }
    else
    {
      $methodVar = '$method';
      $paramVar = '$arguments';
    }

    return <<<EOF
// calls the registered properties dedicated functions
if(in_array(\$methodName = substr({$methodVar}, 0,3), array('add', 'set', 'has', 'get')))
{
  \$propertyName = strtoupper(\$this->extraPropertyNameFromMethod(substr({$methodVar}, 3)));
}
else if(in_array(\$methodName = substr({$methodVar}, 0,5), array('count', 'clear')))
{
  \$propertyName = strtoupper(\$this->extraPropertyNameFromMethod(substr({$methodVar}, 5)));
}
else if(in_array(\$methodName = substr({$methodVar}, 0,6), array('delete', 'update')))
{
  \$propertyName = strtoupper(\$this->extraPropertyNameFromMethod(substr({$methodVar}, 6)));
}
if(isset(\$propertyName))
{
  if(array_key_exists(\$propertyName, \$this->extraProperties))
  {
    switch(\$methodName)
    {
      case 'add':
      case 'set':
        \$callable = array(\$this, 'setProperty');
        break;
      case 'get':
        \$callable = array(\$this, 'getProperty');
        break;
      case 'has':
        \$callable = array(\$this, 'hasProperty');
        break;
      case 'count':
        \$callable = array(\$this, 'countPropertiesByName');
        break;
      case 'clear':
      case 'delete':
        \$callable = array(\$this, 'deletePropertiesByName');
        break;
      case 'update':
        \$callable = array(\$this, 'setPropertyByName');
        break;
    }
  }
  else if(array_key_exists(\$propertyName, \$this->multipleExtraProperties) ||
          ('S' == substr(\$propertyName, -1) && array_key_exists(\$propertyName = substr(\$propertyName, 0, -1), \$this->multipleExtraProperties)))
  {
    switch(\$methodName)
    {
      case 'add':
      case 'set':
        \$callable = array(\$this, 'addProperty');
        break;
      case 'get':
        \$callable = array(\$this, 'getPropertiesByName');
        break;
      case 'has':
        \$callable = array(\$this, 'hasProperty');
        break;
      case 'count':
        \$callable = array(\$this, 'countPropertiesByName');
        break;
      case 'clear':
        \$callable = array(\$this, 'deletePropertiesByName');
        break;
      case 'delete':
        \$callable = array(\$this, 'deletePropertyByNameAndId');
        break;
      case 'update':
        \$callable = array(\$this, 'setPropertyByNameAndId');
        break;
    }
  }
  //* no error throw to make sure other behaviors can be called.
  else
  {
    throw new RuntimeException(sprintf('Unknown property %s.<br />possible single properties: %s<br />possible multiple properties', \$propertyName, join(',', array_keys(\$this->extraProperties)), join(',', array_keys(\$this->multipleExtraProperties))));
  }
  //*/
  if(isset(\$callable))
  {
    array_unshift({$paramVar}, \$propertyName);
    return call_user_func_array(\$callable, {$paramVar});
  }

}
EOF
    ;
  }
} // END OF ExtraPropertiesBehaviorObjectBuilderModifier
