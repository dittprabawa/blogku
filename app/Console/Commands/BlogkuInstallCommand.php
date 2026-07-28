<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class BlogkuInstallCommand extends Command
{
    protected $signature = 'blogku:install';

    protected $description = 'Setup awal BlogKu: migrasi database, buat akun admin pertama, dan siapkan storage.';

    public function handle(): int
    {
        $this->info('Selamat datang di setup BlogKu 👋');
        $this->newLine();

        // --- 1. Nama aplikasi ---
        $appName = $this->ask('Nama aplikasi', config('app.name', 'BlogKu'));

        // --- 2. Data admin ---
        $adminName = $this->ask('Nama lengkap admin');

        $adminEmail = $this->ask('Email admin');
        while (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            $this->error('Email tidak valid.');
            $adminEmail = $this->ask('Email admin');
        }

        $adminPassword = $this->secret('Password admin (minimal 8 karakter)');
        $rules = Password::min(8);
        while (!Password::min(8)->passes('password', $adminPassword)) {
            $this->error('Password minimal 8 karakter.');
            $adminPassword = $this->secret('Password admin (minimal 8 karakter)');
        }

        // --- 3. Migrasi database ---
        $this->newLine();
        $this->info('Menjalankan migrasi database...');
        Artisan::call('migrate', ['--force' => true], $this->getOutput());

        // --- 4. Symlink storage (biar gambar upload bisa diakses publik) ---
        $this->info('Menyiapkan storage link...');
        Artisan::call('storage:link', [], $this->getOutput());

        // --- 5. Buat/update akun admin ---
        $this->info('Membuat akun admin...');
        User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => $adminName,
                'password' => Hash::make($adminPassword),
                'role' => 'admin',
            ]
        );

        // --- 6. Simpan APP_NAME & data admin ke .env, biar seeder di masa depan konsisten ---
        $this->updateEnv([
            'APP_NAME' => '"'.$appName.'"',
            'ADMIN_NAME' => '"'.$adminName.'"',
            'ADMIN_EMAIL' => $adminEmail,
            'ADMIN_PASSWORD' => $adminPassword,
        ]);
        Artisan::call('config:clear');

        // --- 7. Ringkasan akhir ---
        $this->newLine();
        $this->info('✅ Setup selesai!');
        $this->table(
            ['Item', 'Detail'],
            [
                ['Nama aplikasi', $appName],
                ['Email admin', $adminEmail],
                ['Login di', url('/login')],
                ['Dashboard', url('/admin/posts')],
                ['Blog publik', url('/blog')],
            ]
        );
        $this->comment('Silakan login pakai email & password admin yang baru saja kamu buat.');

        return self::SUCCESS;
    }

    /**
     * Update (atau tambahkan kalau belum ada) baris tertentu di file .env.
     *
     * @param  array<string, string>  $values
     */
    protected function updateEnv(array $values): void
    {
        $path = base_path('.env');

        if (!File::exists($path)) {
            return;
        }

        $content = File::get($path);

        foreach ($values as $key => $value) {
            $pattern = "/^{$key}=.*/m";
            $line = "{$key}={$value}";

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $line, $content);
            } else {
                $content .= PHP_EOL.$line;
            }
        }

        File::put($path, $content);
    }
}