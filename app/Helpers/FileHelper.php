<?php

namespace App\Helpers;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class FileHelper
{
    public static function zip(string $zipDirPath, string $saveZipFilePath): bool
    {
        if (!extension_loaded('zip') || !file_exists($zipDirPath)) {
            return false;
        }

        if (file_exists($saveZipFilePath)) {
            unlink($saveZipFilePath);
        }

        $zip = new ZipArchive();
        if (!$zip->open($saveZipFilePath, ZIPARCHIVE::CREATE)) {
            return false;
        }

        $zipDirPath = str_replace('\\', '/', realpath($zipDirPath));

        if (is_dir($zipDirPath) === true) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($zipDirPath), RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $file) {
                $file = str_replace('\\', '/', $file);

                // Ignore "." and ".." folders
                if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..'))) {
                    continue;
                }

                $file = realpath($file);

                if (is_dir($file) === true) {
                    $zip->addEmptyDir(str_replace($zipDirPath . '/', '', $file . '/'));
                } else if (is_file($file) === true) {
                    $zip->addFromString(str_replace($zipDirPath . '/', '', $file), file_get_contents($file));
                }
            }
        } else if (is_file($zipDirPath) === true) {
            $zip->addFromString(basename($zipDirPath), file_get_contents($zipDirPath));
        }

        return $zip->close();
    }
}