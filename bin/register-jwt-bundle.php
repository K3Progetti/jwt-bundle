#!/usr/bin/env php
<?php

$bundlesFile = __DIR__ . '/../config/bundles.php';
$bundleClass = K3Progetti\JwtBundle\JwtBundle::class;
$bundleLine = "    $bundleClass => ['all' => true],";

$configTarget = __DIR__ . '/../config/packages/jwt.yaml';
$configSource = __DIR__ . '/../resources/config/jwt.yaml.dist';

function green($text) {
    return "\033[32m$text\033[0m";
}

function yellow($text) {
    return "\033[33m$text\033[0m";
}

function red($text) {
    return "\033[31m$text\033[0m";
}

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
        $pattern = '/return\s+\[(.*?)(\];)/s';
        $replacement = "return [\n    $bundleClass => ['all' => true],\n$1$2";
        $newContents = preg_replace($pattern, $replacement, $contents, 1);
        file_put_contents($bundlesFile, $newContents);
        echo green("✅ JwtBundle registrato in config/bundles.php\n");
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
