<?php

namespace Database\Seeders;

use App\Models\{
    Product,
    User,
};
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $code = [];
        $adminId = User::where('user_type', 'admin')
            ->select('id')
            ->first()
            ->id;
        while(count($code) < 6) {
            do {
                $newCode = generateProductCode();
            } while(in_array($newCode, $code));
            $code[] = $newCode;
        }

        $data = [
            [
                'name' => 'Product 1',
                'code' => $code[0],
                'quantity' => 10,
                'details' => 'Test Product',
                'added_by' => $adminId,
                'created_at'=> now(),
                'updated_at'=> now(),
            ],
            [
                'name' => 'Product 2',
                'code' => $code[1],
                'quantity' => 10,
                'details' => 'Test Product',
                'added_by' => $adminId,
                'created_at'=> now(),
                'updated_at'=> now(),
            ],
            [
                'name' => 'Product 3',
                'code' => $code[2],
                'quantity' => 10,
                'details' => 'Test Product',
                'added_by' => $adminId,
                'created_at'=> now(),
                'updated_at'=> now(),
            ],
            [
                'name' => 'Product 4',
                'code' => $code[3],
                'quantity' => 10,
                'details' => 'Test Product',
                'added_by' => $adminId,
                'created_at'=> now(),
                'updated_at'=> now(),
            ],
            [
                'name' => 'Product 5',
                'code' => $code[4],
                'quantity' => 10,
                'details' => 'Test Product',
                'added_by' => $adminId,
                'created_at'=> now(),
                'updated_at'=> now(),
            ]
        ];

        Product::insert($data);
    }
}
