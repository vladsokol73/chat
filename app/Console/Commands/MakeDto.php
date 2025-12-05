<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeDto extends Command
{
    protected $signature = 'make:dto {name}';

    protected $description = 'Создать DTO с EntityActionDto, контрактами и автоконструктором';

    private Filesystem $files;

    public function __construct()
    {
        parent::__construct();
        $this->files = new Filesystem;
    }

    public function handle()
    {
        $nameInput = $this->argument('name');

        // Разбираем путь и имя класса
        $parts = explode('/', str_replace('\\', '/', $nameInput));
        $baseName = array_pop($parts);

        // Преобразуем в формат EntityActionDto
        $className = $this->normalizeClassName($baseName);

        $directory = app_path('DTO/'.implode('/', $parts));
        if (! $this->files->exists($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }

        $filePath = "{$directory}/{$className}.php";
        if ($this->files->exists($filePath)) {
            $this->error("DTO {$className} уже существует в {$directory}");

            return;
        }

        $type = $this->detectType($className);
        $stub = $this->getStub($className, $type, $parts);

        $this->files->put($filePath, $stub);

        $this->info("DTO {$className} создан в {$directory}");
    }

    private function normalizeClassName(string $name): string
    {
        $name = Str::studly($name);
        foreach (['Create', 'Update', 'List'] as $action) {
            if (Str::startsWith($name, $action)) {
                $entity = substr($name, strlen($action));
                if ($entity !== '') {
                    return $entity.$action.'Dto';
                }
            }
        }

        return $name.'Dto';
    }

    private function detectType(string $className): string
    {
        if (Str::endsWith($className, 'CreateDto')) {
            return 'create';
        }
        if (Str::endsWith($className, 'UpdateDto')) {
            return 'update';
        }
        if (Str::endsWith($className, 'ListDto')) {
            return 'list';
        }

        return 'simple';
    }

    private function getStub(string $className, string $type, array $namespaceParts): string
    {
        $namespace = 'App\\DTO';
        if ($namespaceParts) {
            $namespace .= '\\'.implode('\\', $namespaceParts);
        }

        // Контракты
        $interfaces = match ($type) {
            'create', 'update' => [
                'App\Contracts\DTO\FromRequestInterface',
                'App\Contracts\DTO\ToArrayInterface',
            ],
            'list' => [
                'App\Contracts\DTO\FromCollectionInterface',
                'App\Contracts\DTO\ToArrayInterface',
            ],
            default => [
                'App\Contracts\DTO\FromArrayInterface',
                'App\Contracts\DTO\FromModelInterface',
                'App\Contracts\DTO\ToArrayInterface',
            ],
        };

        $attributes = [

        ];

        $interfaceUse = implode(";\nuse ", $interfaces).';';
        $attributeUse = implode(";\nuse ", $attributes).';';

        $interfaceNames = implode(', ', array_map(fn ($i) => class_basename($i), $interfaces));

        // Методы с автоконструктором
        $methods = $this->getMethodsWithConstructor($type);

        return <<<PHP
<?php

namespace {$namespace};

use {$interfaceUse}
use {$attributeUse}

class {$className} implements {$interfaceNames}
{
    public function __construct(
        // TODO: добавьте параметры конструктора
    ) {}

{$methods}
}
PHP;
    }

    private function getMethodsWithConstructor(string $type): string
    {
        return match ($type) {
            'create', 'update' => <<<PHP
    public static function fromRequest(\Illuminate\Http\Request \$request): static
    {
        return new self(
        // TODO: заполнить свойства из \$request->validated() или \$request->input()
        );
    }

    public function toArray(): array
    {
        return [
            // TODO: вернуть массив с ключами свойств
        ];
    }
PHP,
            'list' => <<<PHP
    public static function fromModel(\Illuminate\Database\Eloquent\Model \$model): static
    {
    //        if (!(\$model instanceof ModelName)) {
    //            throw new \InvalidArgumentException('Expected ApiToken type model');
    //        }

        return new self(
            // TODO: заполнить из модели
        );
    }

    public static function fromCollection(\Illuminate\Support\Collection \$collection): array
    {
        return \$collection->map(fn(\$item) => static::fromModel(\$item))->toArray();
    }

    public function toArray(): array
    {
        return [
            // TODO: вернуть массив одного объекта
        ];
    }
PHP,
            default => <<<PHP
    public static function fromArray(array \$data): static
    {
        return new self(
            // TODO: заполнить из списка
        );
    }

    public static function fromModel(\Illuminate\Database\Eloquent\Model \$model): static
    {
        //        if (!(\$model instanceof ModelName)) {
        //            throw new \InvalidArgumentException('Expected ApiToken type model');
        //        }

        return new self(
            // TODO: заполнить из модели
        );
    }

    public function toArray(): array
    {
        return [
            // TODO: вернуть массив
        ];
    }
PHP,
        };
    }
}
