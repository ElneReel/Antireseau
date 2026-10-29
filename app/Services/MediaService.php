<?php

namespace App\Services;

use App\Models\Media;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaService
{
    protected array $imageMimes = ['image/jpeg', 'image/png', 'image/webp'];
    protected array $videoMimes = ['video/mp4', 'video/webm', 'video/quicktime'];
    protected array $audioMimes = ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/ogg', 'audio/x-wav'];

    protected int $maxImageBytes = 8388608;  // 8 Mo
    protected int $maxVideoBytes = 52428800; // 50 Mo
    protected int $maxAudioBytes = 20971520; // 20 Mo
    protected int $maxDimension = 2560;

    /**
     * Traite n'importe quel fichier média (Image, Vidéo ou Audio)
     */
    public function processPostMedia(UploadedFile $file, User $user, ?string $altText = null): Media
    {
        if (!$file->isValid()) {
            throw new \InvalidArgumentException("Le fichier envoyé n'est pas valide.");
        }

        $mime = $file->getMimeType();
        $disk = config('insociable.media.disk', 'local');
        $storageDir = config('insociable.media.storage_path', 'private/media');
        $fileHash = hash_file('sha256', $file->getRealPath());

        // 1. Détection IMAGE
        if (in_array($mime, $this->imageMimes, true) || str_starts_with($mime, 'image/')) {
            if ($file->getSize() > $this->maxImageBytes) {
                throw new \InvalidArgumentException("L'image dépasse la taille maximale de 8 Mo.");
            }

            $imageInfo = $this->sanitizeAndReencodeImage($file);
            $randomName = Str::random(40) . '.' . $imageInfo['extension'];
            $fullPath = $storageDir . '/' . $randomName;

            Storage::disk($disk)->put($fullPath, $imageInfo['binary_data']);

            return Media::create([
                'user_id' => $user->id,
                'disk' => $disk,
                'file_path' => $fullPath,
                'original_name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'mime_type' => $imageInfo['mime_type'],
                'media_type' => 'image',
                'file_size' => strlen($imageInfo['binary_data']),
                'width' => $imageInfo['width'],
                'height' => $imageInfo['height'],
                'alt_text' => $altText ? mb_substr(strip_tags($altText), 0, 255) : null,
                'file_hash' => $fileHash,
            ]);
        }

        // 2. Détection VIDÉO
        if (in_array($mime, $this->videoMimes, true) || str_starts_with($mime, 'video/')) {
            if ($file->getSize() > $this->maxVideoBytes) {
                throw new \InvalidArgumentException("La vidéo dépasse la taille maximale autorisée de 50 Mo.");
            }

            $ext = strtolower($file->getClientOriginalExtension());
            if (!in_array($ext, ['mp4', 'webm', 'mov'], true)) {
                $ext = 'mp4';
            }

            $randomName = 'vid_' . Str::random(36) . '.' . $ext;
            $fullPath = $storageDir . '/' . $randomName;

            Storage::disk($disk)->putFileAs($storageDir, $file, $randomName);

            return Media::create([
                'user_id' => $user->id,
                'disk' => $disk,
                'file_path' => $fullPath,
                'original_name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'mime_type' => $mime,
                'media_type' => 'video',
                'file_size' => $file->getSize(),
                'alt_text' => $altText ? mb_substr(strip_tags($altText), 0, 255) : 'Vidéo partagée',
                'file_hash' => $fileHash,
            ]);
        }

        // 3. Détection AUDIO
        if (in_array($mime, $this->audioMimes, true) || str_starts_with($mime, 'audio/')) {
            if ($file->getSize() > $this->maxAudioBytes) {
                throw new \InvalidArgumentException("Le fichier audio dépasse la taille maximale autorisée de 20 Mo.");
            }

            $ext = strtolower($file->getClientOriginalExtension());
            if (!in_array($ext, ['mp3', 'wav', 'ogg'], true)) {
                $ext = 'mp3';
            }

            $randomName = 'aud_' . Str::random(36) . '.' . $ext;
            $fullPath = $storageDir . '/' . $randomName;

            Storage::disk($disk)->putFileAs($storageDir, $file, $randomName);

            return Media::create([
                'user_id' => $user->id,
                'disk' => $disk,
                'file_path' => $fullPath,
                'original_name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'mime_type' => $mime,
                'media_type' => 'audio',
                'file_size' => $file->getSize(),
                'alt_text' => $altText ? mb_substr(strip_tags($altText), 0, 255) : 'Piste audio partagée',
                'file_hash' => $fileHash,
            ]);
        }

        throw new \InvalidArgumentException("Format de fichier non pris en charge. Formats acceptés : Photos (JPEG, PNG, WebP), Vidéos (MP4, WebM), Audio (MP3, WAV, OGG).");
    }

    /**
     * Traite l'avatar d'un profil
     */
    public function processAvatar(UploadedFile $file, User $user): string
    {
        if (!$file->isValid()) {
            throw new \InvalidArgumentException("Le fichier avatar n'est pas valide.");
        }

        $imageInfo = $this->sanitizeAndReencodeImage($file, 400, 400);

        $disk = config('insociable.media.disk', 'local');
        $randomName = 'avatar_' . $user->id . '_' . Str::random(20) . '.' . $imageInfo['extension'];
        $storageDir = config('insociable.media.avatar_path', 'private/avatars');
        $fullPath = $storageDir . '/' . $randomName;

        Storage::disk($disk)->put($fullPath, $imageInfo['binary_data']);

        return $fullPath;
    }

    /**
     * Supprime physiquement le fichier d'un média
     */
    public function deleteMedia(Media $media): void
    {
        if (Storage::disk($media->disk)->exists($media->file_path)) {
            Storage::disk($media->disk)->delete($media->file_path);
        }

        $media->delete();
    }

    /**
     * Réencode l'image via GD pour supprimer métadonnées et redimensionner si nécessaire
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

        $ratio = min($maxWidth / $origWidth, $maxHeight / $origHeight, 1.0);
        $newWidth = (int) round($origWidth * $ratio);
        $newHeight = (int) round($origHeight * $ratio);

        $dstImage = imagecreatetruecolor($newWidth, $newHeight);

        imagealphablending($dstImage, false);
        imagesavealpha($dstImage, true);
        $transparent = imagecolorallocatealpha($dstImage, 255, 255, 255, 127);
        imagefilledrectangle($dstImage, 0, 0, $newWidth, $newHeight, $transparent);

        imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

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
