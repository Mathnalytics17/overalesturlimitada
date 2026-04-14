<?php

namespace app\Services\Admin\PackageTour;

class PackageTourImageUploadService
{
    protected string $publicRoot;
    protected string $publicBaseDir = '/img/packageTourist';

    public function __construct()
    {
        $this->publicRoot = rtrim(\app\Core\Application::$ROOT_DIR, DIRECTORY_SEPARATOR) . '/public';
    }

    public function uploadPackageImages(string $packageUuid, string $slug, array $files): array
    {
        $folderName = $this->sanitizeFolderName($slug . '-' . $packageUuid);
        $relativeDir = $this->publicBaseDir . '/' . $folderName;
        $absoluteDir = $this->publicRoot . $relativeDir;

        if (!is_dir($absoluteDir)) {
            if (!mkdir($absoluteDir, 0755, true) && !is_dir($absoluteDir)) {
                return [
                    'success' => false,
                    'errors' => [
                        'images' => ['No fue posible crear la carpeta del paquete.'],
                    ],
                    'data' => [],
                ];
            }
        }

        $coverPath = null;
        $galleryPaths = [];

        if (isset($files['cover_image_file'])) {
            $coverResult = $this->uploadSingleImage(
                $files['cover_image_file'],
                $absoluteDir,
                $relativeDir,
                'cover'
            );

            if (!$coverResult['success']) {
                return $coverResult;
            }

            $coverPath = $coverResult['path'];
        }

        if (isset($files['gallery_files'])) {
            $galleryResult = $this->uploadMultipleImages(
                $files['gallery_files'],
                $absoluteDir,
                $relativeDir,
                'gallery'
            );

            if (!$galleryResult['success']) {
                return $galleryResult;
            }

            $galleryPaths = $galleryResult['paths'];
        }

        return [
            'success' => true,
            'errors' => [],
            'data' => [
                'cover_path' => $coverPath,
                'gallery_paths' => $galleryPaths,
                'folder' => $relativeDir,
            ],
        ];
    }

    protected function uploadSingleImage(array $file, string $absoluteDir, string $relativeDir, string $prefix): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return [
                'success' => true,
                'path' => null,
            ];
        }

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'errors' => [
                    'cover_image_file' => ['Error al subir la imagen de portada.'],
                ],
                'data' => [],
            ];
        }

        $validation = $this->validateImageFile($file);
        if (!$validation['success']) {
            return $validation;
        }

        $extension = $validation['extension'];
        $fileName = $prefix . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;

        $absolutePath = $absoluteDir . '/' . $fileName;
        $relativePath = $relativeDir . '/' . $fileName;

        if (!is_uploaded_file($file['tmp_name']) || !move_uploaded_file($file['tmp_name'], $absolutePath)) {
            return [
                'success' => false,
                'errors' => [
                    'cover_image_file' => ['No fue posible guardar la imagen de portada.'],
                ],
                'data' => [],
            ];
        }

        return [
            'success' => true,
            'path' => $relativePath,
        ];
    }

    protected function uploadMultipleImages(array $files, string $absoluteDir, string $relativeDir, string $prefix): array
    {
        $paths = [];

        $names = $files['name'] ?? [];
        $tmpNames = $files['tmp_name'] ?? [];
        $errors = $files['error'] ?? [];
        $sizes = $files['size'] ?? [];
        $types = $files['type'] ?? [];

        $count = is_array($names) ? count($names) : 0;

        for ($i = 0; $i < $count; $i++) {
            if (($errors[$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $file = [
                'name' => $names[$i] ?? null,
                'tmp_name' => $tmpNames[$i] ?? null,
                'error' => $errors[$i] ?? null,
                'size' => $sizes[$i] ?? null,
                'type' => $types[$i] ?? null,
            ];

            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                return [
                    'success' => false,
                    'errors' => [
                        'gallery_files' => ['Error al subir una imagen de la galería.'],
                    ],
                    'data' => [],
                ];
            }

            $validation = $this->validateImageFile($file);
            if (!$validation['success']) {
                return [
                    'success' => false,
                    'errors' => [
                        'gallery_files' => $validation['errors']['image'] ?? ['Archivo inválido en la galería.'],
                    ],
                    'data' => [],
                ];
            }

            $extension = $validation['extension'];
            $fileName = $prefix . '-' . ($i + 1) . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;

            $absolutePath = $absoluteDir . '/' . $fileName;
            $relativePath = $relativeDir . '/' . $fileName;

            if (!is_uploaded_file($file['tmp_name']) || !move_uploaded_file($file['tmp_name'], $absolutePath)) {
                return [
                    'success' => false,
                    'errors' => [
                        'gallery_files' => ['No fue posible guardar una imagen de la galería.'],
                    ],
                    'data' => [],
                ];
            }

            $paths[] = $relativePath;
        }

        return [
            'success' => true,
            'paths' => $paths,
        ];
    }

    protected function validateImageFile(array $file): array
    {
        $maxSize = 10 * 1024 * 1024;

        if (($file['size'] ?? 0) > $maxSize) {
            return [
                'success' => false,
                'errors' => [
                    'image' => ['La imagen supera el tamaño máximo permitido de 10MB.'],
                ],
            ];
        }

        $mime = mime_content_type($file['tmp_name']);
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];

        if (!isset($allowed[$mime])) {
            return [
                'success' => false,
                'errors' => [
                    'image' => ['Solo se permiten imágenes JPG, PNG, WEBP o GIF.'],
                ],
            ];
        }

        return [
            'success' => true,
            'extension' => $allowed[$mime],
        ];
    }

    protected function sanitizeFolderName(string $value): string
    {
        $value = mb_strtolower($value);
        $value = preg_replace('/[^a-z0-9\-]+/u', '-', $value);
        $value = preg_replace('/-+/', '-', $value);
        return trim($value, '-');
    }
}