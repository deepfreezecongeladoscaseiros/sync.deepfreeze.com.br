<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ImportLegacyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:legacy-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data from the legacy Deep Freeze SQL dump files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting legacy data import...');

        $this->importBrands();
        // Futuramente, chamaremos outros métodos aqui: 
        // $this->importCategories();
        // $this->importProducts();

        $this->info('Legacy data import finished successfully!');
        return 0;
    }

    private function importBrands()
    {
        $this->info('Importing brands...');
        $path = database_path('../Dump_structure/novo_marcas.sql');
        $fileContent = file_get_contents($path);

        // Encontra a linha com INSERT INTO
        if (!preg_match('/INSERT INTO `marcas` VALUES (.*);/s', $fileContent, $matches)) {
            $this->error('No INSERT statement found for brands.');
            return;
        }

        // Limpa e divide os valores
        $values = trim($matches[1]);
        $rows = explode('),(', trim($values, '()'));

        foreach ($rows as $row) {
            $data = str_getcsv($row, ',', "'");

            // Mapeamento conforme o dump: id, marca_ativa, nome_marca, ...
            $id = $data[0];
            $name = $data[2]; // nome_marca

            if (empty($name)) continue;

            \App\Models\Brand::updateOrCreate(
                ['id' => $id], // Usa o ID legado para evitar duplicatas
                [
                    'brand' => $name,
                    'slug' => \Illuminate\Support\Str::slug($name),
                ]
            );
        }

        $this->info('Brands imported.');
    }
}
