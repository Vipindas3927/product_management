<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Product([
            'name'     => $row['name'],
            'quantity' => $row['quantity'],
            'added_by' => auth()->id(),
            'code'     => $this->generateProductCode(),
        ]);
    }

    private function generateProductCode()
    {
        // Implement your product code generation logic here
    }
}
