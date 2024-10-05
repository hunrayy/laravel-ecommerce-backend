<?php

// namespace App\Http\Controllers;
// use Illuminate\Support\Facades\Validator;
// // use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
// use Cloudinary\Cloudinary;

// use App\Models\Product; 

// use Illuminate\Http\Request;

// class ProductController extends Controller
// {
//     //

//     public function createProduct(Request $request){
//         // Validate request input
//         $validator = Validator::make($request->all(), [
//             'productName' => 'required|string|max:255',
//             'productPrice' => 'required|numeric',
//             'productImage' => 'image|mimes:jpeg,png,jpg,gif',
//         ]);
//         if ($validator->fails()) {
//             return response()->json([
//                 'message' => 'All fields are required.',
//                 'code' => 'error',
//                 'errors' => $validator->errors()
//             ]);
//         }

//         try{
//             // Handle file uploads
//             $uploadedProductImage = $this->uploadToCloudinary($request->file('productImage'));
//             $uploadedSubImage1 = $this->uploadToCloudinary($request->file('subImage1'));
//             $uploadedSubImage2 = $this->uploadToCloudinary($request->file('subImage2'));
//             $uploadedSubImage3 = $this->uploadToCloudinary($request->file('subImage3'));

//             // Create new product
//             Product::create([
//                 // 'id' => 
//                 'productName' => $request->input('productName'),
//                 'productPriceInNaira' => $request->input('productPrice'),
//                 'productImage' => $uploadedProductImage,
//                 'subImage1' => $uploadedSubImage1,
//                 'subImage2' => $uploadedSubImage2,
//                 'subImage3' => $uploadedSubImage3
//             ]);
//             return response()->json([
//                 'message' => 'Product created successfully.',
//                 'code' => 'success',
//             ]);
//         }catch(\Exception $e){
//             return response()->json([
//                 'message' => 'Error creating product.',
//                 'code' => 'error',
//                 'reason' => $e->getMessage()
//             ]);
//         }
//     }
//     /**
//      * Upload file to Cloudinary
//      *
//      * @param \Illuminate\Http\UploadedFile $file
//      * @return string $url - The uploaded image URL
//      */
//     // public function uploadToCloudinary($file){
//     //     if (!$file) {
//     //         return null; // If no file is uploaded, return null
//     //     }

//     //     $uploadedFileUrl = Cloudinary::upload($file->getRealPath(), [
//     //         'folder' => env('FOLDER_FOR_IMAGES_IN_CLOUDINARY'), // Optional: Set a folder name in Cloudinary for organizing images
//     //         'resource_type' => 'image'
//     //     ])->getSecurePath(); // Fetch the secure URL of the uploaded image

//     //     return $uploadedFileUrl;
//     // }




//     public function uploadToCloudinary($file)
//     {
    
//         $cloudinary = new Cloudinary();
    
//         // Upload the image
//         $uploadedImage = $cloudinary->upload($file->getRealPath(), [
//             'folder' => env('FOLDER_FOR_IMAGES_IN_CLOUDINARY'),
//             'resource_type' => 'image',
//         ]);
    
//         // Get the secure URL of the uploaded image
//         $imageUrl = $uploadedImage->getSecurePath();
    
//         // You can now save $imageUrl to your database or return it in your response
//         return $image;
//     }
// }












































namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Cache;

use App\Models\Product; 

use Illuminate\Http\Request;

class ProductController extends Controller
{
    //
    public function createProduct(Request $request){
        // Validate request input
        $validator = Validator::make($request->all(), [
            'productName' => 'required|string|max:255',
            'productPrice' => 'required|numeric',
            'productImage' => 'image|mimes:jpeg,png,jpg,gif',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'All fields are required.',
                'code' => 'error',
                'errors' => $validator->errors()
            ]);
        }

        try{
            // Handle file uploads
            $uploadedProductImage = $this->uploadToCloudinary($request->file('productImage'));
            $uploadedSubImage1 = $this->uploadToCloudinary($request->file('subImage1'));
            $uploadedSubImage2 = $this->uploadToCloudinary($request->file('subImage2'));
            $uploadedSubImage3 = $this->uploadToCloudinary($request->file('subImage3'));

            // Create new product
            Product::create([
                'productName' => $request->input('productName'),
                'productPriceInNaira' => $request->input('productPrice'),
                'productImage' => $uploadedProductImage,
                'subImage1' => $uploadedSubImage1,
                'subImage2' => $uploadedSubImage2,
                'subImage3' => $uploadedSubImage3
            ]);

            //update the cache to hold the current data
            $allProducts = Product::all();
            Cache::put('allProducts', $allProducts);
            
            return response()->json([
                'message' => 'Product created successfully.',
                'code' => 'success',
            ]);
        }catch(\Exception $e){
            Log::error('Error occurred: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error creating product.',
                'code' => 'error',
                'reason' => $e->getMessage()
            ]);
        }
    }
    /**
     * Upload file to Cloudinary
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return string $url - The uploaded image URL
     */




    public function uploadToCloudinary($file){
        if(!$file){
            return null;
        }
    
        try {

            // Specify the folder you want the file to be uploaded to
            $folderName = env('FOLDER_FOR_IMAGES_IN_CLOUDINARY');

            // Upload the file to Cloudinary, specify the folder, and get the secure URL
            $uploadedFileUrl = Cloudinary::upload($file->getRealPath(), [
                'folder' => $folderName,
            ])->getSecurePath();

            return $uploadedFileUrl;

        }catch (\Exception $e) {
            // Handle the exception
            return response()->json([
                'message' => 'Error creating product.',
                'code' => 'error',
                'reason' => $e->getMessage()
            ]);
        }
    }
    
    public function getAllProducts(Request $request){
        //check if products are in cache
        $cachedProducts = Cache::get('allProducts');

        //if so, return cached products
        if($cachedProducts){
            return response()->json([
                "code" => "success",
                "message" => "All products successfully retrieved from cache",
                "data" => $cachedProducts
            ]);
        }

        //else, query the database for products, then save the products to the cache
        $allProducts = Product::all();
        Cache::put('allProducts', $allProducts);
        
        return response()->json([
            "code" => "success",
            "message" => "All products successfully retrieved from  database",
            "data" => $allProducts
        ]);
        
    }


    public function updateProduct(Request $request){
        try{
            $productId = $request->query('productId');
            $product = Product::where('id', $productId)->first();
            
            //if newProductImage, newSubImage1, newSubImage2, or newSubImage3 exists, there is an intention to update the image
    
            // Process Product Image
            if ($request->hasFile('productImage')) {
                // Check if there is an existing product image
                if ($product->productImage) {
                    // Delete old product image from Cloudinary
                    $oldProductImagePublicId = $this->getPublicIdFromUrl($product->productImage);
                    if ($oldProductImagePublicId) {
                        Cloudinary::destroy($oldProductImagePublicId);
                    }
                }
    
                // Upload new product image to Cloudinary
                $newProductImage = $this->uploadToCloudinary($request->file('productImage'));
    
                // Update the product image in the database
                $product->productImage = $newProductImage;
            }
    
            // Process Sub Image 1
            if ($request->hasFile('subImage1')) {
                if ($product->subImage1) {
                    $oldSubImage1PublicId = $this->getPublicIdFromUrl($product->subImage1);
                    if ($oldSubImage1PublicId) {
                        Cloudinary::destroy($oldSubImage1PublicId);
                    }
                }
    
                $newSubImage1 = $this->uploadToCloudinary($request->file('subImage1'));
                $product->subImage1 = $newSubImage1;
            }
    
            // Process Sub Image 2
            if ($request->hasFile('subImage2')) {
                if ($product->subImage2) {
                    $oldSubImage1PublicId = $this->getPublicIdFromUrl($product->subImage2);
                    if ($oldSubImage2PublicId) {
                        Cloudinary::destroy($oldSubImage2PublicId);
                    }
                }
    
                $newSubImage2 = $this->uploadToCloudinary($request->file('subImage2'));
                $product->subImage2 = $newSubImage2;
            }
    
            // Process Sub Image 3
            if ($request->hasFile('subImage3')) {
                if ($product->subImage3) {
                    $oldSubImage3PublicId = $this->getPublicIdFromUrl($product->subImage3);
                    if ($oldSubImage3PublicId) {
                        Cloudinary::destroy($oldSubImage3PublicId);
                    }
                }
    
                $newSubImage3 = $this->uploadToCloudinary($request->file('subImage3'));
                $product->subImage3 = $newSubImage3;
            }
    
            //process the product price
            if($request->has('productName')){
                // Update the name in the database
                $product->productName = $request->input('productName');
            }
    
            //process the product price
            if($request->has('productPriceInNaira')){
                // Update the price in the database
                $product->productPriceInNaira = $request->input('productPriceInNaira');
            }
    
            // Save the updated product in the database
            $product->save();

            //update the cache to hold the current data
            $allProducts = Product::all();
            Cache::put('allProducts', $allProducts);
            
    
            return response()->json([
                "code" => "success",
                "message" => "Product updated successfully",
            ]);
        }catch(Exception $e){
            return response()->json([
                "code" => "error",
                "message" => "An error occured while updating product",
                "reason" => $e->getMessage()
            ]);
        }

        
    }

    function getPublicIdFromUrl($secureUrl){
        // First, remove the base URL (domain, resource type, etc.)
        $urlParts = parse_url($secureUrl);
        
        // Extract the path from the URL
        $path = $urlParts['path'];

        // Remove '/image/upload/' from the path
        $pathWithoutBase = str_replace('/image/upload/', '', $path);

        // Split the path into version and public_id with format (e.g., 'v1312461204/sample.jpg')
        $pathParts = explode('/', $pathWithoutBase);

        // Remove the version part (e.g., 'v1312461204')
        array_shift($pathParts);

        // Get the public_id with the file extension (e.g., 'sample.jpg')
        $publicIdWithExtension = implode('/', $pathParts);

        // Remove the file extension (e.g., '.jpg')
        $publicId = pathinfo($publicIdWithExtension, PATHINFO_FILENAME);

        return $publicId;
    }

    public function deleteProduct(Request $request){
        try{
            //extract the id
            $productId = $request->input('productToDelete.id');

            //fetch the product in the database using the id gotten
            $productToDelete = Product::find($productId)->first();

            //delete the product image
            $productImage = $productToDelete->productImage;
            if($productImage){
                //extract the public id and use it to delete the product in cloudinary
                $publicId = $this->getPublicIdFromUrl($productImage);
                //public id extracted...next, delete product image in cloudinary
                Cloudinary::destroy($publicId);
            }

            //delete the product sub image 1
            $subImage1 = $productToDelete->subImage1;
            if($subImage1){
                //extract the public id and use it to delete the product in cloudinary
                $publicId = $this->getPublicIdFromUrl($subImage1);
                //public id extracted...next, delete product image in cloudinary
                Cloudinary::destroy($publicId);
            }

            //delete the product sub image 2
            $subImage2 = $productToDelete->subImage2;
            if($subImage2){
                //extract the public id and use it to delete the product in cloudinary
                $publicId = $this->getPublicIdFromUrl($subImage2);
                //public id extracted...next, delete product image in cloudinary
                Cloudinary::destroy($publicId);
            }

            //delete the product sub image 3
            $subImage3 = $productToDelete->subImage3;
            if($subImage3){
                //extract the public id and use it to delete the product in cloudinary
                $publicId = $this->getPublicIdFromUrl($subImage3);
                //public id extracted...next, delete product image in cloudinary
                Cloudinary::destroy($publicId);
            }

            $productToDelete->delete();
            
            //delete successful, fetch all products and save to cache
            $newProducts = Product::all();
            Cache::put("allProducts", $newProducts);

            return response()->json([
                "code" => "success",
                "message" => "Product deleted successfully",
            ]);
        }catch(\Exception $e){
            return response()->json([
                "code" => "error",
                "message" => "An error occured while deleting product",
                "reason" => $e->getMessage()
            ]);
        }
    }
}
