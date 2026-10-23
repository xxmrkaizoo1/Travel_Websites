<?php

namespace App\Models;

use Database\Factories\ChinaMuslimPackageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChinaMuslimPackage extends Model
{
    /** @use HasFactory<ChinaMuslimPackageFactory> */
    use HasFactory;

    protected $fillable = [
        'position',
        'title',
        'image_path',
        'price',
        'days',
        'nights',
        'is_hot',
        'link',
        'details',
    ];

    protected function casts(): array
    {
        return [
            'is_hot' => 'boolean',
            'price' => 'integer',
            'days' => 'integer',
            'nights' => 'integer',
        ];
    }

    public function imageUrl(): string
    {
        return '/storage/'.ltrim($this->image_path, '/');
    }

    /**
     * Parses the plain-text details into itinerary days and info lists.
     *
     * @return array{days: list<array{number: string, title: string, meals: string, items: list<string>}>, note: string, sections: array<string, list<string>>}
     */
    public function parsedDetails(): array
    {
        $parsed = ['days' => [], 'note' => '', 'sections' => []];
        $headings = [
            'mandatory tours' => 'Mandatory Tours',
            'shopping stop' => 'Shopping Stop',
            'included' => 'Included',
            'excluded' => 'Excluded',
            'durations' => 'Duration',
            'duration' => 'Duration',
            'language' => 'Language',
            'languages' => 'Language',
        ];
        $currentDay = null;
        $currentSection = null;

        foreach (preg_split('/\R/u', (string) $this->details) as $rawLine) {
            $line = trim($rawLine);

            if ($line === '') {
                continue;
            }

            if (preg_match('/^DAY\s*(\d+)\s*[:\-–]\s*(.+?)(?:\s*\(([^)]*)\))?$/iu', $line, $match)) {
                $parsed['days'][] = ['number' => $match[1], 'title' => $match[2], 'meals' => $match[3] ?? '', 'items' => []];
                $currentDay = array_key_last($parsed['days']);
                $currentSection = null;

                continue;
            }

            if (preg_match('/^(?:📝\s*)?note\s*:\s*(.*)$/iu', $line, $match)) {
                $parsed['note'] = $match[1];
                $currentDay = null;
                $currentSection = null;

                continue;
            }

            $headingKey = mb_strtolower(rtrim($line, ': '));

            if (isset($headings[$headingKey])) {
                $currentSection = $headings[$headingKey];
                $currentDay = null;
                $parsed['sections'][$currentSection] ??= [];

                continue;
            }

            if ($currentDay !== null) {
                $parsed['days'][$currentDay]['items'][] = $line;
            } elseif ($currentSection !== null) {
                $parsed['sections'][$currentSection][] = $line;
            }
        }

        return $parsed;
    }

    /**
     * Section form keys mapped to the headings used in the stored details text.
     *
     * @return array<string, string>
     */
    public static function sectionHeadings(): array
    {
        return [
            'mandatory' => 'Mandatory Tours',
            'shopping' => 'Shopping Stop',
            'included' => 'Included',
            'excluded' => 'Excluded',
            'duration' => 'Duration',
            'language' => 'Language',
        ];
    }

    /**
     * Builds the stored details text from the admin slide editor fields.
     *
     * @param  list<array{title?: ?string, meals?: ?string, items?: ?string}>  $days
     * @param  array<string, ?string>  $sections
     */
    public static function composeDetails(array $days, array $sections, ?string $note): ?string
    {
        $lines = [];
        $number = 0;

        foreach ($days as $day) {
            $title = trim((string) ($day['title'] ?? ''));

            if ($title === '') {
                continue;
            }

            $meals = trim((string) ($day['meals'] ?? ''));
            $lines[] = sprintf('DAY %02d: %s%s', ++$number, $title, $meals !== '' ? " ($meals)" : '');

            foreach (preg_split('/\R/u', (string) ($day['items'] ?? '')) as $item) {
                if (trim($item) !== '') {
                    $lines[] = trim($item);
                }
            }
        }

        if (trim((string) $note) !== '') {
            $lines[] = 'Note: '.trim((string) $note);
        }

        foreach (self::sectionHeadings() as $key => $heading) {
            $items = array_filter(array_map('trim', preg_split('/\R/u', (string) ($sections[$key] ?? ''))));

            if ($items !== []) {
                $lines[] = $heading.':';
                array_push($lines, ...$items);
            }
        }

        return $lines === [] ? null : implode("\n", $lines);
    }

    public function formattedPrice(): string
    {
        return 'RM'.number_format($this->price, 2);
    }
}
