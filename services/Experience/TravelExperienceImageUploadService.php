<?php

namespace app\Services\Experience;

class TravelExperienceImageUploadService
{
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
                        'experience_images' => ['No fue posible crear la carpeta de imágenes.'],
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
                    $validation['errors']['image'] ?? ['Archivo inválido.']
                );
                continue;
            }

            $extension = $validation['extension'];
            $fileName = 'exp-' . ($i + 1) . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;

            $absolutePath = $absoluteDir . '/' . $fileName;
            $relativePath = $relativeDir . '/' . $fileName;

            if (!is_uploaded_file($file['tmp_name']) || !move_uploaded_file($file['tmp_name'], $absolutePath)) {
                $result['errors']['experience_images'][] = 'No fue posible guardar una de las imágenes.';
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
        $maxSize = 10 * 1024 * 1024;

        if (!isset($file['tmp_name']) || !is_file($file['tmp_name'])) {
            return [
                'success' => false,
                'errors' => [
                    'image' => ['El archivo temporal no es válido.'],
                ],
            ];
        }

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
}