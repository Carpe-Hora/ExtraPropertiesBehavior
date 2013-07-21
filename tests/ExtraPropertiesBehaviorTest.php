<?php

/*
 *	$Id: VersionableBehaviorTest.php 1460 2010-01-17 22:36:48Z francois $
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */


/**
 * Test for ExtraPropertiesBehavior
 *
 * @author     Julien Muetton
 * @version    $Revision$
 * @package    generator.behavior.extra_properties
 */
class ExtraPropertiesBehaviorTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
  	if (!class_exists('ExtraPropertiesBehaviorTest1')) {
      $schema = <<<EOF
<database name="extra_properties_behavior_test_1">
  <table name="extra_properties_behavior_test_1">
    <column name="id" type="INTEGER" primaryKey="true" autoincrement="true" />
    <column name="name" type="VARCHAR" size="255" />
    <behavior name="extra_properties" />
  </table>
</database>
EOF;
			PropelQuickBuilder::buildSchema($schema);
    }
  	if (!class_exists('User')) {
      $schema = <<<EOF
<database name="user">
  <table name="user_preference">
    <column name="id" type="INTEGER" primaryKey="true" autoincrement="true" />
    <column name="key" type="VARCHAR" size="50" />
    <column name="value" type="LONGVARCHAR" />
    <column name="user_id" type="integer" required="true" />
    <foreign-key foreignTable="user" onDelete="cascade"
                  refPhpName="Preference">
      <reference local="user_id" foreign="id" />
    </foreign-key>
  </table>
  <table name="user">
    <column name="id" type="INTEGER" primaryKey="true" autoincrement="true" />
    <column name="name" type="VARCHAR" size="255" />
    <behavior name="extra_properties" >
      <parameter name="properties_table" value="user_preference" />
      <parameter name="property_name_column" value="key" />
      <parameter name="property_value_column" value="value" />
    </behavior>
  </table>
</database>
EOF;
			PropelQuickBuilder::buildSchema($schema);
    }
  	if (!class_exists('Product')) {
      $schema = <<<EOF
<database name="store">
  <table name="product">
    <column name="id" type="INTEGER" primaryKey="true" autoincrement="true" />
    <column name="name" type="VARCHAR" size="255" primaryString="true" />
    <behavior name="extra_properties" />
    <behavior name="versionable" />
  </table>
</database>
EOF;
			PropelQuickBuilder::buildSchema($schema);
    }
  }

	public function testPropertyMethodsExists()
	{
		$this->assertTrue(method_exists('ExtraPropertiesBehaviorTest1', 'hasProperty'));
		$this->assertTrue(method_exists('ExtraPropertiesBehaviorTest1', 'countPropertiesByName'));
		$this->assertTrue(method_exists('ExtraPropertiesBehaviorTest1', 'initializeProperties'));
		$this->assertTrue(method_exists('ExtraPropertiesBehaviorTest1', 'getProperty'));
		$this->assertTrue(method_exists('ExtraPropertiesBehaviorTest1', 'setProperty'));
		$this->assertTrue(method_exists('ExtraPropertiesBehaviorTest1', 'addProperty'));
		$this->assertTrue(method_exists('ExtraPropertiesBehaviorTest1', 'getPropertiesByName'));
		$this->assertTrue(method_exists('ExtraPropertiesBehaviorTest1', 'registerProperty'));
		$this->assertTrue(method_exists('ExtraPropertiesBehaviorTest1', 'registerMultipleProperty'));
	}

  public function testInitializeIsCalledOnConstructor()
  {
    eval('class ExtraPropertiesBehaviorTest1Mock extends ExtraPropertiesBehaviorTest1
    {
      protected function initializeProperties()
      {
        $this->registerProperty("INITIALIZE", "initialize");
      }
    }');
    $stub = new ExtraPropertiesBehaviorTest1Mock();

    $this->assertEquals('initialize', $stub->getInitialize());
    $this->assertEquals('initialize', $stub->getInitialize());
    $this->assertEquals('foo', $stub->getInitialize('foo'));
    $this->assertSame($stub, $stub->setInitialize('foo'));
    $this->assertEquals('foo', $stub->getInitialize());
  }

  public function testGetterAndSetterForDeclaredSinglePropertiesWithDefaultValue()
  {
    $obj = new ExtraPropertiesBehaviorTest1();
    $this->assertSame($obj, $obj->registerProperty('MY_FIRST_PROPERTY'));
    $this->assertEquals(null, $obj->getMyFirstProperty());
    $this->assertEquals('foo', $obj->getMyFirstProperty('foo'));
  }

  public function testGetterAndSetterForDeclaredSingleProperties()
  {
    $obj = new ExtraPropertiesBehaviorTest1();
    // no conflict for declaration
    $this->assertSame($obj, $obj->registerProperty('MY_FIRST_PROPERTY', 'foo'));
    $this->assertSame($obj, $obj->registerProperty('MY_SECOND_PROPERTY', 'foo_bar'));
    $this->assertEquals('foo', $obj->getMyFirstProperty());
    $this->assertEquals('foo_bar', $obj->getMySecondProperty());
    // getter overload
    $this->assertEquals('bar', $obj->getMyFirstProperty('bar'));
    $this->assertEquals('bar', $obj->getMySecondProperty('bar'));
    // update default value
    $obj->registerProperty('MY_FIRST_PROPERTY', 'bar');
    $this->assertEquals('bar', $obj->getMyFirstProperty());
    // setter
    $obj->setMyFirstProperty('test');
    $obj->setMySecondProperty('test2');
    $this->assertEquals('test', $obj->getMyFirstProperty());
    $this->assertEquals('test2', $obj->getMySecondProperty());
    // getter overload
    $this->assertEquals('test', $obj->getMyFirstProperty('bar'));
    $this->assertEquals('test2', $obj->getMySecondProperty('bar'));
    // setter update value
    $obj->setMyFirstProperty('baz');
    $this->assertEquals('baz', $obj->getMyFirstProperty());
    $this->assertEquals('test2', $obj->getMySecondProperty());
  }

  public function testDeleteForDeclaredSingleProperty()
  {
    $obj = new ExtraPropertiesBehaviorTest1();
    // no conflict for declaration
    $obj->registerProperty('MY_FIRST_PROPERTY', 'foo');
    $obj->registerProperty('MY_SECOND_PROPERTY', 'foo_bar');
    $obj->setMyFirstProperty('test');
    $obj->setMySecondProperty('test2');
    $obj->deleteMyFirstProperty();
    $this->assertEquals('foo', $obj->getMyFirstProperty());
    $this->assertEquals('test2', $obj->getMySecondProperty());
  }

  public function testGetterAndSetterForUndeclaredProperties()
  {
    $obj = new ExtraPropertiesBehaviorTest1();
    $this->assertEquals(null, $obj->getProperty('foo'));
    $this->assertEquals('bar', $obj->getProperty('foo', 'bar'));
    $this->assertSame($obj, $obj->setProperty('foo', 'baz'));
    $this->assertEquals('baz', $obj->getProperty('foo'));
    $this->assertEquals('baz', $obj->getProperty('foo', 'bar'));
  }

  public function testDeletePropertiesByName()
  {
    $obj = new ExtraPropertiesBehaviorTest1();
    $obj->setProperty('foo', 'baz');
    $obj->setProperty('bar', 'bar');
    $this->assertCount(1, $obj->deletePropertiesByName('foo'));
    $this->assertequals(null, $obj->getProperty('foo'));
    $this->assertequals('bar', $obj->getProperty('bar'));
  }

  public function testGetterAndSetterForDeclaredMultiplePropertiesWithDefaultValue()
  {
    $obj = new ExtraPropertiesBehaviorTest1();
    $this->assertSame($obj, $obj->registerMultipleProperty('MY_FIRST_PROPERTY'));
    $this->assertCount(0, $obj->getMyFirstPropertys());
    $this->assertSame($obj, $obj->addMyFirstPropertys('foo'));
    $properties = $obj->getMyFirstPropertys();
    $this->assertCount(1, $properties);
    $this->assertEquals('foo', $properties['MY_FIRST_PROPERTY_0']);
  }

  public function testGetterAndSetterForDeclaredMultipleProperties()
  {
    $obj = new ExtraPropertiesBehaviorTest1();
    // no conflict for declaration
    $this->assertSame($obj, $obj->registerMultipleProperty('MY_FIRST_PROPERTY', 'foo'));
    $this->assertSame($obj, $obj->registerMultipleProperty('MY_SECOND_PROPERTY', 'foo_bar'));
    $this->assertCount(0, $obj->getMyFirstPropertys());
    $this->assertCount(0, $obj->getMySecondPropertys());
    // setter
    $obj->addMyFirstProperty('first_1');
    $obj->addMyFirstProperty('first_2');
    $obj->addMySecondProperty('second_1');
    $obj->addMySecondProperty('second_2');
    $properties = $obj->getMyFirstPropertys();
    $this->assertCount(2, $properties);
    $this->assertEquals('first_1', $properties['MY_FIRST_PROPERTY_0']);
    $this->assertEquals('first_2', $properties['MY_FIRST_PROPERTY_1']);
    $properties = $obj->getMySecondPropertys();
    $this->assertCount(2, $properties);
    $this->assertEquals('second_1', $properties['MY_SECOND_PROPERTY_0']);
    $this->assertEquals('second_2', $properties['MY_SECOND_PROPERTY_1']);
  }

  public function testDeleteForDeclaredMultipleProperty()
  {
    $obj = new ExtraPropertiesBehaviorTest1();
    // no conflict for declaration
    $obj->registerMultipleProperty('MY_FIRST_PROPERTY', 'foo');
    $obj->addMyFirstProperty('test');
    $obj->addMyFirstProperty('test2');
    $obj->deleteMyFirstProperty('MY_FIRST_PROPERTY_0');
    $properties = $obj->getMyFirstPropertys();
    $this->assertCount(1, $properties);
    $this->assertEquals('test2', $properties['MY_FIRST_PROPERTY_0']);
    $obj->clearMyFirstPropertys();
    $this->assertCount(0, $obj->getMyFirstPropertys());
  }

  public function testQueryFilter()
  {
    ProductPeer::doDeleteAll();

    $glass = new Product();
    $glass->setName('Glass');
    $glass->setProperty('fragile', true);
    $glass->setProperty('color', 'white');
    $glass->save();

    $bottle = new Product();
    $bottle->setName('Bottle');
    $bottle->setProperty('fragile', true);
    $bottle->setProperty('color', 'green');
    $bottle->save();

    $pan = new Product();
    $pan->setName('Pan');
    $pan->setProperty('fragile', false);
    $pan->setProperty('color', 'black');
    $pan->save();

    $book = new Product();
    $book->setName('Harry Potter');
    $book->setProperty('num_pages', 280);
    $book->setProperty('color', 'green');
    $book->save();

    $count_all = ProductQuery::create()
      ->count();
    $count_fragile = ProductQuery::create()
      ->filterByExtraProperty('fragile', true)
      ->count();
    $count_not_fragile = ProductQuery::create()
      ->filterByExtraProperty('fragile', false)
      ->count();

    $count_fragile_with_default_true = ProductQuery::create()
      ->filterByExtraPropertyWithDefault('fragile', true, true)
      ->count();

    $count_fragile_with_default_false = ProductQuery::create()
      ->filterByExtraPropertyWithDefault('fragile', true, false)
      ->count();

    $this->assertSame(4, $count_all);
    $this->assertSame(2, $count_fragile);
    $this->assertSame(1, $count_not_fragile);
    $this->assertSame(3, $count_fragile_with_default_true);
    $this->assertSame(2, $count_fragile_with_default_false);

    $count_on_multiple_1 = ProductQuery::create()
      ->filterByExtraProperty('fragile', true)
      ->filterByExtraProperty('color', 'green')
      ->count();
    $count_on_multiple_2 = ProductQuery::create()
      ->filterByExtraProperty('fragile', false)
      ->filterByExtraProperty('color', 'green')
      ->count();

    $this->assertSame(1, $count_on_multiple_1);
    $this->assertSame(0, $count_on_multiple_2);
  }

  public function testQueryFilterCalledMultipleTimes()
  {
    $count_on_multiple_1 = ProductQuery::create()
      ->filterByExtraProperty('fragile', true)
      ->filterByExtraProperty('color', 'green')
      ->count();
    $count_on_multiple_2 = ProductQuery::create()
      ->filterByExtraProperty('fragile', false)
      ->filterByExtraProperty('color', 'green')
      ->count();

    $this->assertSame(1, $count_on_multiple_1);
    $this->assertSame(0, $count_on_multiple_2);


    $count_on_multiple_3 = ProductQuery::create()
      ->filterByExtraPropertyWithDefault('fragile', false, true)
      ->filterByExtraProperty('color', 'green')
      ->count();
    $count_on_multiple_4 = ProductQuery::create()
      ->filterByExtraPropertyWithDefault('fragile', false, false)
      ->filterByExtraProperty('color', 'green')
      ->count();

    $this->assertSame(0, $count_on_multiple_3);
    $this->assertSame(1, $count_on_multiple_4);
  }



  public function testUseExistingPropertiesTable()
  {
    $user = new User();
    $user->setName('Test User');
    $this->assertSame($user, $user->setProperty('my_preference', 'value'));
    $this->assertCount(1, $user->getPreferences());

    $this->assertSame($user, $user->addProperty('my_other_preference', 'other'));
    $this->assertCount(2, $user->getPreferences());
    $this->assertEquals('value', $user->getProperty('my_preference'));
    $this->assertCount(1, $user->getPropertiesByName('my_other_preference'));
    $this->assertCount(1, $user->deletePropertiesByName('my_other_preference'));
    $this->assertCount(1, $user->getPreferences());
  }

  public function testUseVersionableBehavior()
  {
    $beagle = new Product();
    $beagle->setName('Beagle');
    $beagle->save();
    $this->assertSame($beagle, $beagle->setProperty('color', 'brown'));
    $this->assertSame($beagle, $beagle->addProperty('length', '27.5m'));
    $this->assertCount(2, $beagle->getProductExtraPropertys());
    $this->assertEquals('brown', $beagle->getProperty('color'));
    $this->assertCount(1, $beagle->getPropertiesByName('length'));
    $this->assertCount(1, $beagle->deletePropertiesByName('length'));
    $beagle->save();
    $beagle = ProductQuery::create()
      ->leftJoinWithProductExtraProperty()
      ->findOneByName('Beagle');

    $this->assertEquals('brown', $beagle->getProperty('color'));
    $this->assertCount(1, $beagle->getProductExtraPropertys());
    $this->assertSame($beagle, $beagle->addProperty('length', '27.5m'));
    $beagle->save();
    $beagle = ProductQuery::create()
      ->leftJoinWithProductExtraProperty()
      ->findOneByName('Beagle');

    $this->assertEquals('brown', $beagle->getProperty('color'));
    $this->assertEquals('27.5m', $beagle->getProperty('length'));
    $this->assertCount(2, $beagle->getProductExtraPropertys());
  }

  public function testWithCustomConnection()
  {
    $obj = new ExtraPropertiesBehaviorTest1();
    $con = Propel::getConnection();

    $this->assertFalse($obj->hasProperty('foo', $con));
    $this->assertEquals(0, $obj->countPropertiesByName('foo', $con));
    $this->assertEquals(array(), $obj->deletePropertiesByName('foo', $con));
    $this->assertNull($obj->getProperty('foo', null, $con));
    $obj->setProperty('bar', 42, $con); // bar does not exist yet
    $this->assertEquals(42, $obj->getProperty('bar', $con));
    $obj->setProperty('bar', 24, $con); // bar should be updated
    $this->assertEquals(24, $obj->getProperty('bar', $con));
    $this->assertEquals(array(), $obj->getPropertiesByName('biz', array(), null, $con));
  }

  public function getExtraPropertiesDataProvider()
  {
    return array(
      'test none defined' => array(
          // property list
          array(),
          //expected
          array(
            'FOO' => 'default_foo',
            'BAR' => 'default_bar',
            'BAZ' => null,
            'PROP1' => array('default_prop1',),
            'PROP2' => array('default_prop2',),
            'PROP3' => array(),
          ),
        ),
      'test property defined' => array(
          //property list
          array(
            array('foo', 'foo'),
            array('prop3', 'prop3'),
            array('prop3', 'other prop 3'),
            array('bar', 'bar'),
            array('bar', 'new_bar'),
            array('baz', 'baz'),
            array('prop2', 'prop2'),
          ),
          array(
            'FOO' => 'foo',
            'BAR' => 'new_bar',
            'BAZ' => 'baz',
            'PROP1' => array('default_prop1',),
            'PROP2' => array('prop2',),
            'PROP3' => array('prop3', 'other prop 3'),
          )
        )
    );
  }

  /**
   * @dataProvider getExtraPropertiesDataProvider
   */
  public function testGetExtraProperties($properties, $expected)
  {
    $obj = new ExtraPropertiesBehaviorTest1();

    $obj->registerProperty('foo', 'default_foo');
    $obj->registerProperty('bar', 'default_bar');
    $obj->registerProperty('baz');

    $obj->registerMultipleProperty('prop1', 'default_prop1');
    $obj->registerMultipleProperty('prop2', 'default_prop2');
    $obj->registerMultipleProperty('prop3');

    foreach ($properties as $prop) {
      switch ($prop[0]) {
        case 'foo':
        case 'bar':
        case 'baz':
          $obj->setProperty($prop[0], $prop[1]);
          break;
        case 'prop1':
        case 'prop2':
        case 'prop3':
        default:
          $obj->addProperty($prop[0], $prop[1]);
          break;
      }
    }

    $this->assertInternalType('array', $obj->getExtraProperties());
    $this->assertEquals($expected, $obj->getExtraProperties());
  }
}
