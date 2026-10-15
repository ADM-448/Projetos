<?php

namespace App\Services\MarketSalary;

class StackTagExtractor
{
    /**
     * Canonical stack => accepted tokens (word-boundary match).
     *
     * @var array<string, list<string>>
     */
    private const ALIASES = [
        'javascript' => ['javascript', 'js'],
        'typescript' => ['typescript', 'ts'],
        'java' => ['java'],
        'python' => ['python'],
        'php' => ['php'],
        'laravel' => ['laravel'],
        'react' => ['react'],
        'node' => ['node', 'nodejs', 'node.js'],
        'golang' => ['golang'],
        'go' => ['go'],
        'kotlin' => ['kotlin'],
        'swift' => ['swift'],
        'angular' => ['angular'],
        'vue' => ['vue', 'vuejs'],
        'rust' => ['rust'],
        'scala' => ['scala'],
        'flutter' => ['flutter'],
        'android' => ['android'],
        'ios' => ['ios'],
        'csharp' => ['csharp', 'c#', 'csharp'],
        'dotnet' => ['dotnet', '.net', 'net'],
        'ruby' => ['ruby'],
    ];

    /**
     * @param  array<int, string>|null  $tags
     * @return list<string>
     */
    public function extract(?array $tags): array
    {
        if (empty($tags)) {
            return [];
        }

        $found = [];
        foreach ($tags as $tag) {
            $normalized = $this->normalizeToken((string) $tag);
            foreach (self::ALIASES as $stack => $tokens) {
                foreach ($tokens as $token) {
                    $quoted = preg_quote($token, '/');
                    if ($normalized === $this->normalizeToken($token) || preg_match('/\b'.$quoted.'\b/u', $normalized)) {
                        $found[] = $stack;
                        break;
                    }
                }
            }
        }

        return array_values(array_unique($found));
    }

    public function primary(?array $tags): ?string
    {
        $stacks = $this->extract($tags);

        return $stacks[0] ?? null;
    }

    public function hasStack(?array $tags): bool
    {
        return $this->primary($tags) !== null;
    }

    public function normalizeToken(string $tag): string
    {
        $tag = mb_strtolower(trim($tag));

        return preg_replace('/\s+/', ' ', $tag) ?? $tag;
    }
}
