<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateLangKeys extends Command
{
    /**
     * El nombre y la firma del comando.
     *
     * @var string
     */
    protected $signature = 'lang:generate {locale=es}';

    /**
     * La descripción del comando.
     *
     * @var string
     */
    protected $description = 'Genera un archivo de idioma con todas las claves encontradas en la aplicación';

    /**
     * Ejecuta el comando.
     *
     * @return int
     */
    public function handle()
    {
        $locale = $this->argument('locale');
        $directory = base_path('resources/views');
        $outputFile = resource_path("lang/llaves.json");

        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
        $translations = [];

        foreach ($files as $file) {
            if ($file->isFile() && in_array($file->getExtension(), ['php', 'blade.php'])) {
                $content = file_get_contents($file->getPathname());
                preg_match_all("/__\s*\(\s*['\"](.*?)['\"]\s*\)/", $content, $matches);
                foreach ($matches[1] as $text) {
                    $translations[$text] = $text;
                }
            }
        }

        ksort($translations);

        file_put_contents($outputFile, json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info("Archivo de idioma generado en: {$outputFile}");

        return Command::SUCCESS;
    }
}
