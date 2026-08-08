<?php

declare(strict_types=1);

if ($argc !== 3) {
    fwrite(STDERR, "Usage: php create-deterministic-tar.php <source-directory> <output.tar>\n");
    exit(64);
}

$source = realpath($argv[1]);
if ($source === false || ! is_dir($source)) {
    fwrite(STDERR, "Release root does not exist.\n");
    exit(66);
}

$output = $argv[2];
$parent = dirname($output);
if (! is_dir($parent) && ! mkdir($parent, 0777, true) && ! is_dir($parent)) {
    fwrite(STDERR, "Cannot create output directory.\n");
    exit(73);
}

$files = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
);
foreach ($iterator as $item) {
    if (! $item->isFile() || $item->isLink()) {
        continue;
    }
    $relative = str_replace('\\', '/', substr($item->getPathname(), strlen($source) + 1));
    $files[$relative] = $item->getPathname();
}
ksort($files, SORT_STRING);

$stream = fopen($output, 'wb');
if ($stream === false) {
    fwrite(STDERR, "Cannot open archive output.\n");
    exit(73);
}

foreach ($files as $relative => $absolute) {
    $size = filesize($absolute);
    if ($size === false) {
        throw new RuntimeException("Cannot stat {$relative}.");
    }
    [$name, $prefix] = splitUstarPath($relative);
    $header = str_repeat("\0", 512);
    writeField($header, 0, 100, $name);
    writeField($header, 100, 8, octal(0644, 8));
    writeField($header, 108, 8, octal(0, 8));
    writeField($header, 116, 8, octal(0, 8));
    writeField($header, 124, 12, octal($size, 12));
    writeField($header, 136, 12, octal(0, 12));
    writeField($header, 148, 8, str_repeat(' ', 8));
    writeField($header, 156, 1, '0');
    writeField($header, 257, 6, "ustar\0");
    writeField($header, 263, 2, '00');
    writeField($header, 345, 155, $prefix);
    $checksum = 0;
    for ($index = 0; $index < 512; $index++) {
        $checksum += ord($header[$index]);
    }
    writeField($header, 148, 8, sprintf("%06o\0 ", $checksum));
    fwrite($stream, $header);

    $input = fopen($absolute, 'rb');
    if ($input === false) {
        throw new RuntimeException("Cannot read {$relative}.");
    }
    stream_copy_to_stream($input, $stream);
    fclose($input);
    $padding = (512 - ($size % 512)) % 512;
    if ($padding > 0) {
        fwrite($stream, str_repeat("\0", $padding));
    }
}
fwrite($stream, str_repeat("\0", 1024));
fclose($stream);

echo count($files)." files packaged deterministically.\n";

function octal(int $value, int $length): string
{
    return str_pad(decoct($value), $length - 1, '0', STR_PAD_LEFT)."\0";
}

function writeField(string &$header, int $offset, int $length, string $value): void
{
    if (strlen($value) > $length) {
        throw new RuntimeException('USTAR field overflow.');
    }
    $header = substr_replace($header, str_pad($value, $length, "\0"), $offset, $length);
}

/** @return array{string, string} */
function splitUstarPath(string $path): array
{
    if (strlen($path) <= 100) {
        return [$path, ''];
    }
    $positions = [];
    $offset = 0;
    while (($position = strpos($path, '/', $offset)) !== false) {
        $positions[] = $position;
        $offset = $position + 1;
    }
    foreach (array_reverse($positions) as $position) {
        $prefix = substr($path, 0, $position);
        $name = substr($path, $position + 1);
        if (strlen($prefix) <= 155 && strlen($name) <= 100) {
            return [$name, $prefix];
        }
    }
    throw new RuntimeException("Path cannot be represented in USTAR: {$path}");
}
