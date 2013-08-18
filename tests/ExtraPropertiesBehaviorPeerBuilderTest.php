<?php

/*
 * $Id: ExtraPropertiesBehaviorPeerBuilderTest.php 1460 2010-01-17 22:36:48Z francois $
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */


/**
 * Test for ExtraPropertiesBehaviorPeerBuilder
 *
 * @author     Julien Muetton
 * @version    $Revision$
 * @package    generator.behavior.extra_properties
 */
class ExtraPropertiesBehaviorPeerBuilderTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!class_exists('ExtraPropertiesBehaviorTestNormalize')) {
            $schema = <<<EOF
<database name="extra_properties_behavior_test_normalize">
  <table name="extra_properties_behavior_test_normalize">
    <column name="id" type="INTEGER" primaryKey="true" autoincrement="true" />
    <column name="name" type="VARCHAR" size="255" />
    <behavior name="extra_properties">
      <parameter name="normalize" value="true" />
    </behavior>
  </table>
</database>
EOF;
            PropelQuickBuilder::buildSchema($schema);
        }
        if (!class_exists('ExtraPropertiesBehaviorTestDoNotNormalize')) {
            $schema = <<<EOF
<database name="extra_properties_behavior_test_do_not_normalize">
  <table name="extra_properties_behavior_test_do_not_normalize">
    <column name="id" type="INTEGER" primaryKey="true" autoincrement="true" />
    <column name="name" type="VARCHAR" size="255" />
    <behavior name="extra_properties">
      <parameter name="normalize" value="false" />
    </behavior>
  </table>
</database>
EOF;
            PropelQuickBuilder::buildSchema($schema);
        }
    }

    public function allGeneratedClassesDataProvider()
    {
        return array(
            array('ExtraPropertiesBehaviorTestNormalizePeer'),
            array('ExtraPropertiesBehaviorTestDoNotNormalizePeer'),
        );
    }

    /**
     * @dataProvider allGeneratedClassesDataProvider
     */
    public function testMethodExists($class)
    {
        $obj = new $class();
        $this->assertTrue(method_exists($obj, 'normalizeExtraPropertyName'));
        $this->assertTrue(method_exists($obj, 'normalizeExtraPropertyValue'));
    }

    public function normalizeDataProvider()
    {
        return array(
            array('foo', 'FOO'),
            array('#this is the Key', '#THIS IS THE KEY'),
        );
    }

    /**
     * @dataProvider normalizeDataProvider
     */
    public function testNormalizePropertyName($source, $expected)
    {
        $result = ExtraPropertiesBehaviorTestNormalizePeer::normalizeExtraPropertyName($source);
        $this->assertInternalType('string', $result);
        $this->assertEquals($result, $expected);
    }

    /**
     * @dataProvider normalizeDataProvider
     */
    public function testDoNotNormalizePropertyName($source, $expected)
    {
        $result = ExtraPropertiesBehaviorTestDoNotNormalizePeer::normalizeExtraPropertyName($source);
        $this->assertInternalType('string', $result);
        $this->assertEquals($result, $source);
    }

    public function normalizeValueDataProvider()
    {
        return array(
            array('foo', 'foo'),
            array('#this is the Key', '#this is the Key'),
        );
    }

    /**
     * @dataProvider normalizeValueDataProvider
     */
    public function testNormalizePropertyValue($source, $expected)
    {
        $result = ExtraPropertiesBehaviorTestNormalizePeer::normalizeExtraPropertyValue($source);
        $this->assertInternalType('string', $result);
        $this->assertEquals($result, $expected);
    }

    /**
     * @dataProvider normalizeValueDataProvider
     */
    public function testDoNotNormalizePropertyValue($source, $expected)
    {
        $result = ExtraPropertiesBehaviorTestDoNotNormalizePeer::normalizeExtraPropertyValue($source);
        $this->assertInternalType('string', $result);
        $this->assertEquals($result, $expected);
    }
}
