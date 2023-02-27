<?php

namespace Own3d\DesktopOverlay;

class GenerateVerifiedGames
{
    public function generate(): void
    {
        $verifiedGames = [];

        $nextPageUrl = 'https://api.own3d.pro/v1/verified-games';
        do {
            $games = json_decode(file_get_contents($nextPageUrl), true);

            foreach ($games['data'] as $game) {
                $defaultNotes = $game['supported']
                    ? sprintf('If you have any issues with **%s**, please [upvote or create an new GitHub issue](https://github.com/own3d/desktop-overlay/issues)', $game['name'])
                    : sprintf('We are not able to support **%s** at this time.', $game['name']);
                
                $verifiedGames[] = [
                    $game['supported'] ? '✅' : '❌',
                    $game['name'],
                    $game['notes'] ?? $defaultNotes,
                ];
            }

            $nextPageUrl = $games['next_page_url'];
        } while ($nextPageUrl);

        // update stub file
        $stub = file_get_contents(__DIR__ . '/../stubs/verified-games.md');

        $games = $this->formattedTable(['Supported', 'Game', 'Notes'], $verifiedGames);

        $stub = str_replace('{{games}}', $games, $stub);
        $stub = str_replace('{{date}}', date('Y-m-d H:i:s'), $stub);

        file_put_contents(__DIR__ . '/../verified-games.md', $stub);
    }

    /**
     * Reformat Markdown table to be more readable
     *
     * eg:
     * | Works | Game | Notes |
     * |-------|------|-------|
     * | ✅ | Apex Legends | Very long description |
     *
     * becomes depending on the longest string in each column:
     * | Supported | Game         | Notes                 |
     * |-----------|--------------|-----------------------|
     * | ✅        | Apex Legends | Very long description |
     *
     */
    private function formattedTable(array $headers, array $rows): string
    {
        $columnWidths = array_map('strlen', $headers);

        foreach ($rows as $row) {
            foreach ($row as $index => $column) {
                $columnWidths[$index] = max($columnWidths[$index], strlen($column));
            }
        }

        $table = [];

        foreach ($rows as $row) {
            $table[] = '|' . implode('|', array_map(function ($columnWidth, $column) {
                    return ' ' . str_pad($column, $columnWidth) . ' ';
                }, $columnWidths, $row)) . "|\n";
        }

        // prepend line with correct column widths for header (no need to add space between | and -)
        array_unshift($table, '|' . implode('|', array_map(function ($columnWidth) {
                return str_repeat('-', $columnWidth + 2);
            }, $columnWidths)) . "|\n");

        // prepend header line with correct column widths (add space between | and word)
        array_unshift($table, '|' . implode('|', array_map(function ($columnWidth, $header) {
                return ' ' . str_pad($header, $columnWidth) . ' ';
            }, $columnWidths, $headers)) . "|\n");

        return implode('', $table);
    }
}