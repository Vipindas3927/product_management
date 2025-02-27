<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ApiController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['status' => false, 'error' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['status' => true, 'token' => $token, 'token_type' => 'Bearer']);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['status' => true, 'message' => 'Logged out successfully']);
    }

    public function getProductApi()
    {
        $products = Product::with('addBy', 'images')
            ->withoutTrashed()
            ->get()
            ->map(function ($product) {
                return [
                    'name' => $product->name,
                    'code' => $product->code,
                    'quantity' => $product->quantity,
                    'added_by' => $product->addBy->name ,
                    'images' => $product->images->map(function ($image) {
                        return asset('storage/' . $image->image);
                    })
                ];
            });

        return response()->json([
            'status' => true,
            'products' => $products
        ], 200);
    }
}
