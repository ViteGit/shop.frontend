<?php

namespace App\Helpers;

/**
 * Хелпер для сохранения файлов
 */
class FileSaver
{
    /**
     * @param string $path
     * @param string $remoteFile
     * @param string $fileName
     *
     * @return bool
     */
    public static function saveFile(string $path, string $remoteFile, string $fileName): bool
    {
        self::createDir($path);

        return is_int(file_put_contents("$path/$fileName", file_get_contents($remoteFile)));
    }

    /**
     * @param string $path
     *
     * @param int $mode
     *
     * @return void
     */
    public static function createDir(string $path, int $mode = 0777): void
    {
        if (!is_dir($path)) {
            mkdir($path, $mode, true);
        }
    }
}
