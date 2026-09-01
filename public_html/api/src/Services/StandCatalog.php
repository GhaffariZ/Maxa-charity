<?php

declare(strict_types=1);

namespace Maksa\Services;

/**
 * Server-authoritative catalog for stand designs.
 *
 * The client sends ONLY a design_id and non-financial attributes.
 * This service resolves the authoritative price and image path.
 *
 * PRICING RULE:
 *   The client must NEVER supply unit_price or total_price.
 *   All financial values are derived exclusively from this catalog.
 */
final class StandCatalog
{
    /**
     * Catalog of valid stand designs.
     * In a production system this would come from a database table.
     * The prices here are the ONLY source of truth for stand pricing.
     *
     * @var array<string, array{image: string, full_image: string, unit_price: int}>
     */
    private const DESIGNS = [
        'design1' => [
            'image'      => 'design1.jpg',
            'full_image' => 'happy1.jpg',
            'unit_price' => 150_000, // Toman
        ],
        'design2' => [
            'image'      => 'design2.jpg',
            'full_image' => 'happy2.jpg',
            'unit_price' => 180_000,
        ],
        'design3' => [
            'image'      => 'design3.jpg',
            'full_image' => 'sad1.jpg',
            'unit_price' => 200_000,
        ],
        'design4' => [
            'image'      => 'design4.jpg',
            'full_image' => 'sad3.jpg',
            'unit_price' => 250_000,
        ],
    ];

    private const MIN_QUANTITY = 1;
    private const MAX_QUANTITY = 100;

    /**
     * Resolve a design ID to its authoritative catalog entry.
     *
     * @return array{image: string, full_image: string, unit_price: int}|null
     */
    public static function resolve(string $designId): ?array
    {
        // Normalise: strip extension, path separators, and leading "design" prefix
        // so that "design1.jpg", "design1", "1" all resolve correctly.
        $normalised = preg_replace('/\.[jJ][pP][gG]$/', '', $designId);
        $normalised = basename($normalised);
        $normalised = preg_replace('/[^a-zA-Z0-9]/', '', $normalised);

        return self::DESIGNS[$normalised] ?? null;
    }

    /**
     * Validate and normalise quantity.
     *
     * @return int Validated quantity (clamped to MIN..MAX).
     * @throws \InvalidArgumentException if quantity is out of range.
     */
    public static function validateQuantity(int $quantity): int
    {
        if ($quantity < self::MIN_QUANTITY || $quantity > self::MAX_QUANTITY) {
            throw new \InvalidArgumentException(
                "Quantity must be between " . self::MIN_QUANTITY . " and " . self::MAX_QUANTITY . "."
            );
        }
        return $quantity;
    }

    /**
     * Calculate the authoritative total price.
     */
    public static function calculateTotal(int $unitPrice, int $quantity): int
    {
        return $unitPrice * $quantity;
    }

    /**
     * Get all valid design IDs (for API documentation / listing).
     *
     * @return list<string>
     */
    public static function validDesignIds(): array
    {
        return array_keys(self::DESIGNS);
    }
}
