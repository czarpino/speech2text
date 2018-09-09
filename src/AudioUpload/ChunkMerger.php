<?php

namespace App\AudioUpload;


use App\Entity\AudioUploadChunk;

class ChunkMerger
{
    private $chunkDataDir;

    public function __construct(string $chunkDataDir)
    {
        $this->chunkDataDir = $chunkDataDir;
    }

    /**
     * @param array $audioUploadChunks
     * @return string
     */
    public function merge(array $audioUploadChunks): string
    {
        usort($audioUploadChunks, [$this, 'sort']);

        $merged = '';
        /** @var AudioUploadChunk $audioUploadChunk */
        foreach ($audioUploadChunks as $audioUploadChunk) {
            $merged .= file_get_contents($this->chunkDataDir . '/' . $audioUploadChunk->getFilename());
        }

        return $merged;
    }

    private function sort(AudioUploadChunk $chunkA, AudioUploadChunk $chunkB)
    {
        return $chunkA->getChunkNumber() - $chunkB->getChunkNumber();
    }
}
