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

use Illuminate\Support\Facades\Redis;

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
            $allProducts = Product::orderBy('created_at', 'desc')->get()->toArray(); //  paginate the results

            //save all products fetched to the cache
            Redis::set('allProducts', json_encode($allProducts, true));
    
            
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
        $page = (int)$request->query('page'); //fall back value of 1 if page isn't passed
        $perPage = (int)$request->query('perPage'); //fallback value of 2 if number of products per page isn't passed
        

        // Retrieve the products array from Redis
        $cachedProducts = Redis::get('allProducts');
        if($cachedProducts){
            // Decode the products array
            $products = json_decode($cachedProducts, true);

            // Calculate total number of products
            $totalProducts = count($products);

            // Calculate pagination offset
            $offset = ($page - 1) * $perPage;

            // Slice the array to get the products for the current page
            $paginatedProducts = array_slice($products, $offset, $perPage);

            return response()->json([
                'code' => 'success',
                'message' => 'products successfully fetched from cache',
                'data' => [
                    'data' => $paginatedProducts,
                    'total' => $totalProducts,
                    'current_page' => $page,
                    'per_page' => $perPage,
                ],
            ]);
        }
        //no product exists in the cache, query the database for products
        $allProducts = Product::orderBy('created_at', 'desc')->get()->toArray(); //  paginate the results

        //save all products fetched to the cache
        Redis::set('allProducts', json_encode($allProducts, true));

        // Calculate total number of products
        $totalProducts = count($allProducts);

        // Calculate pagination offset
        $offset = ($page - 1) * $perPage;

        // Slice the array to get the products for the current page
        $paginatedProducts = array_slice($allProducts, $offset, $perPage);

        return response()->json([
            'code' => 'success',
            'message' => 'products successfully fetched from database',
            'data' => [
                'data' => $paginatedProducts,
                'total' => $totalProducts,
                'current_page' => $page,
                'per_page' => $perPage,
            ],
        ]);




        // //check if products are in cache
        // $cachedProducts = Redis::get('allProducts');

        // //if so, return cached products
        // if($cachedProducts){
        //     return response()->json([
        //         "code" => "success",
        //         "message" => "All products successfully retrieved from cache",
        //         "data" => json_decode($cachedProducts, true)
        //     ]);
        // }

        // //else, query the database for products, then save the products to the cache
        // $allProducts = Product::all();
        // $arrayProducts = $allProducts->toArray();
        // Redis::set('allProducts', $allProducts);
        
        // return response()->json([
        //     "code" => "success",
        //     "message" => "All products successfully retrieved from  database",
        //     "data" => $allProducts
        // ]);
        
    }

    public function getSingleProduct(Request $request){
        $productId = $request->query('productId');

        //check if product exists in cache
        $cachedProduct = Redis::get("singleProduct_{$productId}");
        if($cachedProduct){
            //product exists in cache
            return response()->json([
                "code" => "success",
                "message" => "single product successfully retrieved from cache",
                "data" => json_decode($cachedProduct, true)
            ]);
        }
        // If not in cache, fetch from the database 
        try{
            $fetchedProduct = Product::where('id', $productId)->first();

            if($fetchedProduct){
                //save the product in the cache
                Redis::set("singleProduct_{$productId}", $fetchedProduct);

                return response()->json([
                    "code" => "success",
                    "message" => "single product successfully retrieved from database",
                    "data" => $fetchedProduct
                ]);
            }else{
                return response()->json([
                    "message" => "Product could not be retrieved",
                    "code" => "error"
                ]);
                
            }
        }catch(\Exception $e){
            return response()->json([
                "message" => "Product could not be retrieved",
                "code" => "error",
                "reason" => $e->getMessage()
            ]);
            
        }

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
            $allProducts = Product::orderBy('created_at', 'desc')->get()->toArray();
            Redis::set('allProducts', json_encode($allProducts, true));
            

    
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

    // function getPublicIdFromUrl($secureUrl){
    //     // First, remove the base URL (domain, resource type, etc.)
    //     $urlParts = parse_url($secureUrl);
        
    //     // Extract the path from the URL
    //     $path = $urlParts['path'];

    //     // Remove '/image/upload/' from the path
    //     $pathWithoutBase = str_replace('/image/upload/', '', $path);

    //     // Split the path into version and public_id with format (e.g., 'v1312461204/sample.jpg')
    //     $pathParts = explode('/', $pathWithoutBase);

    //     // Remove the version part (e.g., 'v1312461204')
    //     array_shift($pathParts);

    //     // Get the public_id with the file extension (e.g., 'sample.jpg')
    //     $publicIdWithExtension = implode('/', $pathParts);

    //     // Remove the file extension (e.g., '.jpg')
    //     $publicId = pathinfo($publicIdWithExtension, PATHINFO_FILENAME);

    //     return $publicId;
    // }


    public function getPublicIdFromUrl($secureUrl) {
        // Parse the URL
        $urlParts = parse_url($secureUrl);
        
        // Extract the path from the URL
        $path = $urlParts['path'];
        
        // Remove '/image/upload/' from the path
        $pathWithoutBase = str_replace('/image/upload/', '', $path);
        
        // Split the path into parts
        $pathParts = explode('/', $pathWithoutBase);
        
        // Remove the version part (first element)
        array_shift($pathParts); // Remove the version part
        
        // Remove the last part (the file name with extension)
        $fileNameWithExtension = array_pop($pathParts); // Get the file name
        $publicIdWithoutExtension = pathinfo($fileNameWithExtension, PATHINFO_FILENAME); // Get just the file name without extension
        
        // Return the public ID being preceeded by the public Id
        return env('FOLDER_FOR_IMAGES_IN_CLOUDINARY') . '/' . $publicIdWithoutExtension ;
    }

    
    


    public function deleteProduct(Request $request){
        try{
            //extract the id
            $productId = $request->input('productToDelete.id');

            //fetch the product in the database using the id gotten
            $productToDelete = Product::find($productId);

            //delete the product image
            $productImage = $productToDelete->productImage;
            if($productImage){
                //extract the public id and use it to delete the product in cloudinary
                $publicId = $this->getPublicIdFromUrl($productImage);
                //public id extracted...next, delete product image in cloudinary
                $response = Cloudinary::destroy($publicId);
            }

            //delete the product sub image 1
            $subImage1 = $productToDelete->subImage1;
            if($subImage1){
                //extract the public id and use it to delete the product in cloudinary
                $publicId = $this->getPublicIdFromUrl($subImage1);
                //public id extracted...next, delete product image in cloudinary
                Cloudinary::destroy($publicId, ['invalidate' => true]);
                
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

            // delete the single product from cache
            Redis::del("singleProduct_{$productId}");
            
            //delete successful, fetch all products and save to cache
            $newProducts = Product::orderBy('created_at', 'desc')->get()->toArray();
            Redis::set("allProducts", json_encode($newProducts, true));



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
    // $products = Product::where('productName', 'LIKE', '%' . $query . '%')->get();
    // return response()->json([
    //                 'code' => 'success',
    //                 'message' => 'Products successfully retrieved',
    //                 'data' => $products
    //             ]);

    public function searchProducts(Request $request){
        $query = $request->query('query');
    
        // Get products from cache if available
        $cachedProducts = Cache::get('allProducts');
    
        if ($cachedProducts) {
            // Filter cached products based on the query
            $filteredProducts = $this->filterProducts($cachedProducts, $query);
            
            return response()->json([
                'message' => 'Products successfully retrieved from cache',
                'code' => 'success',
                'data' => $filteredProducts
            ]);
        } else {
            // Query the database if products are not found in cache
            $products = Product::where('productName', 'LIKE', '%' . $query . '%')->get();
    
            // Cache all products for future requests
            Cache::put('allProducts', $products);
    
            return response()->json([
                'message' => 'Products successfully retrieved from database',
                'code' => 'success',
                'data' => $products
            ]);
        }
        // $query = $request->query('query');
        
        // // Assume $products contains the results of your search
        // $products = Product::where('productName', 'LIKE', '%' . $query . '%')->get();

        // return response()->json([
        //     'message' => 'Product successfully retrieved',
        //     'code' => 'success',
        //     'data' => $products
        // ]);


        
        // $queryWords = explode(' ', strtolower($query)); // Split query into words
        
        // Check cache first
        // $cachedProducts = Cache::get('products');

        // if ($cachedProducts) {
        //     // Filter products from the cache
        //     $filteredProducts = $this->filterProducts($cachedProducts, $queryWords);
        //     return response()->json([
        //         'message' => 'Product successfully retrieved',
        //         'code' => 'success',
        //         'data' => $filteredProducts
        //     ]);
        // } else {
        //     // Fetch products from the database if not in cache
        //     $responseData = $this->getAllProducts($request);

        //     // Decode the response data
        //     $response = $responseData->getData();
            // if ($response->code === 'success') {
            //     $cachedProducts = $response->data; // Extract data from the response
            //     $filteredProducts = $this->filterProducts($cachedProducts, $queryWords);
            //     return response()->json([
            //         'message' => 'Product successfully retrieved',
            //         'code' => 'success',
            //         'data' => $filteredProducts
            //     ]);
            // } else {
            //     // Handle the error if products could not be retrieved
            //     return response()->json([
            //         'code' => 'error',
            //         'message' => 'An error occurred while retrieving products',
            //         'reason' => $response->reason
            //     ]);
            // }
        // }
    }


    // private function filterProducts($products, $queryWords)
    // {
    //     return array_filter($products, function($product) use ($queryWords) {
    //         foreach ($queryWords as $word) {
    //             if (stripos($product['productName'], $word) !== false) {
    //                 return true;
    //             }
    //         }
    //         return false;
    //     });
    // }

    private function filterProducts($products, $query) {
        return $products->filter(function($product) use ($query) {
            return stripos($product['productName'], $query) !== false;
        })->toArray();
    }
}
