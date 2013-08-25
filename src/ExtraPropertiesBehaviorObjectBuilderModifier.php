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
    protected $behavior, $table, $builder, $objectClassname, $peerClassname, $pluralizer;

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

        // Add namespace for PHP >= 5.3
        $builder->declareClass('RuntimeException');
    }

    protected function getPluralForm($root)
    {
        if ($this->pluralizer === null) {
            $this->pluralizer = new StandardEnglishPluralizer();
        }

        return $this->pluralizer->getPluralForm($root);
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
        $script .= $this->getGetExtraPropertiesMethods();
        return $script;
    }

    public function objectFilter(&$script)
    {
        $parser = new PropelPHPParser($script, true);
        $construct = $parser->findMethod('__construct');
        $propertiesName = ucfirst($this->getPluralForm($this->getParameter('property_name')));

        if (!strlen($construct)) {
            $construct = <<<EOF

/**
 * Initializes internal state of {$this->getActiveRecordClassName()} object.
 */
public function __construct()
{
  parent::__construct();
}

EOF;
            $parser->addMethodBefore('initialize'.$propertiesName, $construct);
        }
        $construct = $this->updateConstructFunctionWithInitialize($construct);
        $parser->replaceMethod('__construct', $construct);
        $script = $parser->getCode();
    }

    protected function updateConstructFunctionWithInitialize($currentCode)
    {
        $propertiesName = ucfirst($this->getPluralForm($this->getParameter('property_name')));

        return preg_replace('#(\s*)parent::__construct\(\);#', <<<EOF
$1parent::__construct();
$1\$this->initialize$propertiesName();
EOF
        , $currentCode);
    }

    /**
     * add methods to define extra properties.
     * @todo add default properties method generator.
     */
    protected function getInitializePropertiesMethod()
    {
        $propertiesName = $this->getPluralForm($this->getParameter('property_name'));
        $propertiesNameMethod = ucfirst($propertiesName);

        return <<<EOF
/**
 * initialize $propertiesName.
 * called in the constructor to add default $propertiesName.
 */
protected function initialize$propertiesNameMethod()
{
}
EOF;
    }

    protected function getSinglePropertyScript()
    {
        $propertiesName = $this->getPluralForm($this->getParameter('property_name'));
        $propertiesNameMethod = ucfirst($propertiesName);

        return <<<EOF

/** the list of all single $propertiesName */
protected \$extra$propertiesNameMethod = array();
EOF;
    }

    protected function getMultiplePropertyScript()
    {
        $propertiesName = $this->getPluralForm($this->getParameter('property_name'));
        $propertiesNameMethod = ucfirst($propertiesName);

        return <<<EOF

/** the list of all multiple $propertiesName */
protected \$multipleExtra$propertiesNameMethod = array();
EOF;
    }

    protected function getSinglePropertyRegistrationMethods()
    {
        $propertyName = $this->getParameter('property_name');
        $propertyNameMethod = ucfirst($propertyName);
        $propertiesName = $this->getPluralForm($propertyName);
        $propertiesNameMethod = ucfirst($propertiesName);

        return <<<EOF
/**
 * Returns the list of registered $propertiesName
 * that can be set only once.
 *
 * @return array
 */
public function getRegisteredSingle$propertiesNameMethod()
{
  return array_keys(\$this->extra$propertiesNameMethod);
}

/**
 * Register a new single occurence $propertyName \${$propertyName}Name for the object.
 * The property will be accessible through {$this->getPropertyColumnGetter('property_name_column')} method.
 *
 * @param String  \${$propertyName}Name   the $propertyName name.
 * @param Mixed   \$defaultValue   default $propertyName value.
 *
 * @return {$this->getActiveRecordClassName()}
 */
public function register$propertyNameMethod(\${$propertyName}Name, \$defaultValue = null)
{
  \${$propertyName}Name = {$this->peerClassname}::normalize{$propertyNameMethod}Name(\${$propertyName}Name);
  /* comment this line to remove default value update ability
  if(!array_key_exists(\${$propertyName}Name, \$this->extra$propertiesNameMethod))
  {
    \$this->extraProperties[\${$propertyName}Name] = \$defaultValue;
  }
  /*/
  \$this->extraProperties[\${$propertyName}Name] = \$defaultValue;
  //*/
  return \$this;
}

/**
 * Set a single occurence $propertyName.
 * If the $propertyName already exists, then it is overriden, ortherwise
 * new $propertyName is created.
 *
 * @param String    \$name   the $propertyName name.
 * @param Mixed     \$value  default $propertyName value.
 * @param PropelPDO \$con    Optional connection object
 *
 * @return {$this->getActiveRecordClassName()}
 */
public function set$propertyNameMethod(\$name, \$value, PropelPDO \$con = null)
{
  \$name = {$this->peerClassname}::normalize{$propertyNameMethod}Name(\$name);
  if(\$this->has$propertyNameMethod(\$name, \$con))
  {
    \${$propertiesName} = \$this->{$this->getPropertyObjectsGetter()}(null, \$con);
    foreach(\${$propertiesName} as \${$propertyName})
    {
      if(\${$propertyName}->{$this->getPropertyColumnGetter('property_name_column')}() == \$name)
      {
        \${$propertyName}->{$this->getPropertyColumnSetter('property_value_column')}({$this->peerClassname}::normalize{$propertyNameMethod}Value(\$value));
        return \$this;
      }
    }
  }
  else
  {
    \${$propertyName} = new {$this->getPropertyActiveRecordClassName()}();
    \${$propertyName}->{$this->getPropertyColumnSetter('property_name_column')}(\$name);
    \${$propertyName}->{$this->getPropertyColumnSetter('property_value_column')}({$this->peerClassname}::normalize{$propertyNameMethod}Value(\$value));
    \$this->{$this->getPropertyObjectsSetter()}(\${$propertyName});
  }
  return \$this;
}

/**
 * Get the value of an extra $propertyName that can appear only once.
 *
 * @param   String    \${$propertyName}Name   the name of $propertyName retrieve.
 * @param   Mixed     \$defaultValue   default value if $propertyName isn't set.
 * @param   PropelPDO \$con            Optional connection object
 *
 * @return  Mixed
 */
public function get$propertyNameMethod(\${$propertyName}Name, \$defaultValue = null, PropelPDO \$con = null)
{
  \${$propertiesName} = \$this->{$this->getPropertyObjectsGetter()}(null, \$con);
  \${$propertyName}Name = {$this->peerClassname}::normalize{$propertyNameMethod}Name(\${$propertyName}Name);
  foreach(\${$propertiesName} as \${$propertyName})
  {
    if(\${$propertyName}->{$this->getPropertyColumnGetter('property_name_column')}() == \${$propertyName}Name)
    {
      return \${$propertyName}->{$this->getPropertyColumnGetter('property_value_column')}();
    }
  }
  return is_null(\$defaultValue)
            ? isset(\$this->extraProperties[\${$propertyName}Name])
                      ? \$this->extraProperties[\${$propertyName}Name]
                      : null
            : \$defaultValue;
}
EOF;
    }

    protected function getCommonPropertyMethods()
    {
        $propertyName = $this->getParameter('property_name');
        $propertyNameMethod = ucfirst($propertyName);
        $propertiesName = $this->getPluralForm($propertyName);
        $propertiesNameMethod = ucfirst($propertiesName);

        return <<<EOF
/**
 * convert a value to a valid $propertyName name
 *
 * @param String \$name the camelized {$propertyName} name
 *
 * @return String
 */
protected function extra{$propertyNameMethod}NameFromMethod(\$name)
{
  \$tmp = \$name;
  \$tmp = str_replace('::', '/', \$tmp);
  \$tmp = preg_replace(array('/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'),
                      array('\\1_\\2', '\\1_\\2'), \$tmp);
  return strtolower(\$tmp);
}

/**
 * checks that the event defines a $propertyName with \${$propertyName}Name
 *
 * @todo optimize to make it stop on first occurence
 *
 * @param String    \${$propertyName}Name  name of the $propertyName to check.
 * @param PropelPDO \$con           Optional connection object
 *
 * @return Boolean
 */
public function has$propertyNameMethod(\${$propertyName}Name, PropelPDO \$con = null)
{
  return \$this->count{$propertiesNameMethod}ByName(\${$propertyName}Name, \$con) > 0;
}

/**
 * Count the number of occurences of \${$propertyName}Name.
 *
 * @param   String    \${$propertyName}Name   the $propertyName to count.
 * @param   PropelPDO \$con            Optional connection object
 *
 * @return  Integer
 */
public function count{$propertiesNameMethod}ByName(\${$propertyName}Name, PropelPDO \$con = null)
{
  \$count = 0;
  \${$propertiesName} = \$this->{$this->getPropertyObjectsGetter()}(null, \$con);
  \${$propertyName}Name = {$this->peerClassname}::normalize{$propertyNameMethod}Name(\${$propertyName}Name);
  foreach(\${$propertiesName} as \${$propertyName})
  {
    if(\${$propertyName}->{$this->getPropertyColumnGetter('property_name_column')}() == \${$propertyName}Name)
    {
      \$count++;
    }
  }
  return \$count;
}

/**
 * Set the $propertyName with id \$id.
 * can only be used with an already set $propertyName
 *
 * @param   PropelPDO \$con Optional connection object
 *
 * @return {$this->getActiveRecordClassName()}|false
 */
protected function set{$propertyNameMethod}ById(\$id, \$value, PropelPDO \$con = null)
{
  \${$propertyName} = \$this->get{$propertyNameMethod}ObjectById(\$id, \$con);
  if(\${$propertyName} instanceof {$this->getPropertyTableName()})
  {
    \${$propertyName}->{$this->getPropertyColumnSetter('property_value_column')}({$this->peerClassname}::normalize{$propertyNameMethod}Value(\$value));
    return \$this;
  }
  else
  {
    return false;
  }
}

/**
 * Retrive $propertyName objects with \${$propertyName}Name.
 *
 * @param   String    \${$propertyName}Name the {$propertiesName} to look for.
 * @param   PropelPDO \$con          Optional connection object
 *
 * @return  Array
 */
protected function get{$propertiesNameMethod}ObjectsByName(\${$propertyName}Name, PropelPDO \$con = null)
{
  \$ret = array();
  \${$propertiesName} = \$this->{$this->getPropertyObjectsGetter()}(null, \$con);
  \${$propertyName}Name = {$this->peerClassname}::normalize{$propertyNameMethod}Name(\${$propertyName}Name);
  foreach(\${$propertiesName} as \${$propertyName})
  {
    if(\${$propertyName}->{$this->getPropertyColumnGetter('property_name_column')}() == \${$propertyName}Name)
    {
      \$ret[\${$propertyName}->getId() ? \${$propertyName}->getId() : \${$propertyName}Name.'_'.count(\$ret)] = \${$propertyName};
    }
  }
  return \$ret;
}

/**
 * Retrieve related $propertyName with \$id.
 * If $propertyName is not saved yet, id is the list index, created this way :
 * \${$propertyName}Name.'_'.\$index.
 *
 * @param Integer|String  \$id   the id of the $propertyName to retrieve.
 * @param PropelPDO       \$con  Optional connection object
 *
 * @return {$this->getPropertyActiveRecordClassName()}
 */
protected function get{$propertyNameMethod}ObjectById(\$id, PropelPDO \$con = null)
{
  if(is_numeric(\$id))
  {
    \${$propertiesName} = \$this->{$this->getPropertyObjectsGetter()}(null, \$con);
    foreach(\${$propertiesName} as \${$propertyName})
    {
      if(\${$propertyName}->getId() == \$id)
      {
        return \${$propertyName};
      }
    }
  }
  else
  {
    \${$propertyName}Name = substr(\$id, 0, strrpos(\$id, '_'));
    \${$propertiesName} = \$this->get{$propertiesNameMethod}ObjectsByName(\${$propertyName}Name, \$con);
    return \${$propertiesName}[\$id];
  }
}

/**
 * Check wether $propertyName with \$id is
 *
 * @param PropelPDO \$con  Optional connection object
 */
protected function is{$propertyNameMethod}WithIdA(\$id, \${$propertyName}Name, PropelPDO \$con = null)
{
  \${$propertyName} = \$this->get{$propertyNameMethod}ObjectById(\$id, \$con);
  return \${$propertyName} && \${$propertyName}->{$this->getPropertyColumnGetter('property_name_column')}() == {$this->peerClassname}::normalize{$propertyNameMethod}Name(\${$propertyName}Name);
}

/**
 * wrapped function on update{{$propertyNameMethod}} callback
 *
 * @param string          \$name  the $propertyName to update's type
 * @param mixed           \$value the new value
 * @param integer|string  \$id    the id of the $propertyName to update
 * @param PropelPDO       \$con   Optional connection object
 *
 * @return Boolean|{$this->getPropertyActiveRecordClassName()}
 */
protected function set{$propertyNameMethod}ByNameAndId(\$name, \$value, \$id, PropelPDO \$con = null)
{
  if(\$this->is{$propertyNameMethod}WithIdA(\$id, {$this->peerClassname}::normalize{$propertyNameMethod}Name(\$name), \$con))
  {
    return \$this->set{$propertyNameMethod}ById(\$id, \$value);
  }
  return false;
}

/**
 * get the $propertyName with id \$id.
 * can only be used with an already set $propertyName
 *
 * @param PropelPDO \$con Optional connection object
 */
protected function get{$propertyNameMethod}ById(\$id, \$defaultValue = null, PropelPDO \$con = null)
{
  \${$propertyName} = \$this->get{$propertyNameMethod}ObjectById(\$id, \$con);
  if(\${$propertyName} instanceof {$this->getPropertyActiveRecordClassName()})
  {
    return \${$propertyName}->{$this->getPropertyColumnGetter('property_value_column')}();
  }
  else
  {
    return \$defaultValue;
  }
}

/**
 * wrapped function on delete$propertyNameMethod callback
 *
 * @param PropelPDO \$con Optional connection object
 */
protected function delete{$propertyNameMethod}ByNameAndId(\$name, \$id, PropelPDO \$con = null)
{
  if(\$this->is{$propertyNameMethod}WithIdA(\$id, {$this->peerClassname}::normalize{$propertyNameMethod}Name(\$name), \$con))
  {
    return \$this->delete{$propertyNameMethod}ById(\$id, \$con);
  }
  return false;
}

/**
 * delete a multiple occurence $propertyName
 *
 * @param PropelPDO \$con  Optional connection object
 */
protected function delete{$propertyNameMethod}ById(\$id, PropelPDO \$con = null)
{
  \${$propertyName} = \$this->get{$propertyNameMethod}ObjectById(\$id, \$con);
  if(\${$propertyName} instanceof {$this->getPropertyActiveRecordClassName()})
  {
    if(!\${$propertyName}->isNew())
    {
      \${$propertyName}->delete(\$con);
    }
    \$this->{$this->getPropertyObjectsColumn()}->remove(\$this->{$this->getPropertyObjectsColumn()}->search(\${$propertyName}));
    return \${$propertyName};
  }
  else
  {
    return false;
  }
}

/**
 * delete all {$propertiesName} with \$name
 *
 * @param PropelPDO \$con Optional connection object
 */
public function delete{$propertiesName}ByName(\$name, PropelPDO \$con = null)
{
  \${$propertiesName} = \$this->get{$propertiesNameMethod}ObjectsByName(\$name, \$con);
  foreach(\${$propertiesName} as \${$propertyName})
  {
    if(\${$propertyName} instanceof {$this->getPropertyActiveRecordClassName()})
    {
      \${$propertyName}->delete(\$con);
      \$this->{$this->getPropertyObjectsColumn()}->remove(\$this->{$this->getPropertyObjectsColumn()}->search(\${$propertyName}));
    }
  }
  return \${$propertiesName};
}
EOF;
    }

    protected function getMultiplePropertyRegistrationMethods()
    {
        $propertyName = $this->getParameter('property_name');
        $propertyNameMethod = ucfirst($propertyName);
        $propertiesName = $this->getPluralForm($propertyName);
        $propertiesNameMethod = ucfirst($propertiesName);

        return <<<EOF
/**
 * returns the list of registered multiple {$propertiesName}
 *
 * @return array
 */
public function getRegisteredMultiple{$propertiesNameMethod}()
{
  return array_keys(\$this->multipleExtra{$propertiesNameMethod});
}

/**
 * Register a new multiple occurence $propertyName \${$propertyName}Name for the object.
 * The {$propertiesName} will be accessible through {$this->getPropertyColumnGetter('property_name_column')}s method.
 *
 * @param String  \${$propertyName}Name   the $propertyName name.
 * @param Mixed   \$defaultValue   default $propertyName value.
 * @return {$this->getActiveRecordClassName()}
 */
public function registerMultiple{$propertyNameMethod}(\${$propertyName}Name, \$defaultValue = null)
{
  \${$propertyName}Name = {$this->peerClassname}::normalize{$propertyNameMethod}Name(\${$propertyName}Name);
  /* comment this line to remove default value update ability
  if(!array_key_exists(\${$propertyName}Name, \$this->multipleExtra{$propertiesNameMethod}))
  {
    \$this->multipleExtra{$propertiesNameMethod}[\${$propertyName}Name] = \$defaultValue;
  }
  /*/
  \$this->multipleExtra{$propertiesNameMethod}[\${$propertyName}Name] = \$defaultValue;
  //*/
  return \$this;
}

/**
 * adds a multiple instance $propertyName to event
 *
 * @param String  \${$propertyName}Name   the name of the $propertyName to add.
 * @param Mixed   \$value          the value for new $propertyName.
 */
public function add{$propertyNameMethod}(\${$propertyName}Name, \$value)
{
  \${$propertyName} = new {$this->getPropertyActiveRecordClassName()}();
  \${$propertyName}->{$this->getPropertyColumnSetter('property_name_column')}({$this->peerClassname}::normalize{$propertyNameMethod}Name(\${$propertyName}Name));
  \${$propertyName}->{$this->getPropertyColumnSetter('property_value_column')}({$this->peerClassname}::normalize{$propertyNameMethod}Value(\$value));
  \$this->{$this->getPropertyObjectsSetter()}(\${$propertyName});
  return \$this;
}

/**
 * returns an array of all matching values for given $propertyName
 * the array keys are the values ID
 * @todo enhance the case an id is given
 * @todo check the case there is an id but does not exists
 *
 * @param string    \${$propertyName}Name    the name of {$propertiesName} to retrieve
 * @param mixed     \$default         The default value to use
 * @param Integer   \$id              The unique id of the $propertyName to retrieve
 * @param PropelPDO \$con             Optional connection object
 *
 * @return array  the list of matching $propertiesName (prop_id => value).
 */
public function get{$propertiesNameMethod}ByName(\${$propertyName}Name, \$default = array(), \$id = null, PropelPDO \$con = null)
{
  \$ret = array();
  \${$propertiesName} = \$this->get{$propertiesNameMethod}ObjectsByName(\${$propertyName}Name, \$con);
  foreach(\${$propertiesName} as \$key => \${$propertyName})
  {
    \$ret[\$key] = \${$propertyName}->{$this->getPropertyColumnGetter('property_value_column')}();
  }
  // is there a $propertyName id ?
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

    protected function getGetExtraPropertiesMethods()
    {
        $propertyName = $this->getParameter('property_name');
        $propertyNameMethod = ucfirst($propertyName);
        $propertiesName = $this->getPluralForm($propertyName);
        $propertiesNameMethod = ucfirst($propertiesName);

        return <<<EOF
/**
 * returns an associative array with the {$propertiesName} and associated values.
 *
 * @deprecated Prefer the get{$propertiesNameMethod}() method
 *
 * @return array
 */
public function getExtra{$propertiesNameMethod}(\$con = null)
{
  return \$this->get{$propertiesNameMethod}(\$con);
}

/**
 * returns an associative array with the {$propertiesName} and associated values.
 *
 * @return array
 */
public function get{$propertiesNameMethod}(\$con = null)
{
  \$ret = array();

  // init with default single and multiple {$propertiesName}
  \$ret = array_merge(\$ret, \$this->extra{$propertiesNameMethod});
  foreach (\$this->multipleExtra{$propertiesNameMethod} as \${$propertyName}Name => \$default) {
    \$ret[\${$propertyName}Name] = array();
  }

  foreach (\$this->{$this->getPropertyObjectsGetter()}(null, \$con) as \${$propertyName}) {
    \$pname = \${$propertyName}->{$this->getPropertyColumnGetter('property_name_column')}();
    \$pvalue = \${$propertyName}->{$this->getPropertyColumnGetter('property_value_column')}();

    if (array_key_exists(\$pname, \$this->extra{$propertiesNameMethod})) {
      // single $propertyName
      \$ret[\$pname] = \$pvalue;
    }
    elseif (array_key_exists(\$pname, \$ret) && is_array(\$ret[\$pname])){
      \$ret[\$pname][] = \$pvalue;
    }
    elseif (array_key_exists(\$pname, \$ret)){
      \$ret[\$pname] = array(\$ret[\$pname], \$pvalue);
    }
    else {
      \$ret[\$pname] = \$pvalue;
    }
  }

  // set multiple {$propertiesName} default
  foreach (\$this->multipleExtra{$propertiesNameMethod} as \${$propertyName}Name => \$default) {
    if (!is_null(\$default) && !count(\$ret[\${$propertyName}Name])) {
      \$ret[\${$propertyName}Name][] = \$default;
    }
  }

  return \$ret;
}
EOF;
    }

    public function objectCall()
    {
        $propertyName = $this->getParameter('property_name');
        $propertyNameMethod = ucfirst($propertyName);
        $propertiesName = $this->getPluralForm($propertyName);
        $propertiesNameMethod = ucfirst($propertiesName);

        if(floatval(substr(Propel::VERSION, 0, 3)) >= 1.5) {
            $methodVar = '$name';
            $paramVar = '$params';
        } else {
            $methodVar = '$method';
            $paramVar = '$arguments';
        }

        $script = <<<EOF
// calls the registered {$propertiesName} dedicated functions
if(in_array(\$methodName = substr({$methodVar}, 0,3), array('add', 'set', 'has', 'get')))
{
  \${$propertyName}Name = {$this->peerClassname}::normalize{$propertyNameMethod}Name(\$this->extra{$propertyNameMethod}NameFromMethod(substr({$methodVar}, 3)));
}
else if(in_array(\$methodName = substr({$methodVar}, 0,5), array('count', 'clear')))
{
  \${$propertyName}Name = {$this->peerClassname}::normalize{$propertyNameMethod}Name(\$this->extra{$propertyNameMethod}NameFromMethod(substr({$methodVar}, 5)));
}
else if(in_array(\$methodName = substr({$methodVar}, 0,6), array('delete', 'update')))
{
  \${$propertyName}Name = {$this->peerClassname}::normalize{$propertyNameMethod}Name(\$this->extra{$propertyNameMethod}NameFromMethod(substr({$methodVar}, 6)));
}
if(isset(\${$propertyName}Name))
{
  if(array_key_exists(\${$propertyName}Name, \$this->extra{$propertiesNameMethod}))
  {
    switch(\$methodName)
    {
      case 'add':
      case 'set':
        \$callable = array(\$this, 'set{$propertyNameMethod}');
        break;
      case 'get':
        \$callable = array(\$this, 'get{$propertyNameMethod}');
        break;
      case 'has':
        \$callable = array(\$this, 'has{$propertyNameMethod}');
        break;
      case 'count':
        \$callable = array(\$this, 'count{$propertiesNameMethod}ByName');
        break;
      case 'clear':
      case 'delete':
        \$callable = array(\$this, 'delete{$propertiesNameMethod}ByName');
        break;
      case 'update':
        \$callable = array(\$this, 'set{$propertyNameMethod}ByName');
        break;
    }
  }
  else if(array_key_exists(\${$propertyName}Name, \$this->multipleExtra{$propertiesNameMethod}) ||
          ('S' == substr(\${$propertyName}Name, -1) && array_key_exists(\${$propertyName}Name = substr(\${$propertyName}Name, 0, -1), \$this->multipleExtra{$propertiesNameMethod})))
  {
    switch(\$methodName)
    {
      case 'add':
      case 'set':
        \$callable = array(\$this, 'add{$propertyNameMethod}');
        break;
      case 'get':
        \$callable = array(\$this, 'get{$propertiesNameMethod}ByName');
        break;
      case 'has':
        \$callable = array(\$this, 'has{$propertyNameMethod}');
        break;
      case 'count':
        \$callable = array(\$this, 'count{$propertiesNameMethod}ByName');
        break;
      case 'clear':
        \$callable = array(\$this, 'delete{$propertiesNameMethod}ByName');
        break;
      case 'delete':
        \$callable = array(\$this, 'delete{$propertyNameMethod}ByNameAndId');
        break;
      case 'update':
        \$callable = array(\$this, 'set{$propertyNameMethod}ByNameAndId');
        break;
    }
  }

EOF;
    if ('true' === $this->getParameter('throw_error')) {
        $script .= <<<EOF
    //* no error throw to make sure other behaviors can be called.
    else
    {
      throw new RuntimeException(sprintf('Unknown $propertyName %s.<br />possible single {$propertiesName}: %s<br />possible multiple {$propertiesName}', \${$propertyName}Name, join(',', array_keys(\$this->extra{$propertiesNameMethod})), join(',', array_keys(\$this->multipleExtra{$propertiesNameMethod}))));
    }
    //*/

EOF;
    }
    $script .= <<<EOF
  if(isset(\$callable))
  {
    array_unshift({$paramVar}, \${$propertyName}Name);
    return call_user_func_array(\$callable, {$paramVar});
  }

}

EOF;
        return $script;
    }
} // END OF ExtraPropertiesBehaviorObjectBuilderModifier
