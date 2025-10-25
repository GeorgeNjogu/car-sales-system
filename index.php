<?php
require_once 'Classes.php';
require_once 'config.php'; // include configuration file

$layoutsInstance = new Layouts();
$formsInstance = new Forms();

// Display layout
$layoutsInstance->header($conf);
$layoutsInstance->welcome($conf);
$formsInstance->carPurchaseForm(); // changed from appointmentForm() to carPurchaseForm()
$layoutsInstance->footer($conf);
?>
