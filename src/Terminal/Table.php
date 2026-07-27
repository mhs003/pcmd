<?php

declare(strict_types=1);

namespace Pcmd\Terminal;

final class Table
{
    /**
     * @param list<string> $headers
     * @param list<list<string>> $rows
     */
    public function render(array $headers, array $rows): string
    {
        if ($headers === [] && $rows === []) {
            return '';
        }

        $widths = $this->calculateWidths($headers, $rows);

        $output = '';

        if ($headers !== []) {
            $output .= $this->renderRow($headers, $widths) . "\n";
            $output .= $this->renderSeparator($widths) . "\n";
        }

        foreach ($rows as $row) {
            $output .= $this->renderRow($row, $widths) . "\n";
        }

        return $output;
    }

    /**
     * @param list<string> $headers
     * @param list<list<string>> $rows
     * @return array<int, int>
     */
    private function calculateWidths(array $headers, array $rows): array
    {
        $columnCount = max(count($headers), ...array_map('count', $rows));
        $widths = [];

        for ($i = 0; $i < $columnCount; $i++) {
            $widths[] = 0;
        }

        foreach ($headers as $i => $header) {
            $widths[$i] = strlen($header);
        }

        foreach ($rows as $row) {
            foreach ($row as $i => $cell) {
                $widths[$i] = max($widths[$i] ?? 0, strlen($cell));
            }
        }

        return $widths;
    }

    /**
     * @param list<string> $row
     * @param array<int, int> $widths
     */
    private function renderRow(array $row, array $widths): string
    {
        $parts = [];

        foreach ($widths as $i => $width) {
            $cell = $row[$i] ?? '';
            $parts[] = ' ' . str_pad($cell, $width) . ' ';
        }

        return '|' . implode('|', $parts) . '|';
    }

    /**
     * @param array<int, int> $widths
     */
    private function renderSeparator(array $widths): string
    {
        $parts = [];

        foreach ($widths as $width) {
            $parts[] = '-' . str_repeat('-', $width) . '-';
        }

        return '|' . implode('|', $parts) . '|';
    }
}
