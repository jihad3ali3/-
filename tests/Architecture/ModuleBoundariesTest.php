<?php

namespace Tests\Architecture;

use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Fails the build when a module reaches into another module's internals.
 *
 * Rules:
 *  1. A module may reference another module only through its Contracts
 *     namespace (Modules\<Other>\Contracts\...).
 *  2. Contracts must not reference the internals of their own module.
 *
 * Pure static analysis: no application boot, no database.
 */
final class ModuleBoundariesTest extends TestCase
{
    private const REFERENCE = '~\bModules\\\\+(\w+)\\\\+(\w+)~';

    public function test_modules_only_reference_other_modules_through_contracts(): void
    {
        $violations = [];

        foreach ($this->moduleFiles() as $file) {
            foreach ($this->references($file['content']) as [$target, $segment]) {
                if ($target !== $file['module'] && $segment !== 'Contracts') {
                    $violations[] = "{$file['path']} references Modules\\{$target}\\{$segment}";
                }
            }
        }

        $this->assertSame([], $violations, "Cross-module references must go through Contracts:\n".implode("\n", $violations));
    }

    public function test_contracts_do_not_depend_on_their_own_internals(): void
    {
        $violations = [];

        foreach ($this->moduleFiles() as $file) {
            if (! $file['isContract']) {
                continue;
            }

            foreach ($this->references($file['content']) as [$target, $segment]) {
                if ($target === $file['module'] && $segment !== 'Contracts') {
                    $violations[] = "{$file['path']} references Modules\\{$target}\\{$segment}";
                }
            }
        }

        $this->assertSame([], $violations, "Contracts must stay free of implementation details:\n".implode("\n", $violations));
    }

    public function test_the_scanner_actually_sees_the_modules(): void
    {
        // Guards against a silently empty scan making every other test pass.
        $modules = array_unique(array_column($this->moduleFiles(), 'module'));
        sort($modules);

        $this->assertContains('Catalog', $modules);
        $this->assertContains('Orders', $modules);
    }

    /**
     * @return list<array{path: string, module: string, isContract: bool, content: string}>
     */
    private function moduleFiles(): array
    {
        $root = dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'Modules';

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        );

        $files = [];

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $relative = substr($file->getPathname(), strlen($root) + 1);
            $parts = explode(DIRECTORY_SEPARATOR, $relative);

            $files[] = [
                'path' => str_replace(DIRECTORY_SEPARATOR, '/', 'Modules/'.$relative),
                'module' => $parts[0],
                'isContract' => ($parts[1] ?? null) === 'Contracts',
                'content' => (string) file_get_contents($file->getPathname()),
            ];
        }

        return $files;
    }

    /**
     * @return list<array{0: string, 1: string}> [module, first namespace segment]
     */
    private function references(string $content): array
    {
        preg_match_all(self::REFERENCE, $content, $matches, PREG_SET_ORDER);

        return array_map(fn (array $match) => [$match[1], $match[2]], $matches);
    }
}
