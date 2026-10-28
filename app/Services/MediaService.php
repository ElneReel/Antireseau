<?php

namespace App\Services;

use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaService
{
    protected array $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
    protected int $maxBytes = 3145728; // 3 Mo
    protected int $maxDimension = 2560; // Max width or height

    /**
     * Valide et traite un fichier image de publication :
     * - Vérification de type MIME et taille
     * - Vérification des dimensions
     * - Réencodage via GD pour purger les métadonnées EXIF et toute charge malveillante
     * - Calcul du hash sha256 du contenu
     * - Stockage sécurisé sur le disque privé local
     */
    public function processPostImage(UploadedFile $file, User $user, ?string $altText = null): Media
    {
        $this->validateImageFile($file);

        $fileHash = hash_file('sha256', $file->getRealPath());
        $imageInfo = $this->sanitizeAndReencodeImage($file);

        $disk = config('insociable.media.disk', 'local');
        $randomName = Str::random(40) . '.' . $imageInfo['extension'];
        $storageDir = config('insociable.media.storage_path', 'private/media');
        $fullPath = $storageDir . '/' . $randomName;

        Storage::disk($disk)->put($fullPath, $imageInfo['binary_data']);

        return Media::create([
            'user_id' => $user->id,
            'disk' => $disk,
            'file_path' => $fullPath,
            'original_name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'mime_type' => $imageInfo['mime_type'],
            'file_size' => strlen($imageInfo['binary_data']),
            'width' => $imageInfo['width'],
            'height' => $imageInfo['height'],
            'alt_text' => $altText ? mb_substr(strip_tags($altText), 0, 255) : null,
            'file_hash' => $fileHash,
        ]);
    }

    /**
     * Traite un avatar pour un profil.
     */
    public function processAvatar(UploadedFile $file, User $user): string
    {
        $this->validateImageFile($file);

        $imageInfo = $this->sanitizeAndReencodeImage($file, 400, 400);

        $disk = config('insociable.media.disk', 'local');
        $randomName = 'avatar_' . $user->id . '_' . Str::random(20) . '.' . $imageInfo['extension'];
        $storageDir = config('insociable.media.avatar_path', 'private/avatars');
        $fullPath = $storageDir . '/' . $randomName;

        Storage::disk($disk)->put($fullPath, $imageInfo['binary_data']);

        return $fullPath;
    }

    /**
     * Supprime physiquement le fichier d'un média.
     */
    public function deleteMedia(Media $media): void
    {
        if (Storage::disk($media->disk)->exists($media->file_path)) {
            Storage::disk($media->disk)->delete($media->file_path);
        }

        $media->delete();
    }

    protected function validateImageFile(UploadedFile $file): void
    {
        if (!$file->isValid()) {
            throw new \InvalidArgumentException("Le fichier envoyé n'est pas valide.");
        }

        if ($file->getSize() > $this->maxBytes) {
            throw new \InvalidArgumentException("L'image dépasse la taille maximale autorisée de 3 Mo.");
        }

        $mime = $file->getMimeType();
        if (!in_array($mime, $this->allowedMimes, true)) {
            throw new \InvalidArgumentException("Format d'image non supporté. Formats acceptés : JPEG, PNG, WebP.");
        }
    }

    /**
     * Réencode l'image via l'extension GD de PHP pour éliminer les métadonnées (EXIF/GPS)
     * et redimensionne si elle dépasse la dimension maximale.
     */
    protected function sanitizeAndReencodeImage(UploadedFile $file, ?int $maxWidth = null, ?int $maxHeight = null): array
    {
        $path = $file->getRealPath();
        $maxWidth = $maxWidth ?? $this->maxDimension;
        $maxHeight = $maxHeight ?? $this->maxDimension;

        $imageSize = @getimagesize($path);
        if ($imageSize === false) {
            throw new \InvalidArgumentException("Le fichier n'est pas une image valide.");
        }

        [$origWidth, $origHeight, $type] = $imageSize;

        $srcImage = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_WEBP => @imagecreatefromwebp($path),
            default => false,
        };

        if (!$srcImage) {
            throw new \InvalidArgumentException("Impossible de décoder l'image fournie.");
        }

        // Calcul des nouvelles dimensions proportionnelles
        $ratio = min($maxWidth / $origWidth, $maxHeight / $origHeight, 1.0);
        $newWidth = (int) round($origWidth * $ratio);
        $newHeight = (int) round($origHeight * $ratio);

        $dstImage = imagecreatetruecolor($newWidth, $newHeight);

        // Préservation de la transparence pour PNG/WebP
        imagealphablending($dstImage, false);
        imagesavealpha($dstImage, true);
        $transparent = imagecolorallocatealpha($dstImage, 255, 255, 255, 127);
        imagefilledrectangle($dstImage, 0, 0, $newWidth, $newHeight, $transparent);

        imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

        // Réencodage en WebP (ou JPEG) sans métadonnées
        ob_start();
        imagewebp($dstImage, null, 85);
        $binary = ob_get_clean();

        imagedestroy($srcImage);
        imagedestroy($dstImage);

        return [
            'binary_data' => $binary,
            'extension' => 'webp',
            'mime_type' => 'image/webp',
            'width' => $newWidth,
            'height' => $newHeight,
        ];
    }
}
