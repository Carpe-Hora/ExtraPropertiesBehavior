/**
 * Filter based on a <?php echo $propertyName ?>
 *
 * If the <?php echo $propertyName ?> is not set for a particular object it will be present in the results
 *
 * @var string $<?php echo $propertyName ?>Name The name of the <?php echo $propertyName ?> to filter on
 * @var mixed $<?php echo $propertyName ?>Value The value of the <?php echo $propertyName ?> to filter on
 *
 * @return <?php echo $queryClassName . PHP_EOL ?>
 */
public function filterBy<?php echo $propertyNameMethod ?>($<?php echo $propertyName ?>Name, $<?php echo $propertyName ?>Value)
{<?php echo "\n"; if ($shouldNormalize): ?>
  $<?php echo $propertyName ?>Name = <?php echo $peerClassName ?>::normalize<?php echo $propertyNameMethod ?>Name($<?php echo $propertyName ?>Name);
  $<?php echo $propertyName ?>Value = <?php echo $peerClassName ?>::normalize<?php echo $propertyNameMethod ?>Value($<?php echo $propertyName ?>Value);
<?php echo "\n"; endif;  ?>
  return $this
    -><?php echo $joinExtraPropertyTableMethod ?>($joinName = $<?php echo $propertyName ?>Name . '_' . uniqid())
    ->addJoinCondition($joinName, "{$joinName}.<?php echo $propertyPropertyNameColName ?> = ?", $<?php echo $propertyName ?>Name)
    ->where("{$joinName}.<?php echo $propertyPropertyValueColName ?> = ?", $<?php echo $propertyName ?>Value);
}

/**
 * Filter based on a <?php echo $propertyName ?>
 *
 * If the <?php echo $propertyName ?> is not set for a particular object it will be present in the results
 *
 * @deprecated see filterBy<?php echo $propertyNameMethod ?>()
 *
 * @var string $<?php echo $propertyName ?>Name The name of the <?php echo $propertyName ?> to filter on
 * @var mixed $<?php echo $propertyName ?>Value The value of the <?php echo $propertyName ?> to filter on
 *
 * @return <?php echo $queryClassName . PHP_EOL ?>
 */
public function filterByExtra<?php echo $propertyNameMethod ?>($<?php echo $propertyName ?>Name, $<?php echo $propertyName ?>Value)
{
  return $this->filterBy<?php echo $propertyNameMethod ?>($<?php echo $propertyName ?>Name, $<?php echo $propertyName ?>Value);
}
