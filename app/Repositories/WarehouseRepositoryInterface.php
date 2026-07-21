<?php

namespace App\Repositories;

interface WarehouseRepositoryInterface
{
    /**
     * Get details for a single warehouse item code.
     */
    public function getItemDetails(string $itemCode): array;

    /**
     * Get details for multiple warehouse item codes.
     */
    public function getItemsDetails(array $itemCodes): array;

    /**
     * Search warehouse items by code or name.
     */
    public function searchItems(string $query): array;
}
