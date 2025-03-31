<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductsCategory;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ProductsCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Donor raw hair', 'image' => 'productCategoryImages/donor-raw-hair.jpeg'],
            ['name' => 'Virgin hairs', 'image' => 'productCategoryImages/virgin-hair.jpeg'],
            ['name' => 'Hair installation', 'image' => 'productCategoryImages/hair-installation.jpeg'],
            ['name' => 'Lash extensions', 'image' => 'productCategoryImages/lash-extension.jpeg'],
        ];

        foreach ($categories as $category) {
            // Convert category name to folder-friendly format
            $categoryFolder = strtolower(str_replace(' ', '-', $category['name']));

            // Build the full Cloudinary folder path
            $folderPath = env('FOLDER_FOR_IMAGES_IN_CLOUDINARY') . "/productCategory/{$categoryFolder}";

            // Get the correct full image path
            $imagePath = public_path('images/' . $category['image']);

            // Ensure the file exists
            if (!file_exists($imagePath)) {
                throw new \Exception("File not found: " . $imagePath);
            }

            // Check if the category already exists
            $existingCategory = ProductsCategory::where('name', $category['name'])->first();

            if ($existingCategory) {
                // Extract public_id from Cloudinary URL before deleting
                $publicId = pathinfo($existingCategory->image, PATHINFO_FILENAME);

                try {
                    // Delete the existing image from Cloudinary
                    Cloudinary::destroy("productCategory/{$categoryFolder}/{$publicId}");
                } catch (\Exception $e) {
                    echo "Error deleting image: " . $e->getMessage() . "\n";
                }
            }

            // Convert to a file object
            $file = new \Illuminate\Http\File($imagePath);

            // Upload to Cloudinary
            $uploadResult = Cloudinary::upload($file->getRealPath(), [
                'folder' => $folderPath,
            ]);

            $imageUrl = $uploadResult->getSecurePath();

            // Update or create the record in the database
            ProductsCategory::updateOrCreate(
                ['name' => $category['name']],
                ['image' => $imageUrl]
            );
        }
    }
}
