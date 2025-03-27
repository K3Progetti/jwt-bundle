#!/usr/bin/env php
<?php

$projectRoot = getcwd();
$bundlesFile = $projectRoot . '/config/bundles.php';
$bundleClass = 'K3Progetti\JwtBundle\JwtBundle::class';
$bundleLine = "    $bundleClass => ['all' => true],";

$configTarget = $projectRoot . '/config/packages/jwt.yaml';
echo $configSource = 'resources/config/jwt.yaml.dist';

function green($text) { return "\033[32m$text\033[0m"; }
function yellow($text) { return "\033[33m$text\033[0m"; }
function red($text) { return "\033[31m$text\033[0m"; }

echo yellow("🔍 Cercando il file: $bundlesFile\n");

if (!file_exists($bundlesFile)) {
    echo red("❌ File config/bundles.php non trovato.\n");
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
        echo green("🗑️  JwtBundle rimosso da config/bundles.php\n");
    } else {
        echo yellow("ℹ️  JwtBundle non presente in config/bundles.php\n");
    }

    if (file_exists($configTarget)) {
        unlink($configTarget);
        echo green("🗑️  File jwt.yaml rimosso da config/packages.\n");
    }
} else {
    if (strpos($contents, $bundleClass) === false) {
        $pattern = '/(return\s+\[\n)(.*?)(\n\];)/s';

        if (preg_match($pattern, $contents, $matches)) {
            $before = $matches[1]; // "return [\n"
            $middle = rtrim($matches[2]); // bundle già presenti
            $after = $matches[3]; // "\n];"

            $newMiddle = $middle . "\n" . $bundleLine;
            $newContents = str_replace($matches[0], $before . $newMiddle . $after, $contents);

            file_put_contents($bundlesFile, $newContents);
            echo green("✅ JwtBundle aggiunto in fondo a config/bundles.php\n");
        } else {
            echo red("❌ Errore durante l'inserimento in config/bundles.php\n");
        }
    } else {
        echo yellow("ℹ️  JwtBundle è già presente in config/bundles.php\n");
    }

    if (!file_exists($configTarget)) {
        if (file_exists($configSource)) {
            copy($configSource, $configTarget);
            echo green("✅ File jwt.yaml copiato in config/packages.\n");
        } else {
            echo red("⚠️  File sorgente jwt.yaml.dist non trovato.\n");
        }
    } else {
        echo yellow("ℹ️  File jwt.yaml già presente in config/packages.\n");
    }
}
