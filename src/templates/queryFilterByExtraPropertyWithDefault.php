/**
 * Filter based on a <?php echo $propertyName ?>
 *
 * If the <?php echo $propertyName ?> is not set for a particular object it it will be assumed
 * to have a value of $default
 *
 * @var string $<?php echo $propertyName ?>Name The name of the <?php echo $propertyName ?> to filter on
 * @var mixed $<?php echo $propertyName ?>Value The value of the <?php echo $propertyName ?> to filter on
 * @var mixed $default The value that will be assumed as default if an object
 *                     does not have the <?php echo $propertyName ?> set
 *
 * @return <?php echo $queryClassName . PHP_EOL ?>
 */
public function filterBy<?php echo $propertyNameMethod ?>WithDefault($<?php echo $propertyName ?>Name, $<?php echo $propertyName ?>Value, $default)
{<?php echo "\n"; if ($shouldNormalize): ?>
  $<?php echo $propertyName ?>Name = <?php echo $peerClassName ?>::normalize<?php echo $propertyNameMethod ?>Name($<?php echo $propertyName ?>Name);
  $<?php echo $propertyName ?>Value = <?php echo $peerClassName ?>::normalize<?php echo $propertyNameMethod ?>Value($<?php echo $propertyName ?>Value);
  $default = <?php echo $peerClassName ?>::normalize<?php echo $propertyNameMethod ?>Value($default);
<?php echo "\n"; endif; ?>
  return $this
    -><?php echo $joinExtraPropertyTableMethod ?>($joinName = $<?php echo $propertyName ?>Name . '_' . uniqid())
    ->addJoinCondition($joinName, "{$joinName}.<?php echo $propertyPropertyNameColName ?> = ?", $<?php echo $propertyName ?>Name)
    ->where("COALESCE({$joinName}.<?php echo $propertyPropertyValueColName ?>, '{$default}') = ?", $<?php echo $propertyName ?>Value);
}

/**
 * Filter based on a <?php echo $propertyName ?>
 *
 * If the <?php echo $propertyName ?> is not set for a particular object it it will be assumed
 * to have a value of $default
 *
 * @deprecated see filterByExtraPropertyWithDefault()
 *
 * @var string $<?php echo $propertyName ?>Name The name of the <?php echo $propertyName ?> to filter on
 * @var mixed $<?php echo $propertyName ?>Value The value of the <?php echo $propertyName ?> to filter on
 * @var mixed $default The value that will be assumed as default if an object
 *                     does not have the <?php echo $propertyName ?> set
 *
 * @return <?php echo $queryClassName . PHP_EOL ?>
 */
public function filterByExtraPropertyWithDefault($<?php echo $propertyName ?>Name, $<?php echo $propertyName ?>Value, $default)
{
  return $this->filterBy<?php echo $propertyNameMethod ?>WithDefault($<?php echo $propertyName ?>Name, $<?php echo $propertyName ?>Value, $default);
}
