<?php

declare(strict_types=1);

namespace Rlnks\Models;

use DateTimeImmutable;

/**
 * Image model.
 *
 * @property string $id UUID identifier
 * @property string $uuid UUID identifier
 * @property string $filename Display filename
 * @property string $url Full URL to image
 * @property string|null $thumbnail_url Thumbnail URL
 * @property int $width Image width in pixels
 * @property int $height Image height in pixels
 * @property string $dimensions Formatted dimensions (e.g., '1200x600')
 * @property int $size File size in bytes
 * @property string $human_size Human-readable size (e.g., '44.26 KB')
 * @property string $mime_type MIME type (e.g., 'image/jpeg')
 * @property string|null $folder Folder/category
 * @property string $created_at Creation timestamp
 */
class Image extends Model
{
    /**
     * Get image width.
     */
    public function getWidth(): int
    {
        return (int) $this->width;
    }

    /**
     * Get image height.
     */
    public function getHeight(): int
    {
        return (int) $this->height;
    }

    /**
     * Get file size in bytes.
     */
    public function getSize(): int
    {
        return (int) $this->size;
    }

    /**
     * Check if image is in a folder.
     */
    public function inFolder(?string $folder = null): bool
    {
        if ($folder === null) {
            return $this->folder !== null && $this->folder !== '';
        }
        return $this->folder === $folder;
    }

    /**
     * Get creation date as DateTimeImmutable.
     */
    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->created_at ? new DateTimeImmutable($this->created_at) : null;
    }

    /**
     * Check if image is a JPEG.
     */
    public function isJpeg(): bool
    {
        return in_array($this->mime_type, ['image/jpeg', 'image/jpg']);
    }

    /**
     * Check if image is a PNG.
     */
    public function isPng(): bool
    {
        return $this->mime_type === 'image/png';
    }

    /**
     * Check if image is a WebP.
     */
    public function isWebp(): bool
    {
        return $this->mime_type === 'image/webp';
    }

    /**
     * Check if image is a GIF.
     */
    public function isGif(): bool
    {
        return $this->mime_type === 'image/gif';
    }
}
