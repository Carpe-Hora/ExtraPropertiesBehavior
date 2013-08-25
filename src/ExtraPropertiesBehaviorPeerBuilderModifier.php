<?php
/**
 * This file declare the ExtraPropertiesBehaviorPeerBuilderModifier class.
 *
 * @copyright (c) Carpe Hora SARL 2012
 * @since 2012-03-16
 * @license     MIT License
 */

/**
 * @author Julien Muetton <julien_muetton@carpe-hora.com>
 * @package propel.generator.behavior.extra_properties
 */
class ExtraPropertiesBehaviorPeerBuilderModifier
{
    protected $behavior, $table, $pluralizer;

    public function __construct($behavior)
    {
        $this->behavior = $behavior;
        $this->table = $behavior->getTable();
    }
    protected function getParameter($key)
    {
        return $this->behavior->getParameter($key);
    }

    protected function getPluralForm($root)
    {
        if ($this->pluralizer === null) {
            $this->pluralizer = new StandardEnglishPluralizer();
        }

        return $this->pluralizer->getPluralForm($root);
    }

    public function shouldNormalize()
    {
        return 'true' === $this->getParameter('normalize');
    }

    public function staticMethods()
    {
        $propertyName = $this->getParameter('property_name');
        $propertyNameMethod = ucfirst($propertyName);
        $propertiesName = $this->getPluralForm($propertyName);
        $propertiesNameMethod = ucfirst($propertiesName);

        $script = <<<EOF
/**
 * Normalizes {$propertyName} name.
 *
 * @param String \${$propertyName}Name the {$propertyName} name to normalize.
 * @param String the normalized {$propertyName} name
 */
static function normalize{$propertyNameMethod}Name(\${$propertyName}Name)
{

EOF;
        if ($this->shouldNormalize()) {
            $script .= <<<EOF
  return strtoupper(\${$propertyName}Name);
EOF;
        } else {
            $script .= <<<EOF
  return \${$propertyName}Name;
EOF;
        }
        $script .= <<<EOF

}

/**
 * Normalizes {$propertyName} name.
 *
 * @deprecated see normalize{$propertyNameMethod}Name()
 *
 * @param String \${$propertyName}Name the {$propertyName} name to normalize.
 * @param String the normalized {$propertyName} name
 */
static function normalizeExtraPropertyName(\${$propertyName}Name)
{
  return self::normalize{$propertyNameMethod}Name(\${$propertyName}Name);
}

/**
 * Normalizes {$propertyName} value.
 *
 * @param String \${$propertyName}Value the {$propertyName} value to normalize.
 * @param String the normalized {$propertyName} value
 */
static function normalize{$propertyNameMethod}Value(\${$propertyName}Value)
{
  return \${$propertyName}Value;
}

/**
 * Normalizes {$propertyName} value.
 *
 * @deprecated see normalize{$propertyNameMethod}Value()
 *
 * @param String \${$propertyName}Value the {$propertyName} value to normalize.
 * @param String the normalized {$propertyName} value
 */
static function normalizeExtraPropertyValue(\${$propertyName}Value)
{
  return self::normalize{$propertyNameMethod}Value(\${$propertyName}Value);
}
EOF;

        return $script;
    }
} // END OF ExtraPropertiesBehaviorPeerBuilderModifier
