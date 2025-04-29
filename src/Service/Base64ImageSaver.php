<?php

namespace App\Service;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class Base64ImageSaver
{

    public function __construct(
        #[Autowire(value: '%upload_dir%')]
        private readonly string $uploadDir,
    )
    {
    }


    public function saveBase64Image(string $base64, LoggerInterface $logger): ?string
    {
        $logger->info("Путь для сохранения изображения: " . $this->uploadDir);
        if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {

            $data = substr($base64, strpos($base64, ',') + 1);
            $type = strtolower($type[1]); // jpg, png, gif

            if (!in_array($type, ['jpg', 'jpeg', 'png', 'gif'])) {
                return null;
            }

            $data = base64_decode($data);
            if ($data === false) return null;

            $filename = uniqid('task_', true) . '.' . $type;
            $filePath = $this->uploadDir . '/' . $filename;

            file_put_contents($filePath, $data);

            return 'uploads/tasks/' . $filename; // относительный путь
        }

        return null;
    }

    public function deleteImage(string $relativePath): void
    {
        $fullPath = $this->uploadDir . '/' . basename($relativePath);
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }
}