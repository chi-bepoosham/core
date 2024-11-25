<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExportSamplesImagesToJson extends Command
{
    protected $signature = 'export:samples_images';
    protected $description = 'Export image names and sub folder names to a JSON file';

    public function handle()
    {
        // Define the base directories
        $baseDirs = ['celebrity', 'clothes'];
        $basePath = public_path(); // Adjust if needed

        $celebrityData = [];
        $clothesData = [];

        foreach ($baseDirs as $dir) {
            $fullDirPath = $basePath . '/' . $dir;

            if (File::exists($fullDirPath)) {
                // Get subdirectories
                $subDirs = File::directories($fullDirPath);

                foreach ($subDirs as $subDir) {
                    $subDirName = basename($subDir);

                    // Use sub folder name as `body_type_id` (if numeric)
                    $bodyTypeId = is_numeric($subDirName) ? (int)$subDirName : null;

                    // Get image files in the subdirectory
                    $images = File::files($subDir);

                    foreach ($images as $image) {

                        $originalName = $image->getFilename();
                        $newName = str_replace(' ', '_', $originalName);

                        // Rename the file if needed
                        if ($originalName !== $newName) {
                            $originalPath = $image->getPathname();
                            $newPath = $image->getPath() . '/' . $newName;
                            File::move($originalPath, $newPath);
                            File::delete($originalPath);
                        }

                        $relativePath = '/' . $dir . '/' . $subDirName . '/' . $newName;
                        $title = str_replace("_", " ", pathinfo($image->getFilename(), PATHINFO_FILENAME));

                        if ($dir === 'celebrity') {
                            $celebrityData[] = [
                                'title' => $title,
                                'body_type_id' => trim($bodyTypeId),
                                'image' => $relativePath,
                            ];
                        } elseif ($dir === 'clothes') {
                            $clothesData[] = [
                                'body_type_id' => trim($bodyTypeId),
                                'image' => $relativePath,
                            ];
                        }
                    }
                }
            } else {
                $this->warn("Directory not found: $fullDirPath");
            }
        }


        $celebritySql = [];
        $clothesSql = [];

        foreach ($celebrityData as $item) {
            $celebritySql[] = sprintf(
                "INSERT INTO celebrity_body_types (title, body_type_id, image, created_at, updated_at) VALUES ('%s', %s, '%s', NOW(), NOW());",
                addslashes($item['title']),
                $item['body_type_id'] !== null ? $item['body_type_id'] : 'NULL',
                addslashes($item['image'])
            );
        }

        foreach ($clothesData as $item) {
            $clothesSql[] = sprintf(
                "INSERT INTO clothes_body_types (body_type_id, image) VALUES (%s, '%s');",
                $item['body_type_id'] !== null ? $item['body_type_id'] : 'NULL',
                addslashes($item['image'])
            );
        }

        $outputSql = array_merge($celebritySql, $clothesSql);

        $sqlPath = storage_path('body_type_images.sql');
        File::put($sqlPath, implode("\n", $outputSql));

        $this->info("SQL file has been generated: $sqlPath");

    }
}
