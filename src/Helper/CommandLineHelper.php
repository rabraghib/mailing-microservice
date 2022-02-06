<?php

namespace App\Helper;

use Codedungeon\PHPCliColors\Color;

class CommandLineHelper
{
    const DASH_SEPARATOR = "------------------------------------------------------------------------------";
    const CLEAR_LINE = "\033[2K\r";

    public static function logColored(string $msg, string $colorAnsi, bool $newLine = true) {
        echo $colorAnsi, $msg, Color::RESET;
        if ($newLine){
            echo PHP_EOL;
        }
    }
    public static function logInfo(string $msg, bool $newLine = true) {
        echo Color::BLUE, "• ", Color::RESET, $msg;
        if ($newLine){
            echo PHP_EOL;
        }
    }
    public static function logSuccess(string $msg, bool $newLine = true) {
        echo Color::GREEN, "✓ ", Color::RESET, $msg;
        if ($newLine){
            echo PHP_EOL;
        }
    }
    public static function logError(string $msg, bool $newLine = true) {
        echo Color::RED, "X ", Color::RESET, $msg;
        if ($newLine){
            echo PHP_EOL;
        }
    }

}