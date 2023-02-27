<?php

use Own3d\DesktopOverlay\GenerateVerifiedGames;

require_once __DIR__ . '/../vendor/autoload.php';

$generateVerifiedGames = new GenerateVerifiedGames();

$generateVerifiedGames->generate();