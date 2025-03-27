#!/usr/bin/env php
<?php

$projectRoot = getcwd();
$bundlesFile = $projectRoot . '/config/bundles.php';
$bundleClass = 'K3Progetti\JwtBundle\JwtBundle::class';
$bundleLine = "    $bundleClass => ['all' => true],";

$configTarget = $projectRoot . '/config/packages/jwt.yaml';
$configSource = __DIR__ . '/../resources/config/packages/jwt.yaml.dist';

$routesFile = $projectRoot . '/config/routes.yaml';
$routesBlock = <<<YAML

jwt_bundle_routes:
  resource: '@JwtBundle/Controller/'
  type: attribute
YAML;

function green($text): string
{ return "\033[32m$text\033[0m"; }
function yellow($text): string
{ return "\033[33m$text\033[0m"; }
function red($text): string
{ return "\033[31m$text\033[0m"; }

echo yellow("🔍 File bundles: $bundlesFile\n");

if (!file_exists($bundlesFile)) {
    echo red("❌ File config/bundles.php non trovato.\n");
    exit(1);
}

$contents = file_get_contents($bundlesFile);
$argv = $_SERVER['argv'];
$remove = in_array('--remove', $argv, true);

if ($remove) {
    // Rimozione bundle
    if (strpos($contents, $bundleLine) !== false) {
        $contents = str_replace($bundleLine . "\n", '', $contents);
        $contents = str_replace($bundleLine, '', $contents); // fallback
        file_put_contents($bundlesFile, $contents);
        echo green("🗑️  JwtBundle rimosso da config/bundles.php\n");
    } else {
        echo yellow("ℹ️  JwtBundle non presente in config/bundles.php\n");
    }

    // Rimozione jwt.yaml
    if (file_exists($configTarget)) {
        unlink($configTarget);
        echo green("🗑️  File jwt.yaml rimosso da config/packages.\n");
    }

    // Rimozione blocco routes
    if (file_exists($routesFile)) {
        $routesContent = file_get_contents($routesFile);
        if (strpos($routesContent, $routesBlock) !== false) {
            $routesContent = str_replace($routesBlock, '', $routesContent);
            file_put_contents($routesFile, trim($routesContent) . "\n");
            echo green("🗑️  Blocco routes JwtBundle rimosso da config/routes.yaml\n");
        } else {
            echo yellow("ℹ️  Il blocco routes JwtBundle non era presente.\n");
        }
    }
} else {
    // Aggiungo bundle
    if (strpos($contents, $bundleClass) === false) {
        $pattern = '/(return\s+\[\n)(.*?)(\n\];)/s';
        if (preg_match($pattern, $contents, $matches)) {
            $before = $matches[1];
            $middle = rtrim($matches[2]);
            $after = $matches[3];

            $newMiddle = $middle . "\n" . $bundleLine;
            $newContents = $before . $newMiddle . $after;
            file_put_contents($bundlesFile, $newContents);
            echo green("✅ JwtBundle aggiunto in fondo a config/bundles.php\n");
        } else {
            echo red("❌ Errore durante l'inserimento in config/bundles.php\n");
        }
    } else {
        echo yellow("ℹ️  JwtBundle è già presente in config/bundles.php\n");
    }

    // Copia jwt.yaml se non esiste
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

    // Aggiunta blocco routes
    if (file_exists($routesFile)) {
        $routesContent = file_get_contents($routesFile);
        if (strpos($routesContent, $routesBlock) === false) {
            file_put_contents($routesFile, trim($routesContent) . "\n" . $routesBlock . "\n");
            echo green("✅ Blocco routes JwtBundle aggiunto in config/routes.yaml\n");
        } else {
            echo yellow("ℹ️  Il blocco routes JwtBundle è già presente in config/routes.yaml\n");
        }
    } else {
        echo red("❌ File config/routes.yaml non trovato.\n");
    }
}
