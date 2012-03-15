<?php

$propel_dir = $_SERVER['PROPEL_DIR'];

$behavior_dir = file_exists(__DIR__ . '/../src/')
                    ? __DIR__ . '/../src'
                    : $propel_dir . '/generator/lib/behavior/extra_properties';

require_once $propel_dir . '/runtime/lib/Propel.php';
require_once $propel_dir . '/generator/lib/util/PropelQuickBuilder.php';
require_once $propel_dir . '/generator/lib/util/PropelPHPParser.php';
require_once $propel_dir . '/generator/lib/behavior/versionable/VersionableBehavior.php';
require_once $behavior_dir . '/ExtraPropertiesBehavior.php';

