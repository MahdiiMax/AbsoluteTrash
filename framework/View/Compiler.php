<?php

declare(strict_types=1);

namespace Trash\View;

class Compiler
{
    private array $directives = [];
    private int $loopDepth = 0;

    public function __construct(private string $cachePath)
    {
        $this->directives = [
            'if' => fn(string $e) => "<?php if ($e): ?>",
            'elseif' => fn(string $e) => "<?php elseif ($e): ?>",
            'else' => fn() => '<?php else: ?>',
            'endif' => fn() => '<?php endif; ?>',
            'unless' => fn(string $e) => "<?php if (!($e)): ?>",
            'endunless' => fn() => '<?php endif; ?>',
            'foreach' => $this->compileForeach(...),
            'endforeach' => $this->compileForeachEnd(...),
            'forelse' => $this->compileForelse(...),
            'empty' => $this->compileEmpty(...),
            'endforelse' => fn() => '<?php endif; ?>',
            'for' => fn(string $e) => "<?php for ($e): ?>",
            'endfor' => fn() => '<?php endfor; ?>',
            'while' => fn(string $e) => "<?php while ($e): ?>",
            'endwhile' => fn() => '<?php endwhile; ?>',
            'continue' => fn(string $e) => $e === '' ? '<?php continue; ?>' : "<?php continue $e; ?>",
            'break' => fn(string $e) => $e === '' ? '<?php break; ?>' : "<?php break $e; ?>",
            'php' => fn(string $e) => $e === '' ? '<?php ?>' : "<?php $e; ?>",
            'include' => $this->compileInclude(...),
            'extends' => fn(string $e) => '<?php $__env->setLayout(' . $e . '); ?>',
            'section' => $this->compileSection(...),
            'endsection' => fn() => '<?php $__env->stopSection(); ?>',
            'stop' => fn() => '<?php $__env->stopSection(); ?>',
            'yield' => $this->compileYield(...)
        ];
    }

    public function directive(string $name, callable $handler): void
    {
        $this->directives[$name] = $handler;
    }

    public function compileString(string $template): string
    {
        $this->loopDepth = 0;
        $template = $this->protectLiteralBraces($template);
        $template = preg_replace_callback('/\{\{--(.*?)--\}\}/s', fn(): string => '', $template);
        $template = preg_replace_callback('/\{!!(.*?)!!\}/s', fn(array $m): string => '<?php echo ' . trim($m[1]) . '; ?>', $template);
        $template = preg_replace_callback('/\{\{(.*?)\}\}/s', fn(array $m): string => '<?php echo e(' . trim($m[1]) . '); ?>', $template);
        $template = preg_replace_callback('/\x1A(.*?)\x1A/s', fn(array $m): string => '{{' . $m[1] . '}}', $template);
        $template = preg_replace_callback('/@php\s*(.*?)\s*@endphp/s', fn(array $m): string => '<?php ' . trim($m[1]) . ' ?>', $template);
        $template = $this->compileDirectives($template);
        return $template;
    }

    public function compile(string $path): string
    {
        $compiled = $this->compiledPath($path);
        if (is_file($path) && is_file($compiled) && filemtime($compiled) >= filemtime($path)) {
            return $compiled;
        }
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0777, true);
        }
        file_put_contents($compiled, $this->compileString((string)file_get_contents($path)), LOCK_EX);
        return $compiled;
    }

    private function protectLiteralBraces(string $template): string
    {
        return preg_replace_callback('/@\{\{(.*?)\}\}/s', fn(array $m): string => "\x1A" . $m[1] . "\x1A", $template);
    }

    private function compiledPath(string $path): string
    {
        return rtrim($this->cachePath, '/\\') . DIRECTORY_SEPARATOR . sha1($path) . '.php';
    }

    private function compileDirectives(string $template): string
    {
        return preg_replace_callback('/@(@?\w+)(?:\s*\(((?:[^()]++|\((?2)\))*)\))?/', function (array $m): string {
            $name = $m[1];
            if (str_starts_with($name, '@')) {
                return '@' . substr($name, 1);
            }
            $expr = trim($m[2] ?? '');
            $handler = $this->directives[$name] ?? null;
            return $handler !== null ? $handler($expr) : $m[0];
        }, $template);
    }

    private function compileForeach(string $expr): string
    {
        [$items, $as] = preg_split('/\s+as\s+/', $expr, 2);
        [$key, $value] = str_contains($as, '=>')
            ? array_map('trim', explode('=>', $as, 2))
            : ['$__k', $as];
        $depth = $this->loopDepth++;
        $data = '$__loopData' . $depth;
        return '<?php foreach ((' . $data . ' = ' . trim($items) . ') as ' . $key . ' => ' . $value . '): $loop = new \Trash\View\Loop(' . $data . ', ' . $key . '); ?>';
    }

    private function compileForeachEnd(): string
    {
        $this->loopDepth = max(0, $this->loopDepth - 1);
        return '<?php endforeach; ?>';
    }

    private function compileForelse(string $expr): string
    {
        [$items, $as] = preg_split('/\s+as\s+/', $expr, 2);
        [$key, $value] = str_contains($as, '=>')
            ? array_map('trim', explode('=>', $as, 2))
            : ['$__k', $as];
        $depth = $this->loopDepth++;
        $data = '$__loopData' . $depth;
        $empty = '$__empty' . $depth;
        return '<?php ' . $empty . ' = true; foreach ((' . $data . ' = ' . trim($items) . ') as ' . $key . ' => ' . $value . '): ' . $empty . ' = false; $loop = new \Trash\View\Loop(' . $data . ', ' . $key . '); ?>';
    }

    private function compileEmpty(): string
    {
        $this->loopDepth = max(0, $this->loopDepth - 1);
        return '<?php endforeach; if ($__empty' . $this->loopDepth . '): ?>';
    }

    private function compileInclude(string $expr): string
    {
        [$name, $data] = array_pad(array_map('trim', explode(',', $expr, 2)), 2, '[]');
        return '<?php echo $__env->make(' . $name . ', array_merge(get_defined_vars(), ' . $data . '))->render(); ?>';
    }

    private function compileSection(string $expr): string
    {
        [$name, $content] = array_pad(array_map('trim', explode(',', $expr, 2)), 2, null);
        return $content === null
            ? '<?php $__env->startSection(' . $name . '); ?>'
            : '<?php $__env->startSection(' . $name . ', ' . $content . '); ?>';
    }

    private function compileYield(string $expr): string
    {
        [$name, $default] = array_pad(array_map('trim', explode(',', $expr, 2)), 2, "''");
        return '<?php echo $__env->yieldContent(' . $name . ', ' . $default . '); ?>';
    }
}
