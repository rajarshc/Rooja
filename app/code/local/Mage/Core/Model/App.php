<?php
/**
 * This file replacement (for /app/code/core/Mage/Core/Model/App.php) is necessary for an early
 * launch of the Extended Rewriter (ER) subsystem developed by AITOC Inc.. The Magento system
 * allows only one rewrite to be used by extensions to extend some of the core Magento functionality.
 * Often different extensions need to use rewrites of the same class in order to work properly.
 * The `one rewrite` restriction leads to the situation when manual changes in extensions' class
 * files are required to make extensions work together. Thereby extensions' install and update
 * processes become much more complex.
 *
 * The ER subsystem solves conflicts between AITOCs' and 3rd-party extensions' rewrites by creating
 * generic chains of rewrites without making direct changes to the extensions' original class files.
 * It makes the process of modules installation easier and prevents many issues which could be caused
 * by conflicts of rewrites.
 *
 * Please note that this file replacement does NOT affect normal work of the Mageno system - it only
 * launches an additional subsystem during system initialization. All the extensions developed by AITOC Inc.
 * could work without this subsystem but it will be much harder to install them as it may be necessary
 * to manually solve rewrites' conflicts with 3rd-party extension. Please note that solving conflicts
 * with 3rd-party extensions is out of AITOC's free support policy. The ER subsystem could be disabled
 * by removing this file but it is strongly not recommended.
 */
Mage::setRoot();
require_once Mage::getRoot().'/code/core/Mage/Core/Model/App.php';

$initProcessor = new Aitoc_Aitsys_Model_Init_Processor();

if ($initProcessor->isInstallerEnabled())
{
    Aitoc_Aitsys_Model_Rewriter_Autoload::register(true);
    $initProcessor->realize();
}

Mage::register('aitsys_autoload_initialized', true);

Mage::register('aitsys_autoload_initialized_base',true);
