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
    protected $behavior, $table;

    public function __construct($behavior)
    {
        $this->behavior = $behavior;
        $this->table = $behavior->getTable();
    }
    protected function getParameter($key)
    {
        return $this->behavior->getParameter($key);
    }

    public function shouldNormalize()
    {
        return 'true' === $this->getParameter('normalize');
    }

    public function staticMethods()
    {
        $script = <<<EOF
/**
 * Normalizes property name.
 *
 * @param String \$propertyName the property name to normalize.
 * @param String the normalized property name
 */
static function normalizeExtraPropertyName(\$propertyName)
{

EOF;
        if ($this->shouldNormalize()) {
            $script .= <<<EOF
  return strtoupper(\$propertyName);
EOF;
        } else {
            $script .= <<<EOF
  return \$propertyName;
EOF;
        }
        $script .= <<<EOF

}

/**
 * Normalizes property value.
 *
 * @param String \$propertyValue the property value to normalize.
 * @param String the normalized property value
 */
static function normalizeExtraPropertyValue(\$propertyValue)
{
  return \$propertyValue;
}
EOF;

        return $script;
    }
} // END OF ExtraPropertiesBehaviorPeerBuilderModifier
