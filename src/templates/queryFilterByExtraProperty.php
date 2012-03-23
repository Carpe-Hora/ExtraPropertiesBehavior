/**
 * Filter based on an extra property
 *
 * If the property is not set for a particular object it will be present in the results
 *
 * @var string $propertyName The name of the property to filter on
 * @var mixed $propertyValue The value of the property to filter on
 *
 * @return <?php echo $queryClassName . PHP_EOL ?>
 */
public function filterByExtraProperty($propertyName, $propertyValue)
{<?php echo "\n"; if ($shouldNormalize): ?>
  $propertyName = <?php echo $peerClassName ?>::normalizeExtraPropertyName($propertyName);
  $propertyValue = <?php echo $peerClassName ?>::normalizeExtraPropertyValue($propertyValue);
<?php echo "\n"; endif;  ?>
  return $this
    -><?php echo $joinExtraPropertyTableMethod ?>($joinName = $propertyName . '_' . uniqid())
    ->addJoinCondition($joinName, "{$joinName}.<?php echo $propertyPropertyNameColName ?> = ?", $propertyName)
    ->where("{$joinName}.<?php echo $propertyPropertyValueColName ?> = ?", $propertyValue);
}

