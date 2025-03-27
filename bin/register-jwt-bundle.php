#!/usr/bin/env php
<?php

$bundlesFile = __DIR__ . '/../config/bundles.php';
$bundleClass = "K3Progetti\\JwtBundle\\JwtBundle::class";
$bundleLine = "    $bundleClass => ['all' => true],";

$configTarget = __DIR__ . '/../config/packages/jwt.yaml';
$configSource = __DIR__ . '/../resources/config/jwt.yaml.dist';

if (!file_exists($bundlesFile)) {
    echo "❌ File config/bundles.php non trovato.\n";
    exit(1);
}

$contents = file_get_contents($bundlesFile);
$argv = $_SERVER['argv'];
$remove = in_array('--remove', $argv, true);

if ($remove) {
    if (strpos($contents, $bundleLine) !== false) {
        $contents = str_replace($bundleLine . "\n", '', $contents);
        $contents = str_replace($bundleLine, '', $contents); // fallback
        file_put_contents($bundlesFile, $contents);
        echo "🗑️  JwtBundle rimosso da config/bundles.php\n";
    } else {
        echo "ℹ️  JwtBundle non presente in config/bundles.php\n";
    }

    // Rimuovo anche il file jwt.yaml se esiste
    if (file_exists($configTarget)) {
        unlink($configTarget);
        echo "🗑️  File jwt.yaml rimosso da config/packages.\n";
    }
} else {
    if (strpos($contents, $bundleClass) === false) {
        $pattern = '/return\s+\[(.*?)(\];)/s';
        $replacement = "return [\n    $bundleClass => ['all' => true],\n$1$2";
        $newContents = preg_replace($pattern, $replacement, $contents, 1);
        file_put_contents($bundlesFile, $newContents);
        echo "✅ JwtBundle registrato in config/bundles.php\n";
    } else {
        echo "ℹ️  JwtBundle è già presente in config/bundles.php\n";
    }

    if (!file_exists($configTarget)) {
        if (file_exists($configSource)) {
            copy($configSource, $configTarget);
            echo "✅ File jwt.yaml copiato in config/packages.\n";
        } else {
            echo "⚠️  File sorgente jwt.yaml.dist non trovato.\n";
        }
    } else {
        echo "ℹ️  File jwt.yaml già presente in config/packages.\n";
    }
}
