<?php

$config = new \PhpCsFixer\Config();
$config->setRiskyAllowed(true);
$config->setRules([
    '@DoctrineAnnotation'       => true,
    '@PHP71Migration'           => true,
    '@PHP71Migration:risky'     => true,
    '@PHPUnit84Migration:risky' => true,
    '@PhpCsFixer'               => true,
    '@PhpCsFixer:risky'         => true,
]);
$config->getFinder()
    ->in(__DIR__ . '/lib')
    ->in(__DIR__ . '/tests')
;

return $config;
