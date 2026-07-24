<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MakeMyViewCommand extends Command
{
    /**
     * Contoh pemakaian:
     * php artisan make:myview welcome
     * php artisan make:myview products.index
     * php artisan make:myview dashboard --force
     */
    protected $signature = 'make:myview {name} {--force : Timpa file jika sudah ada}';

    protected $description = 'Buat file view Blade baru dengan boilerplate HTML custom';

    public function handle(): int
    {
        $name = str_replace('.', '/', $this->argument('name'));
        $path = resource_path("views/{$name}.blade.php");

        if (file_exists($path) && ! $this->option('force')) {
            $this->error("View sudah ada: {$path}");
            $this->line('Gunakan --force untuk menimpa.');
            return self::FAILURE;
        }

        $directory = dirname($path);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($path, $this->stub());

        $this->info("View berhasil dibuat: {$path}");

        return self::SUCCESS;
    }

    protected function stub(): string
    {
        return <<<'BLADE'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
</body>
</html>

BLADE;
    }
}