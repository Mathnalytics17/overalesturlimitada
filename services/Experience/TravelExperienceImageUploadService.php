<?php

namespace app\Services\Experience;

class TravelExperienceImageUploadService
{
    protected const MAX_IMAGE_SIZE = 10 * 1024 * 1024;
    protected const MAX_EXPERIENCE_IMAGES = 10;

    protected string $publicRoot;
    protected string $publicBaseDir = '/img/experiences';

    public function __construct()
    {
        $this->publicRoot = rtrim(\app\Core\Application::$ROOT_DIR, DIRECTORY_SEPARATOR) . '/public';
    }

    public function uploadImages(int $experienceId, array $files): array
    {
        $result = [
            'success' => true,
            'errors' => [],
            'paths' => [],
        ];

        if (
            !isset($files['experience_images']) ||
            !isset($files['experience_images']['name']) ||
            !is_array($files['experience_images']['name'])
        ) {
            return $result;
        }

        $relativeDir = $this->publicBaseDir . '/' . $experienceId;
        $absoluteDir = $this->publicRoot . $relativeDir;

        if (!is_dir($absoluteDir)) {
            if (!mkdir($absoluteDir, 0755, true) && !is_dir($absoluteDir)) {
                return [
                    'success' => false,
                    'errors' => [
                        'experience_images' => ['No fue posible crear la carpeta de imagenes.'],
                    ],
                    'paths' => [],
                ];
            }
        }

        $names = $files['experience_images']['name'] ?? [];
        $tmpNames = $files['experience_images']['tmp_name'] ?? [];
        $errors = $files['experience_images']['error'] ?? [];
        $sizes = $files['experience_images']['size'] ?? [];
        $types = $files['experience_images']['type'] ?? [];

        $count = is_array($names) ? count($names) : 0;

        if ($count > self::MAX_EXPERIENCE_IMAGES) {
            return [
                'success' => false,
                'errors' => [
                    'experience_images' => ['Solo se permiten hasta ' . self::MAX_EXPERIENCE_IMAGES . ' imagenes por experiencia.'],
                ],
                'paths' => [],
            ];
        }

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
                $result['errors']['experience_images'][] = 'Error al subir una imagen.';
                continue;
            }

            $validation = $this->validateImageFile($file);
            if (!$validation['success']) {
                $result['errors']['experience_images'] = array_merge(
                    $result['errors']['experience_images'] ?? [],
                    $validation['errors']['image'] ?? ['Archivo invalido.']
                );
                continue;
            }

            $extension = $validation['extension'];
            $fileName = 'exp-' . ($i + 1) . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;

            $absolutePath = $absoluteDir . '/' . $fileName;
            $relativePath = $relativeDir . '/' . $fileName;

            if (!is_uploaded_file($file['tmp_name']) || !move_uploaded_file($file['tmp_name'], $absolutePath)) {
                $result['errors']['experience_images'][] = 'No fue posible guardar una de las imagenes.';
                continue;
            }

            $result['paths'][] = $relativePath;
        }

        if (!empty($result['errors'])) {
            $result['success'] = false;
        }

        return $result;
    }

    protected function validateImageFile(array $file): array
    {
        if (!isset($file['tmp_name']) || !is_file($file['tmp_name'])) {
            return [
                'success' => false,
                'errors' => [
                    'image' => ['El archivo temporal no es valido.'],
                ],
            ];
        }

        if (($file['size'] ?? 0) > self::MAX_IMAGE_SIZE) {
            return [
                'success' => false,
                'errors' => [
                    'image' => ['La imagen supera el tamano maximo permitido de 10MB.'],
                ],
            ];
        }

        $mime = $this->detectMimeType((string) $file['tmp_name']);
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
                    'image' => ['Solo se permiten imagenes JPG, PNG, WEBP o GIF.'],
                ],
            ];
        }

        if (@getimagesize((string) $file['tmp_name']) === false) {
            return [
                'success' => false,
                'errors' => [
                    'image' => ['El archivo no contiene una imagen valida.'],
                ],
            ];
        }

        return [
            'success' => true,
            'extension' => $allowed[$mime],
        ];
    }

    protected function detectMimeType(string $path): ?string
    {
        if (class_exists(\finfo::class)) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($path);
            if (is_string($mimeType) && $mimeType !== '') {
                return $mimeType;
            }
        }

        $mimeType = mime_content_type($path);
        return is_string($mimeType) && $mimeType !== '' ? $mimeType : null;
    }
}
