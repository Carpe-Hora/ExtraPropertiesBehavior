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
{
  $propertyName = strtoupper($propertyName);

  return $this
    -><?php echo $joinExtraPropertyTableMethod ?>()
    ->addJoinCondition('<?php echo $propertyRelationName ?>', '<?php echo $propertyRelationName ?>.<?php echo $propertyPropertyNameColName ?> = ?', $propertyName)
    ->where('<?php echo $propertyRelationName ?>.<?php echo $propertyPropertyValueColName ?> = ?', $propertyValue);
}

