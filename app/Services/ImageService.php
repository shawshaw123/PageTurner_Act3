<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;
use Illuminate\Http\UploadedFile;

class ImageService
{
    private static $manager;
    
    private static function getManager()
    {
        if (!self::$manager) {
            try {
                // Try Imagick driver first (better quality)
                self::$manager = new ImageManager(new Driver());
            } catch (\Throwable $e) {
                // Fallback to GD driver
                try {
                    self::$manager = new ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
                } catch (\Throwable $e2) {
                    // Last resort - simple file handling without processing
                    self::$manager = null;
                }
            }
        }
        return self::$manager;
    }
    
    /**
     * Process and store book cover image
     */
    public static function processBookCover(UploadedFile $image): string
    {
        $manager = self::getManager();
        
        if (!$manager) {
            // Fallback: just store the original image
            return $image->store('covers', 'public');
        }
        
        try {
            // Create image instance
            $img = $manager->read($image->getPathname());
            
            // Resize to standard dimensions while maintaining aspect ratio
            $img->scaleDown(400, 600);
            
            // Generate unique filename
            $filename = 'book_' . time() . '_' . uniqid() . '.jpg';
            $path = 'covers/' . $filename;
            
            // Convert to JPEG and optimize
            $encoded = $img->toJpeg(85);
            
            // Save to storage
            Storage::disk('public')->put($path, (string)$encoded);
            
            return $path;
        } catch (\Exception $e) {
            // Fallback to original image storage
            return $image->store('covers', 'public');
        }
    }
    
    /**
     * Create thumbnail for book cover
     */
    public static function createThumbnail(string $imagePath): string
    {
        if (!Storage::disk('public')->exists($imagePath)) {
            return self::generatePlaceholder();
        }
        
        $manager = self::getManager();
        
        if (!$manager) {
            // Return original image if no processing available
            return $imagePath;
        }
        
        try {
            $imageContent = Storage::disk('public')->get($imagePath);
            $img = $manager->read($imageContent);
            
            // Create thumbnail
            $img->scaleDown(150, 225);
            
            $thumbnailPath = 'covers/thumbnails/' . basename($imagePath);
            $encoded = $img->toJpeg(75);
            
            Storage::disk('public')->put($thumbnailPath, (string)$encoded);
            
            return $thumbnailPath;
        } catch (\Exception $e) {
            return $imagePath;
        }
    }
    
    /**
     * Generate placeholder image
     */
    public static function generatePlaceholder(string $title = 'Book Cover'): string
    {
        $manager = self::getManager();
        
        if (!$manager) {
            // Create a simple placeholder file
            $filename = 'placeholder_' . time() . '.txt';
            $path = 'covers/placeholders/' . $filename;
            Storage::disk('public')->put($path, 'Placeholder for: ' . $title);
            return $path;
        }
        
        try {
            $img = $manager->create(400, 600, '#f3f4f6');
            
            // Add book icon or text
            $img->text('📚', 200, 250, function($font) {
                $font->size(120);
                $font->color('#9ca3af');
                $font->align('center');
                $font->valign('middle');
            });
            
            // Add title text (truncate if too long)
            $displayTitle = strlen($title) > 30 ? substr($title, 0, 27) . '...' : $title;
            $img->text($displayTitle, 200, 350, function($font) {
                $font->size(24);
                $font->color('#6b7280');
                $font->align('center');
                $font->valign('middle');
            });
            
            $filename = 'placeholder_' . time() . '.jpg';
            $path = 'covers/placeholders/' . $filename;
            
            Storage::disk('public')->put($path, (string)$img->toJpeg(85));
            
            return $path;
        } catch (\Exception $e) {
            // Fallback placeholder
            $filename = 'placeholder_' . time() . '.txt';
            $path = 'covers/placeholders/' . $filename;
            Storage::disk('public')->put($path, 'Placeholder for: ' . $title);
            return $path;
        }
    }
    
    /**
     * Get image URL or placeholder
     */
    public static function getImageUrl(?string $imagePath, string $title = 'Book Cover'): string
    {
        if ($imagePath && Storage::disk('public')->exists($imagePath)) {
            return asset('storage/' . $imagePath);
        }
        
        // Generate and return placeholder
        $placeholderPath = self::generatePlaceholder($title);
        return asset('storage/' . $placeholderPath);
    }
    
    /**
     * Get thumbnail URL
     */
    public static function getThumbnailUrl(?string $imagePath, string $title = 'Book Cover'): string
    {
        if ($imagePath && Storage::disk('public')->exists($imagePath)) {
            $thumbnailPath = 'covers/thumbnails/' . basename($imagePath);
            
            if (!Storage::disk('public')->exists($thumbnailPath)) {
                self::createThumbnail($imagePath);
            }
            
            if (Storage::disk('public')->exists($thumbnailPath)) {
                return asset('storage/' . $thumbnailPath);
            }
        }
        
        // Return main image URL as fallback
        return self::getImageUrl($imagePath, $title);
    }
    
    /**
     * Delete image and thumbnail
     */
    public static function deleteImage(?string $imagePath): void
    {
        if ($imagePath) {
            // Delete main image
            Storage::disk('public')->delete($imagePath);
            
            // Delete thumbnail
            $thumbnailPath = 'covers/thumbnails/' . basename($imagePath);
            Storage::disk('public')->delete($thumbnailPath);
        }
    }
}
