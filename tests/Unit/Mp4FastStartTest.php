<?php

namespace Tests\Unit;

use App\Support\Mp4FastStart;
use PHPUnit\Framework\TestCase;

class Mp4FastStartTest extends TestCase
{
    private string $path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->path = (string) tempnam(sys_get_temp_dir(), 'mp4');
    }

    protected function tearDown(): void
    {
        @unlink($this->path);

        parent::tearDown();
    }

    public function test_moves_index_before_media_and_keeps_chunk_offsets_pointing_at_the_same_bytes(): void
    {
        $ftyp = $this->atom('ftyp', 'isom'.pack('N', 512).'isomavc1');
        $mdat = $this->atom('mdat', 'CHUNK-ONE'.'CHUNK-TWO');
        $firstChunk = strlen($ftyp) + 8;
        file_put_contents($this->path, $ftyp.$mdat.$this->moov([$firstChunk, $firstChunk + 9]));

        $this->assertTrue(Mp4FastStart::optimize($this->path));

        $data = (string) file_get_contents($this->path);
        $this->assertSame('moov', substr($data, strlen($ftyp) + 4, 4));
        $this->assertSame(strlen($ftyp.$mdat.$this->moov([0, 0])), strlen($data));

        $offsets = array_values(unpack('N2', substr($data, strpos($data, 'stco') + 12, 8)));
        $this->assertSame('CHUNK-ONE', substr($data, $offsets[0], 9));
        $this->assertSame('CHUNK-TWO', substr($data, $offsets[1], 9));
    }

    public function test_leaves_files_that_already_start_with_the_index_unchanged(): void
    {
        $contents = $this->atom('ftyp', 'isom').$this->moov([100]).$this->atom('mdat', 'CHUNK');
        file_put_contents($this->path, $contents);

        $this->assertFalse(Mp4FastStart::optimize($this->path));
        $this->assertSame($contents, file_get_contents($this->path));
    }

    public function test_leaves_unparseable_files_unchanged(): void
    {
        file_put_contents($this->path, 'not a video at all');

        $this->assertFalse(Mp4FastStart::optimize($this->path));
        $this->assertSame('not a video at all', file_get_contents($this->path));
    }

    /**
     * @param  list<int>  $chunkOffsets
     */
    private function moov(array $chunkOffsets): string
    {
        $stco = $this->atom('stco', pack('N', 0).pack('N', count($chunkOffsets)).pack('N*', ...$chunkOffsets));

        return $this->atom('moov', $this->atom('trak', $this->atom('mdia', $this->atom('minf', $this->atom('stbl', $stco)))));
    }

    private function atom(string $type, string $payload): string
    {
        return pack('N', strlen($payload) + 8).$type.$payload;
    }
}
