/**
 * Filter based on an extra property
 *
 * If the property is not set for a particular object it it will be assumed
 * to have a value of $default
 *
 * @var string $propertyName The name of the property to filter on
 * @var mixed $propertyValue The value of the property to filter on
 * @var mixed $default The value that will be assumed as default if an object
 *                     does not have the property set
 *
 * @return <?php echo $queryClassName . PHP_EOL ?>
 */
public function filterByExtraPropertyWithDefault($propertyName, $propertyValue, $default)
{<?php echo "\n"; if ($shouldNormalize): ?>
  $propertyName = <?php echo $peerClassName ?>::normalizeExtraPropertyName($propertyName);
  $propertyValue = <?php echo $peerClassName ?>::normalizeExtraPropertyValue($propertyValue);
  $default = <?php echo $peerClassName ?>::normalizeExtraPropertyValue($default);
<?php echo "\n"; endif; ?>
  return $this
    -><?php echo $joinExtraPropertyTableMethod ?>($joinName = $propertyName . '_' . uniqid())
    ->addJoinCondition($joinName, "{$joinName}.<?php echo $propertyPropertyNameColName ?> = ?", $propertyName)
    ->where("COALESCE({$joinName}.<?php echo $propertyPropertyValueColName ?>, '{$default}') = ?", $propertyValue);
}

