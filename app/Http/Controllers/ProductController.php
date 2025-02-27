<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\{
    Product,
    ProductImage,
    User
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Maatwebsite\Excel\Readers\LaravelExcelReader;
use Spatie\Permission\Models\Role;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('addBy', 'images')
            ->withoutTrashed();

        if ($request->name) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        if ($request->status !== null) {
            if ($request->status == 0){
                $query->where('quantity',0);
            } else {
                $query->where('quantity', '<>', 0);
            }
        }
        if ($request->from) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->to) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $products = $query->paginate(10);

        return view("product", compact("products"));
    }
    public function add(ProductRequest $request)
    {
        try{
            DB::beginTransaction();
            $product = new Product();
            $product->name = $request->name;
            $product->quantity = $request->quantity;
            $product->added_by = auth()->user()->id;
            $product->code = generateProductCode();
            $product->save();
            if ($request->hasFile('images')) {
                $imagePaths = [];
                foreach ($request->file('images') as $image) {
                    $path = $image->store('products', 'public');
                    $imagePaths[] = [
                        'product_id' => $product->id,
                        'image' => $path,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                ProductImage::insert($imagePaths);
            }
            DB::commit();
            return response()->json(['success' => 'Product added successfully.'], 200);
        }catch (\Throwable $th){
            DB::rollBack();
            return response()->json(['error' => 'Something went wrong!' ], 500);
        }
        
    }
    public function load($id)
    {
        try{
            $status = $this->checkAccess($id, 'single');
            if(!$status){
                 return response()->json(['error'=> 'Editing of restricted products is not permitted'],403);
            }
            $product = Product::with('images')->find($id);
            return response()->json($product);
        }catch (\Throwable $th){
            dd($th);
        }
    }
    public function edit(ProductRequest $request)
    {
        try{
            $status = $this->checkAccess($request->id, 'single');
            if(!$status){
                 return response()->json(['error'=> 'Editing of restricted products is not permitted'],403);
            }
            DB::beginTransaction();
            $product = Product::find($request->id);
            $product->name = $request->name;
            $product->quantity = $request->quantity;
            $product->save();
            if ($request->hasFile('images')) {
                $imagePaths = [];
                foreach ($request->file('images') as $image) {
                    $path = $image->store('products', 'public');
                    $imagePaths[] = [
                        'product_id' => $product->id,
                        'image' => $path,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                ProductImage::insert($imagePaths);
            }
            DB::commit();
            return response()->json(['success' => 'Product updated successfully.'], 200);
        }catch (\Throwable $th){
            DB::rollBack();
            dd($th);
            return response()->json(['error' => 'Something went wrong!' ], 500);
        }
        
    }
    public function deleteImage($id)
    {
        try {
            $status = $this->checkAccess($id, 'single');
            if(!$status){
                 return response()->json(['error'=> 'Editing of restricted products is not permitted'],403);
            }
            $image = ProductImage::find($id);
            if ($image) {
                Storage::disk('public')->delete($image->image);
                $image->delete();

                return response()->json(['success' => 'Image deleted successfully.'], 200);
            } else {
                return response()->json(['error' => 'Image not found.'], 404);
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Something went wrong!'], 500);
        }
    }
    public function deleteProduct($id)
    {
        try {
            $status = $this->checkAccess($id, 'single');
            if(!$status){
                 return response()->json(['error'=> 'Deletion of restricted products is not allowed.'],403);
            }
            $product = Product::find($id);
            if ($product) {
                $product->delete();
                return response()->json(['success' => 'Product deleted successfully.'], 200);
            } else {
                return response()->json(['error' => 'Product not found.'], 404);
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Something went wrong!'], 500);
        }
    }

    public function bulkAdd(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xlsx,xls|max:2048'
        ]);
        try {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $filePath = $file->getPathname();
 
            if ($extension == 'xlsx') {
                $reader = ReaderEntityFactory::createXLSXReader();
            } elseif ($extension == 'csv') {
                $reader = ReaderEntityFactory::createCSVReader();
            }
            $reader->open($filePath);
            
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                    $cells = $row->toArray();

                    if ($rowIndex === 1) continue;
                    if($cells[0] == '') break;
                    DB::beginTransaction();
                    Product::create([
                        'name' => $cells[0],  
                        'quantity' => $cells[1] ?? 0,
                        'added_by' => auth()->user()->id,
                        'code' => generateProductCode(),
                    ]);
                    DB::commit();
                }
                break;
            }
            $reader->close();

            return response()->json(['success' => "Products imported successfully. Last Added Product: $cells[0]"], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['error' => 'Error importing data: ' . $th->getMessage()], 500);
        }
    }

    public function bulkDelete(Request $request)
    {
        try {
           $status = $this->checkAccess($request->ids, 'multiple');
           if(!$status){
                return response()->json(['error'=> 'Deletion of restricted products is not allowed.'],403);
           }
           Product::whereIn('id', $request->ids)->delete();
           return response()->json(['success' => 'Deleted Successfully.'], 200);

        } catch (\Throwable $th) {
            return response()->json(['error' => 'Somthing went wrong... '], 500);
        }
    }

    protected function checkAccess($productId, $type='single')
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) return true;

        if($type == 'single'){
            $added = Product::find( $productId )->added_by;
            if( $user->id == $added) return true;
        }elseif($type == 'multiple'){
            $adminUserIds = User::role('admin')->pluck('id')->toArray();
            $count = Product::whereIn('id', $productId)
                ->whereIn('added_by', $adminUserIds)->count;
            if($count == 0) return true; 
        }
        return false;
    }

    
}
