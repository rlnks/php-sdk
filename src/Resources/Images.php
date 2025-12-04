<?php

declare(strict_types=1);

namespace Rlnks\Resources;

use Rlnks\Models\Image;
use Rlnks\Models\PaginatedResponse;

/**
 * Images resource for managing image assets.
 *
 * Images can be uploaded and organized into folders, then referenced
 * in decision trees as outputs for image-type trees.
 */
class Images extends Resource
{
    /**
     * List all images.
     *
     * @param array $options Query options:
     *                       - folder: Filter by folder name
     *                       - search: Search by filename
     *                       - sort_by: Field to sort (default: 'created_at')
     *                       - sort_dir: 'asc' or 'desc' (default: 'desc')
     *                       - per_page: Items per page (default: 20)
     *                       - page: Page number
     *
     * @return PaginatedResponse<Image>
     */
    public function list(array $options = []): PaginatedResponse
    {
        $query = $this->buildQuery([
            'folder' => $options['folder'] ?? null,
            'search' => $options['search'] ?? null,
            'sort_by' => $options['sort_by'] ?? null,
            'sort_dir' => $options['sort_dir'] ?? null,
            'per_page' => $options['per_page'] ?? null,
            'page' => $options['page'] ?? null,
        ]);

        $response = $this->http->get('/api/v1/images', $query);

        return PaginatedResponse::fromResponse($response, Image::class);
    }

    /**
     * Get a single image by ID.
     */
    public function get(string $id): Image
    {
        $response = $this->http->get("/api/v1/images/{$id}");

        return Image::fromResponse($response['data']);
    }

    /**
     * Upload a new image.
     *
     * @param string $filePath Path to the image file
     * @param array $options Upload options:
     *                       - name: Display name for the image
     *                       - folder: Folder to organize the image
     */
    public function upload(string $filePath, array $options = []): Image
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("File not found: {$filePath}");
        }

        $multipart = [
            [
                'name' => 'file',
                'contents' => fopen($filePath, 'r'),
                'filename' => basename($filePath),
            ],
        ];

        if (isset($options['name'])) {
            $multipart[] = [
                'name' => 'name',
                'contents' => $options['name'],
            ];
        }

        if (isset($options['folder'])) {
            $multipart[] = [
                'name' => 'folder',
                'contents' => $options['folder'],
            ];
        }

        $response = $this->http->postMultipart('/api/v1/images', $multipart);

        return Image::fromResponse($response['data']);
    }

    /**
     * Upload image from string content.
     *
     * @param string $content Binary image content
     * @param string $filename Filename for the image
     * @param array $options Upload options (name, folder)
     */
    public function uploadFromString(string $content, string $filename, array $options = []): Image
    {
        $multipart = [
            [
                'name' => 'file',
                'contents' => $content,
                'filename' => $filename,
            ],
        ];

        if (isset($options['name'])) {
            $multipart[] = [
                'name' => 'name',
                'contents' => $options['name'],
            ];
        }

        if (isset($options['folder'])) {
            $multipart[] = [
                'name' => 'folder',
                'contents' => $options['folder'],
            ];
        }

        $response = $this->http->postMultipart('/api/v1/images', $multipart);

        return Image::fromResponse($response['data']);
    }

    /**
     * Update image metadata.
     *
     * @param string $id Image ID
     * @param array $data Fields to update (name, folder)
     */
    public function update(string $id, array $data): Image
    {
        $response = $this->http->put("/api/v1/images/{$id}", $data);

        return Image::fromResponse($response['data']);
    }

    /**
     * Delete an image.
     */
    public function delete(string $id): bool
    {
        $this->http->delete("/api/v1/images/{$id}");

        return true;
    }

    /**
     * Get list of folders.
     *
     * @return array<string> List of folder names
     */
    public function getFolders(): array
    {
        $response = $this->http->get('/api/v1/images-folders');

        return $response['data'] ?? [];
    }

    /**
     * Move image to a different folder.
     */
    public function moveToFolder(string $id, ?string $folder): Image
    {
        return $this->update($id, ['folder' => $folder]);
    }

    /**
     * Rename an image.
     */
    public function rename(string $id, string $name): Image
    {
        return $this->update($id, ['name' => $name]);
    }
}
