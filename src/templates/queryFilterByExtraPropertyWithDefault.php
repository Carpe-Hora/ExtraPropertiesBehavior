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
{
  $propertyName = strtoupper($propertyName);

  return $this
    -><?php echo $joinExtraPropertyTableMethod ?>()
    ->addJoinCondition('<?php echo $propertyRelationName ?>', '<?php echo $propertyRelationName ?>.<?php echo $propertyPropertyNameColName ?> = ?', $propertyName)
    ->where("COALESCE(<?php echo $propertyRelationName ?>.<?php echo $propertyPropertyValueColName ?>, '{$default}') = ?", $propertyValue);
}

