ExtraPropertiesBehavior
=======================

[![Build Status](https://secure.travis-ci.org/Carpe-Hora/ExtraPropertiesBehavior.png?branch=master)](http://travis-ci.org/Carpe-Hora/ExtraPropertiesBehavior)

The *ExtraPropertiesBehavior* provides a key/value extension for an object.

Basic example
-------------

Given a product model, *ExtraPropertiesBehavior* will add a key/value extension interface.

``` xml
<table name="product">
  <column name="id" type="INTEGER" primaryKey="true" autoincrement="true" />
  <column name="name" type="VARCHAR" size="255" />
  <behavior name="extra_properties" />
</table>
```

``` php
<?php
$tvSet = new Product();
$tvSet->setName('My big TV');
$tvSet->setProperty('size', '12 inches');
$tvSet->setProperty('frequency', '11 Hz');
$tvSet->save();

$tvSet->getProperty('size'); // will result in '12 inches'
$tvSet->getProperty('frequency'); // will result in '11 Hz'
```

Installation
------------

First clone the behavior in your vendor directory:

```
git clone git://github.com/Carpe-Hora/ExtraPropertiesBehavior.git
```

Then register behavior in either your ```propel.ini``` or ```buid.properties```
configuration file:

``` ini
propel.behavior.extra_properties.class = path.to.ExtraPropertiesBehavior
```

Usage
-----

Just add the behavior to your table definition:

``` xml
<!-- in schema.xml -->
<table name="product">
  <!-- ... -->

  <behavior name="extra_properties" />
</table>
```

At this point the behavior will create an extra table to store properties and
will add the following set of methods in the active
record object:

### Common methods

 * `hasProperty('property_name')`
 * `countPropertiesByName('property_name')`
 * `initializeProperties()`
 * `deletePropertiesByName('property_name')`

### Single instance properties

 * `setProperty('property_name', 'value')`
 * `getProperty('property_name', 'default value')`

### multiple instance properties

 * `addProperty('property_name', 'value')`
 * `getPropertiesByName('property_name')`

This is nice, but usualy what a developer wants is direct access through getters and setters.
To do so, declare an extra properties list using the following methods:

 * `registerProperty('property_name, 'default value')`
 * `registerMultipleProperty('property_name')`

### property extraction methods

 * `getExtraProperties()` returns an array of properties

Configuration
-------------

First declare the behavior in your ```schema.xml```:

``` xml
<database name="user">
  <table name="user_preference">
    <column name="id" type="INTEGER" primaryKey="true" autoincrement="true" />
    <column name="key" type="VARCHAR" size="50" />
    <column name="value" type="LONGVARCHAR" />
    <column name="user_id" type="integer" required="true" />
    <foreign-key foreignTable="user" onDelete="cascade" refPhpName="Preference">
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
      <!-- normalize property names and values using peer normalize methods ? -->
      <parameter name="normalize" value="true" />
      <!-- throw an error if shortcut get{PropertyName} cannot resole PropertyName ? -->
      <parameter name="throw_error" value="true" />
    </behavior>
  </table>
</database>
```

To enable humanized getters, declare an `initializeProperties()` method in your model like this:

``` php
<?php
class User extends BaseUser
{
  protected function initializeProperties()
  {
    $this->registerExtraProperty('MY_MODULE_PREFERENCE', 'default_value');
  }
}
```

Then you can use getters and setters directly with your model object:

``` php
<?php
// get/set methods created by initializeProperties()
$user->getMyModulePreference();             // or call $user->getProperty('my_module_preference');
$user->setMyModulePreference('preference'); // or call $user->setProperty('my_module_preference', 'preference');

// extend dynamicly
$user->registerExtraProperty('MY_OTHER_PREFERENCE', 'default_value');
$user->getMyOtherPreference();             // or call $user->getProperty('my_other_preference');
$user->setMyOtherPreference('preference'); // or call $user->setProperty('my_other_preference', 'preference');

// simply deal with multiple occurences
$user->registerExtraProperty('MY_MULTIPLE_PREFERENCE');
$user->addMyMultiplePreference('pref1');
$user->addMyMultiplePreference('pref2');
$user->save();

// extract properties
$user->getExtraProperties();
// will result in
// array(
//   'MY_MODULE_PREFERENCE' => 'preference',
//   'MY_OTHER_PREFERENCE' => 'preference',
//   'MY_MULTIPLE_PREFERENCE' => array('pref1', 'pref2'),
// )

$user->getMyMultiplePreferences();        // will result in array('id_pref1' => 'pref1', 'id_pref2' => 'pref2')
$user->clearMyMultiplePreferences();      // remove all MY_MULTIPLE_PREFERENCE preferences
$user->save();
```

Use with single inheritance
---------------------------

It sometimes is useful to be able to extend the model depending on the inheritance classkey.
*ExtraPropertiesBehavior* can do that for you.

Imagine a CMS with several content types:

``` xml
<database name="content">
  <table name="content">
    <column name="id" type="INTEGER" primaryKey="true" autoincrement="true" />
    <column name="title" type="VARCHAR" size="255" />
    <column name="type" type="VARCHAR" inheritance="single">
    <behavior name="extra_properties" />
  </table>
</database>
```

Given the default content structure, just define your content options by defining your possible key/values in the
`initializeProperties()` method:

``` php
<?php
class Article extends Content
{
  protected function initializeProperties()
  {
    $this->registerExtraProperty('CONTENT');
    $this->registerExtraProperty('AUTHOR');
  }

  public function getOMClass()
  {
    return 'Article';
  }
}
```

and

``` php
<?php
class Video extends Content
{
  protected function initializeProperties()
  {
    $this->registerExtraProperty('URL');
    $this->registerExtraProperty('LENGTH');
  }

  public function getOMClass()
  {
    return 'Video';
  }
}
```

Then, just use extra properties as if it where built in fields:

``` php
<?php
$article = new Article();
$article->setTitle('Propel, greatest php ORM ever');
$article->setContent('Try it you\'ll see');
$article->save();

$video = new Video();
$video->setTitle('Propel + phpsh');
$video->setUrl('http://vimeo.com/15140218');
$video->setLength('2:01');
$video->save();
```

Todo
----

 * implement default properties (generate methods and register in initialize)
 * parameter to chose setters and getters name.
 * add a callback to convert property value
 * add namespace
