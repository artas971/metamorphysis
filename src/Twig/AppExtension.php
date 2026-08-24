<?php

namespace App\Twig;

use App\Entity\Section;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('media_url', [$this, 'getMediaUrl']),
        ];
    }

    public function getMediaUrl($media): ?string
    {
        if ($media instanceof Section) {
            $media = $media->getMedia();
        }

        if (!$media || !is_string($media)) {
            return null;
        }

        $media = trim($media);
        if ($media === '') {
            return null;
        }

        // URL absolue ou chemin relatif direct
        if (str_starts_with($media, 'http://') || str_starts_with($media, 'https://') || str_starts_with($media, '/')) {
            return $media;
        }

        $publicDir = $this->projectDir . DIRECTORY_SEPARATOR . 'public';

        // 1. Vérifier dans uploads/pages/
        if (file_exists($publicDir . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . $media)) {
            return '/uploads/pages/' . $media;
        }

        // 2. Vérifier dans images/
        if (file_exists($publicDir . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $media)) {
            return '/images/' . $media;
        }

        // 3. Vérifier dans uploads/prestations/
        if (file_exists($publicDir . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'prestations' . DIRECTORY_SEPARATOR . $media)) {
            return '/uploads/prestations/' . $media;
        }

        // Fallback par défaut vers /images/
        return '/images/' . $media;
    }
}
