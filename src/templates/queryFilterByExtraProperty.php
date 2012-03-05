/**
 * Filter based on an extra property
 *
 * If the property is not set for a particular object it will be present in the results
 *
 * @var string $property_name The name of the property to filter on
 * @var mixed $property_value The value of the property to filter on
 *
 * @return <?php echo $queryClassName . PHP_EOL ?>
 */
public function filterByExtraProperty($property_name, $property_value)
{
  return $this
    -><?php echo $joinExtraPropertyTableMethod ?>()
    ->addJoinCondition('<?php echo $propertyRelationName ?>', '<?php echo $propertyRelationName ?>.<?php echo $propertyPropertyNameColName ?> = ?', $property_name)
    ->where('<?php echo $propertyRelationName ?>.<?php echo $propertyPropertyValueColName ?> = ?', $property_value);
}

