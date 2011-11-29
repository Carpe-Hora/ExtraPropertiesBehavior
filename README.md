ExtraPropertiesBehavior
=======================

The *ExtraPropertiesBehavior* helps key/value extension for an object.

Basic example
-------------

Given a product, *ExtraPropertiesBehavior* provides extra property fields.

``` xml
<table name="product">
  <column name="id" type="INTEGER" primaryKey="true" autoincrement="true" />
  <column name="name" type="VARCHAR" size="255" />
  <!-- .... -->
  <behavior name="extra_properties" />
</table>
```

``` php
$user = new Product();
$user->setName('foo');
$user->setProperty('my_preference', 'my_preference_value');
$user->save();

$user->getProperty('my_preference'); // will result in 'my_preference_value'
```

Installation
------------

First clone the behavior in your vendor directory

```
git clone git://github.com/Carpe-Hora/ExtraPropertiesBehavior.git
```

then register behavior in your ```propel.ini``` or ```buid.properties``` configuration files :

``` ini
propel.behavior.extra_properties.class = path.to.ExtraPropertiesBehavior
```

Usage
-----

Just declare the behavior in your table definition :

``` xml
<!-- in schema.xml -->
<behavior name="extra_properties" />
```

At this point behavior will add an extra table to store properties and a set of methods in the active 
record object :

### Common methods

 * hasProperty('property_name')
 * countPropertiesByName('property_name')
 * initializeProperties()
 * deletePropertiesByName('property_name')

### Single instance properties

 * setProperty('property_name', 'value')
 * getProperty('property_name', 'default value')

### multiple instance properties

 * addProperty('property_name', 'value')
 * getPropertiesByName('property_name')

This is nice, but usualy, what a developper want is direct access through getters and setters.
To do so, declare the extra property list using following :

 * registerProperty
 * registerMultipleProperty

Configuration
-------------

First declare the behavior in the ```schema.xml``` :

``` xml
<database name="user">
  <table name="user_preference">
    <column name="id" type="INTEGER" primaryKey="true" autoincrement="true" />
    <column name="key" type="VARCHAR" size="50" />
    <column name="value" type="LONGVARCHAR" />
    <column name="user_id" type="integer" required="true" />
    <foreign-key foreignTable="user" onDelete="cascade">
      <reference local="user_id" foreign="id" />
    </foreign-key>
  </table>
  <table name="user">
    <column name="id" type="INTEGER" primaryKey="true" autoincrement="true" />
    <column name="name" type="VARCHAR" size="255" />
    <behavior name="extra_properties" >
      <!-- related table -->
      <parameter name="properties_table" value="user_preference" />
      <!-- property label column -->
      <parameter name="property_name_column" value="key" />
      <!-- property value column -->
      <parameter name="property_value_column" value="value" />
    </behavior>
  </table>
</database>
```

To enable humanized getters, you can declare properties during your initilization boot or anywhere else...

``` php
class User extends BaseUser
{
  protected function initialize()
  {
    $this->registerExtraProperty('MY_MODULE_PREFERENCE', 'default_value');
  }
}
```

Then, anywhere just access preferences as follow :

``` php
$user->getMyModulePreference();             // or call $user->getProperty('my_module_preference');
$user->setMyModulePreference('preference'); // or call $user->setProperty('my_module_preference', 'preference');

$user->registerExtraProperty('MY_OTHER_PREFERENCE', 'default_value');
$user->getMyOtherPreference();             // or call $user->getProperty('my_other_preference');
$user->setMyOtherPreference('preference'); // or call $user->setProperty('my_other_preference', 'preference');
```




Todo
----

 * implement default properties (generate methods and register in initialize)
 * parameter to chose setters and getters name.
 * add a calback to convert property value
