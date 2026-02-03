<?php

namespace fenomeno\NepheliaWorldGuard\Regions;

use fenomeno\NepheliaWorldGuard\Main;
use fenomeno\NepheliaWorldGuard\Utils\PositionParser;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use Throwable;

class RegionRepository
{

    private const FILE_NAME        = "regions.json";
    private const BACKUP_EXTENSION = ".backup";
    private const MAX_BACKUPS      = 5;

    private string $filePath;
    private Config $config;
    private string $backupDir;

    private array $rawData;
    private bool $dirty    = false;

    public function __construct(private readonly Main $main)
    {
        $dataFolder      = $this->main->getDataFolder();
        $this->filePath  = $dataFolder . DIRECTORY_SEPARATOR . self::FILE_NAME;
        $this->backupDir = $dataFolder . DIRECTORY_SEPARATOR . "backups";
    }

    public function init(): void
    {
        $dataFolder      = $this->main->getDataFolder();

        if(! is_dir($dataFolder)) {
            mkdir($dataFolder, 0755, true);
        }

        if (! is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }

        $this->config  = new Config($this->filePath, Config::JSON);
        $this->rawData = $this->config->getAll();
    }

    /** @return Region[] */
    public function load(): array
    {
        $regions = [];

        try {
            $data = $this->config->getAll();
            foreach ($data as $name => $regionData){
                if(! $this->validateRegionData($regionData)){
                    continue;
                }

                try {
                    $region = $this->hydrateRegion($name, $regionData);
                    $regions[$name] = $region;
                } catch (Throwable $e){
                    $this->main->getLogger()->error("Erreur lors de l'hydratation de la region " . $name . ": " . $e->getMessage());
                }
            }

            $this->main->getLogger()->info(TextFormat::GREEN . "Chargement de " . count($regions) . " regions.");

            $this->rawData = $data;
            $this->dirty   = false;

            return $regions;
        } catch (Throwable $e){
            $this->main->getLogger()->error("Erreur lors du chargement des regions: " . $e->getMessage());

            return [];
        }
    }

    private function validateRegionData(array $data): bool
    {
        $required = ['pos1', 'pos2'];
        foreach ($required as $field){
            if(! isset($data[$field])){
                $this->main->getLogger()->error("La region est invalide, champ manquant: " . $field);
                return false;
            }
        }

        try {
            PositionParser::load($data['pos1']);
            PositionParser::load($data['pos2']);

            return true;
        } catch (Throwable $e){
            $this->main->getLogger()->error("La position de la region est invalide: " . $e->getMessage());
            return false;
        }
    }

    private function hydrateRegion(int|string $name, array $regionData): Region
    {
        return new Region(
            name: $name,
            pos1: PositionParser::load($regionData['pos1']),
            pos2: PositionParser::load($regionData['pos2']),
            extended: (bool) ($regionData['extended'] ?? false),
            global: (bool) ($regionData['global'] ?? false),
            flags: $regionData['flags'] ?? [],
            parent: $regionData['parent'] ?? null,
        );
    }

    public function save(Region $region): bool
    {
        $this->rawData[$region->name] = $this->dehydrateRegion($region);
        $this->dirty                  = true;

        $this->config->set($region->name, $this->rawData[$region->name]);
        return $this->saveToFile();
    }

    public function delete(string $name): bool
    {
        if (isset($this->rawData[$name])){
            unset($this->rawData[$name]);
            $this->dirty = true;
        }

        $this->config->remove($name);
        return $this->saveToFile();
    }

    public function flush(): bool
    {
        if (! $this->dirty){
            return true;
        }

        return $this->saveAll($this->main->getRegionsManager()->getAllRegions());
    }

    /**
     * @param Region[] $regions
     * @return bool
     */
    public function saveAll(array $regions): bool
    {
        $data = [];
        foreach ($regions as $region){
            $data[$region->name] = $this->dehydrateRegion($region);
        }

        $this->rawData = $data;
        $this->config->setAll($data);

        return $this->saveToFile();
    }

    private function dehydrateRegion(Region $region): array
    {
        $data = [
            'pos1'     => PositionParser::toArray($region->pos1),
            'pos2'     => PositionParser::toArray($region->pos2),
            'flags'    => $region->flags,
            'extended' => $region->extended,
            'global'   => $region->global,
        ];

        if ($region->parent !== null) {
            $data['parent'] = $region->parent;
        }

        return $data;
    }

    private function saveToFile(): bool
    {
        try {
            $this->createBackup();

            $this->config->save();
            $this->dirty = false;

            return true;
        } catch (Throwable $e){
            $this->main->getLogger()->error("Erreur lors de la sauvegarde des regions: " . $e->getMessage());
            return false;
        }
    }

    private function createBackup(): void
    {
        try {
            $timestamp  = date('Y-m-d_H-i-s');
            $backupFile = $this->backupDir . DIRECTORY_SEPARATOR . "regions_" . $timestamp . self::BACKUP_EXTENSION;

            if(! copy($this->filePath, $backupFile)){
                $this->main->getLogger()->error("Échec de la création de la sauvegarde des regions.");
                return;
            }

            $backups = glob($this->backupDir . DIRECTORY_SEPARATOR . "regions_*" . self::BACKUP_EXTENSION);

            if($backups === false || count($backups) <= self::MAX_BACKUPS){
                return;
            }

            usort($backups, fn($a, $b) => filemtime($a) <=> filemtime($b));
            $toDelete = array_slice($backups, 0, count($backups) - self::MAX_BACKUPS);

            foreach ($toDelete as $file){
                unlink($file);
            }
        } catch (Throwable $e){
            $this->main->getLogger()->error("Erreur lors de la création de la sauvegarde des regions: " . $e->getMessage());
        }
    }

}