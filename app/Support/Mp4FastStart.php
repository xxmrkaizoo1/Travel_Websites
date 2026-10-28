<?php

namespace App\Support;

/**
 * Moves an MP4's "moov" index ahead of its media data ("faststart"), so a browser can start
 * playing the video before the whole file has downloaded, even from servers that ignore
 * HTTP range requests such as `php artisan serve`.
 */
class Mp4FastStart
{
    /**
     * Atoms inside "moov" that hold the chunk offset tables (stco / co64).
     */
    private const CONTAINER_ATOMS = ['trak', 'mdia', 'minf', 'stbl'];

    /**
     * Rewrite the file in place when its index sits after the media data. Returns whether the
     * file was changed; files that are already faststart or cannot be parsed are left as they are.
     */
    public static function optimize(string $path): bool
    {
        $handle = @fopen($path, 'rb');

        if ($handle === false) {
            return false;
        }

        try {
            $atoms = self::topLevelAtoms($handle, (int) filesize($path));
            $moov = collect($atoms)->firstWhere('type', 'moov');
            $lastMdatOffset = collect($atoms)->where('type', 'mdat')->max('offset');

            if ($moov === null || $lastMdatOffset === null || $moov['offset'] < $lastMdatOffset) {
                return false;
            }

            fseek($handle, $moov['offset']);
            $moovData = self::shiftChunkOffsets((string) fread($handle, $moov['size']), $moov['size']);

            if ($moovData === null) {
                return false;
            }

            $temporaryPath = $path.'.faststart';
            $output = fopen($temporaryPath, 'wb');
            $moovWritten = false;

            foreach ($atoms as $atom) {
                if ($atom['type'] === 'moov') {
                    continue;
                }

                if ($atom['type'] === 'mdat' && ! $moovWritten) {
                    fwrite($output, $moovData);
                    $moovWritten = true;
                }

                fseek($handle, $atom['offset']);
                stream_copy_to_stream($handle, $output, $atom['size']);
            }

            fclose($output);
        } finally {
            fclose($handle);
        }

        return rename($temporaryPath, $path);
    }

    /**
     * @param  resource  $handle
     * @return list<array{type: string, offset: int, size: int}>
     */
    private static function topLevelAtoms($handle, int $fileSize): array
    {
        $atoms = [];
        $offset = 0;

        while ($offset + 8 <= $fileSize) {
            fseek($handle, $offset);
            ['size' => $size, 'type' => $type] = unpack('Nsize/a4type', (string) fread($handle, 8));

            if ($size === 1) {
                $size = unpack('J', (string) fread($handle, 8))[1];
            } elseif ($size === 0) {
                $size = $fileSize - $offset;
            }

            if ($size < 8 || $offset + $size > $fileSize) {
                return [];
            }

            $atoms[] = ['type' => $type, 'offset' => $offset, 'size' => $size];
            $offset += $size;
        }

        return $atoms;
    }

    /**
     * Add the moov size to every chunk offset, since moving moov in front pushes the media data
     * back by exactly that many bytes. Returns null when an offset would no longer fit.
     */
    private static function shiftChunkOffsets(string $moov, int $shift): ?string
    {
        return self::patchChildren($moov, 8, strlen($moov), $shift) ? $moov : null;
    }

    private static function patchChildren(string &$data, int $start, int $end, int $shift): bool
    {
        $offset = $start;

        while ($offset + 8 <= $end) {
            ['size' => $size, 'type' => $type] = unpack('Nsize/a4type', substr($data, $offset, 8));

            if ($size < 8 || $offset + $size > $end) {
                return false;
            }

            if (in_array($type, self::CONTAINER_ATOMS, true) && ! self::patchChildren($data, $offset + 8, $offset + $size, $shift)) {
                return false;
            }

            if ($type === 'stco' || $type === 'co64') {
                $entrySize = $type === 'stco' ? 4 : 8;
                $format = $type === 'stco' ? 'N' : 'J';
                $count = unpack('N', substr($data, $offset + 12, 4))[1];

                for ($index = 0; $index < $count; $index++) {
                    $position = $offset + 16 + $index * $entrySize;
                    $chunkOffset = unpack($format, substr($data, $position, $entrySize))[1] + $shift;

                    if ($type === 'stco' && $chunkOffset > 0xFFFFFFFF) {
                        return false;
                    }

                    $data = substr_replace($data, pack($format, $chunkOffset), $position, $entrySize);
                }
            }

            $offset += $size;
        }

        return true;
    }
}
