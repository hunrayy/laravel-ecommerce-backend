<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Exception;
use App\Models\ProductsCategory; 


class ProductCategoryController extends Controller
{
    //
    public function createCategory(Request $request){
        try {
            $request->validate([
                'newCategory' => 'required|string'
            ]);

            // Create category only if it doesn't exist
            $createCategory = ProductsCategory::firstOrCreate(['name' => $request->newCategory]);

            // Retrieve all categories and save to cache
            $allCategories = ProductsCategory::all()->toArray();
            Cache::put('productCategories', $allCategories, now()->addWeek()); // Cache for a week

            return response()->json([
                'code' => $createCategory->wasRecentlyCreated ? 'success' : 'error',
                'message' => $createCategory->wasRecentlyCreated ? "Product Category created successfully" : "Product Category already exists",
                'data' => $createCategory->wasRecentlyCreated ? $createCategory : null
            ]);
        } catch (Exception $e) {
            return response()->json([
                'code' => 'error',
                'message' => "An error occurred while creating new product category: " . $e->getMessage()
            ]);
        }
    }


    public function editCategory(Request $request) {
        try {
            $request->validate([
                'oldCategory' => 'required|string',  // The existing category name
                'newCategory' => 'required|string'   // The new category name to update to
            ]);
    
            // Find the category by name
            $category = ProductsCategory::where('name', $request->oldCategory)->first();
    
            // Check if category exists
            if (!$category) {
                return response()->json([
                    'code' => 'error',
                    'message' => "Product Category not found"
                ]);
            }
    
            // Check if the new name already exists
            $existingCategory = ProductsCategory::where('name', $request->newCategory)->first();
            if ($existingCategory) {
                return response()->json([
                    'code' => 'error',
                    'message' => "A category with this name already exists"
                ]);
            }
    
            // Update the category name
            $category->update(['name' => $request->newCategory]);
    
            // Refresh cache by retrieving all categories again
            $allCategories = ProductsCategory::all()->toArray();
            Cache::put('productCategories', $allCategories, now()->addWeek()); // Cache for 7 days
    
            return response()->json([
                'code' => 'success',
                'message' => "Product Category updated successfully",
                'data' => $category
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'code' => 'error',
                'message' => "An error occurred while updating the product category: " . $e->getMessage()
            ]);
        }
    }

    public function deleteCategory(Request $request) {
        try {
            $request->validate([
                'category' => 'required|string'  // Expecting category name
            ]);
    
            // Find the category by name
            $category = ProductsCategory::where('name', $request->category)->first();
    
            // Check if category exists
            if (!$category) {
                return response()->json([
                    'code' => 'error',
                    'message' => "Product Category not found"
                ]);
            }
    
            // Delete the category
            $category->delete();
    
            // Refresh cache after deleting
            $allCategories = ProductsCategory::all()->toArray();
            Cache::put('productCategories', $allCategories, now()->addWeek()); // Cache for 7 days
    
            return response()->json([
                'code' => 'success',
                'message' => "Product Category deleted successfully"
            ]);
    
        } catch (Exception $e) {
            return response()->json([
                'code' => 'error',
                'message' => "An error occurred while deleting the product category: " . $e->getMessage()
            ]);
        }
    }
    
    
    
}
