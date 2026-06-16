<?php

namespace app\Services\Profile;

class ProfilePhotoUploadService
{
    protected const MAX_IMAGE_SIZE = 5 * 1024 * 1024;

    public function upload(array $file, string $accountType, int $accountId): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return [
                'success' => true,
                'path' => null,
            ];
        }

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return $this->failure('No fue posible subir la foto de perfil.');
        }

        if (!isset($file['tmp_name']) || !is_file($file['tmp_name'])) {
            return $this->failure('El archivo temporal no es valido.');
        }

        if (($file['size'] ?? 0) > self::MAX_IMAGE_SIZE) {
            return $this->failure('La foto supera el tamano maximo permitido de 5MB.');
        }

        $mime = $this->detectMimeType((string) $file['tmp_name']);
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        if (!isset($allowed[$mime])) {
            return $this->failure('Solo se permiten imagenes JPG, PNG o WEBP.');
        }

        if (@getimagesize((string) $file['tmp_name']) === false) {
            return $this->failure('El archivo no contiene una imagen valida.');
        }

        $folder = $accountType === 'admin' ? 'admins' : 'customers';
        $relativeDir = '/img/profiles/' . $folder . '/' . $accountId;
        $absoluteDir = public_path(ltrim($relativeDir, '/'));

        if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0755, true) && !is_dir($absoluteDir)) {
            return $this->failure('No fue posible crear la carpeta para la foto.');
        }

        $fileName = 'profile-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
        $relativePath = $relativeDir . '/' . $fileName;
        $absolutePath = $absoluteDir . DIRECTORY_SEPARATOR . $fileName;

        if (!is_uploaded_file((string) $file['tmp_name']) || !move_uploaded_file((string) $file['tmp_name'], $absolutePath)) {
            return $this->failure('No fue posible guardar la foto de perfil.');
        }

        return [
            'success' => true,
            'path' => $relativePath,
        ];
    }

    public function deleteStored(?string $path): void
    {
        $path = trim((string) $path);

        if ($path === '' || !str_starts_with($path, '/img/profiles/')) {
            return;
        }

        $absolutePath = public_path(ltrim($path, '/'));
        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }

    protected function detectMimeType(string $path): ?string
    {
        if (class_exists(\finfo::class)) {
            $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
            if (is_string($mime) && $mime !== '') {
                return $mime;
            }
        }

        $mime = mime_content_type($path);
        return is_string($mime) && $mime !== '' ? $mime : null;
    }

    protected function failure(string $message): array
    {
        return [
            'success' => false,
            'path' => null,
            'errors' => [
                'profile_photo' => [$message],
            ],
        ];
    }
}
