<?php

namespace App\Twig;

use App\Entity\Section;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    /** @var array<string, string|null> Cache local statique pour la requête */
    private static array $resolvedMediaCache = [];
    private const SUB_DIRS = ['uploads/pages', 'images', 'uploads/prestations'];

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('media_url', [$this, 'getMediaUrl']),
            new TwigFunction('has_active_prestation', [$this, 'hasActivePrestation']),
        ];
    }

    public function hasActivePrestation(?\App\Entity\Prestation $prestation, ?\App\Entity\User $user = null): bool
    {
        if (!$user || !$prestation) {
            return false;
        }

        return $user->hasActivePrestation($prestation);
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('clean_excerpt', [$this, 'cleanExcerpt']),
            new TwigFilter('format_paragraphs', [$this, 'formatParagraphs'], ['is_safe' => ['html']]),
        ];
    }

    public function getMediaUrl(mixed $media): ?string
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

        if (isset(self::$resolvedMediaCache[$media])) {
            return self::$resolvedMediaCache[$media];
        }

        // URL absolue ou chemin relatif direct
        if (preg_match('#^(https?://|//|data:)#i', $media) || str_starts_with($media, '/')) {
            return self::$resolvedMediaCache[$media] = $media;
        }

        $cleanFilename = basename(parse_url($media, PHP_URL_PATH));
        $publicDir = $this->projectDir . DIRECTORY_SEPARATOR . 'public';

        foreach (self::SUB_DIRS as $subDir) {
            $fullPath = $publicDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $subDir) . DIRECTORY_SEPARATOR . $cleanFilename;
            if (file_exists($fullPath) && is_file($fullPath)) {
                return self::$resolvedMediaCache[$media] = '/' . $subDir . '/' . $cleanFilename;
            }
        }

        // Fallback par défaut vers /images/
        return self::$resolvedMediaCache[$media] = '/images/' . $cleanFilename;
    }

    public function cleanExcerpt(?string $text, int $length = 90): string
    {
        if (!$text) {
            return '';
        }

        $clean = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $clean = preg_replace('/\s+/', ' ', trim($clean));

        if (mb_strlen($clean) <= $length) {
            return $clean;
        }

        return mb_substr($clean, 0, $length) . '...';
    }

    /**
     * Convertit automatiquement le texte de section en paragraphes distincts (<p>)
     * à chaque retour à la ligne ou saut de paragraphe, tout en préservant le code HTML
     * si l'utilisateur a saisi du balisage de bloc personnalisé (<p>, <div>, <h3>, etc.).
     */
    public function formatParagraphs(?string $content, ?string $textColor = null, string $extraClasses = ''): string
    {
        if ($content === null || trim($content) === '') {
            return '';
        }

        $trimmed = trim($content);

        // Si le contenu contient déjà des balises de bloc HTML explicites, on respecte le balisage existant
        if (preg_match('/<\s*(p|div|h[1-6]|ul|ol|li|table|blockquote|section|article|header|footer|hr|pre|style|script)\b/i', $trimmed)) {
            return $trimmed;
        }

        // Normalisation des sauts de ligne
        $normalized = str_replace(["\r\n", "\r"], "\n", $trimmed);

        // Découpage par blocs de paragraphes (séparés par un ou plusieurs sauts de ligne avec éventuels espaces)
        $blocks = preg_split('/\n\s*\n+/', $normalized);
        if (!$blocks) {
            $blocks = [$normalized];
        }

        $htmlOutput = [];
        $colorClass = $textColor ? 'text-' . $textColor : 'text-ivory';
        $classes = trim('fw-light mb-4 ' . $colorClass . ' ' . $extraClasses);

        foreach ($blocks as $block) {
            $block = trim($block);
            if ($block === '') {
                continue;
            }

            // Au sein d'un même paragraphe, les simples retours à la ligne deviennent des <br>
            $inner = nl2br($block);

            $htmlOutput[] = '<p class="' . htmlspecialchars($classes) . '" style="font-size: 1.05rem; line-height: 1.85;">' . $inner . '</p>';
        }

        return implode("\n", $htmlOutput);
    }
}
