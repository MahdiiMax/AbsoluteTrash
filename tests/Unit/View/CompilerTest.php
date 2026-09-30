<?php

declare(strict_types=1);

namespace Tests\Unit\View;

use Tests\TestCase;
use Trash\View\Compiler;

class CompilerTest extends TestCase
{
    private Compiler $compiler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->compiler = new Compiler(sys_get_temp_dir() . '/absolute_trash_views_' . uniqid());
    }

    public function test_echo_escaping(): void
    {
        $this->assertStringContainsString(
            '<?php echo e($name); ?>',
            $this->compiler->compileString('{{ $name }}')
        );
    }

    public function test_unescaped_echo(): void
    {
        $this->assertStringContainsString(
            '<?php echo $name; ?>',
            $this->compiler->compileString('{!! $name !!}')
        );
    }

    public function test_comments_are_removed(): void
    {
        $this->assertDoesNotMatchRegularExpression(
            '/comment/',
            $this->compiler->compileString('{{-- comment --}}ok')
        );
    }

    public function test_literal_braces_preserved(): void
    {
        $this->assertStringContainsString(
            '{{ $name }}',
            $this->compiler->compileString('@{{ $name }}')
        );
    }

    public function test_double_at_sign_escapes_directive(): void
    {
        $this->assertStringContainsString('@if', $this->compiler->compileString('@@if ($x)'));
    }

    public function test_if_directive(): void
    {
        $compiled = $this->compiler->compileString('@if ($x)yes@elseif ($y)no@else none@endif');
        $this->assertStringContainsString('<?php if ($x): ?>', $compiled);
        $this->assertStringContainsString('<?php elseif ($y): ?>', $compiled);
        $this->assertStringContainsString('<?php else: ?>', $compiled);
        $this->assertStringContainsString('<?php endif; ?>', $compiled);
    }

    public function test_unless_directive(): void
    {
        $compiled = $this->compiler->compileString('@unless ($x)no@endunless');
        $this->assertStringContainsString('<?php if (!($x)): ?>', $compiled);
        $this->assertStringContainsString('<?php endif; ?>', $compiled);
    }

    public function test_foreach_builds_loop_object(): void
    {
        $compiled = $this->compiler->compileString(
            '@foreach ($items as $item){{ $item }}@endforeach'
        );
        $this->assertStringContainsString('foreach (($__loopData0 = $items) as $__k => $item):', $compiled);
        $this->assertStringContainsString('new \Trash\View\Loop(', $compiled);
        $this->assertStringContainsString('<?php endforeach; ?>', $compiled);
    }

    public function test_foreach_with_key(): void
    {
        $compiled = $this->compiler->compileString('@foreach ($items as $key => $item)@endforeach');
        $this->assertStringContainsString('as $key => $item):', $compiled);
    }

    public function test_forelse_directive(): void
    {
        $compiled = $this->compiler->compileString(
            '@forelse ($items as $item){{ $item }}@empty none@endforelse'
        );
        $this->assertStringContainsString('$__empty0 = true;', $compiled);
        $this->assertStringContainsString('if ($__empty0):', $compiled);
        $this->assertStringContainsString('<?php endif; ?>', $compiled);
    }

    public function test_for_while_directives(): void
    {
        $compiled = $this->compiler->compileString(
            '@for ($i = 0; $i < 3; $i++)x@endfor@while ($x) y @endwhile'
        );
        $this->assertStringContainsString('<?php for ($i = 0; $i < 3; $i++): ?>', $compiled);
        $this->assertStringContainsString('<?php endfor; ?>', $compiled);
        $this->assertStringContainsString('<?php while ($x): ?>', $compiled);
        $this->assertStringContainsString('<?php endwhile; ?>', $compiled);
    }

    public function test_break_and_continue(): void
    {
        $this->assertStringContainsString('<?php break; ?>', $this->compiler->compileString('@break'));
        $this->assertStringContainsString('<?php break 2; ?>', $this->compiler->compileString('@break(2)'));
        $this->assertStringContainsString('<?php continue; ?>', $this->compiler->compileString('@continue'));
        $this->assertStringContainsString('<?php continue 2; ?>', $this->compiler->compileString('@continue(2)'));
    }

    public function test_php_block(): void
    {
        $this->assertStringContainsString(
            '<?php $x = 1; ?>',
            $this->compiler->compileString('@php($x = 1)')
        );
        $this->assertStringContainsString(
            '<?php $x = 1; ?>',
            $this->compiler->compileString('@php $x = 1; @endphp')
        );
    }

    public function test_csrf_directive(): void
    {
        $this->assertStringContainsString(
            '<?php echo csrf_field(); ?>',
            $this->compiler->compileString('@csrf')
        );
    }

    public function test_method_directive(): void
    {
        $this->assertStringContainsString(
            'value="PUT"',
            $this->compiler->compileString("@method('PUT')")
        );
        $this->assertStringContainsString(
            'value="DELETE"',
            $this->compiler->compileString('@method("DELETE")')
        );
    }

    public function test_include_directive(): void
    {
        $compiled = $this->compiler->compileString("@include('partial', ['x' => 1])");
        $this->assertStringContainsString('$__env->make(', $compiled);
        $this->assertStringContainsString('array_merge(get_defined_vars(), [\'x\' => 1])', $compiled);
    }

    public function test_extends_yield_section(): void
    {
        $compiled = $this->compiler->compileString(
            "@extends('layout')\n@section('title', 'Hi')\n@yield('content', 'default')"
        );
        $this->assertStringContainsString('$__env->make(\'layout\', get_defined_vars())->render();', $compiled);
        $this->assertStringContainsString('$__env->startSection(\'title\', \'Hi\');', $compiled);
        $this->assertStringContainsString('$__env->yieldContent(\'content\', \'default\');', $compiled);
    }

    public function test_nested_parentheses_preserved(): void
    {
        $compiled = $this->compiler->compileString('@if (array_key_exists(\'a.b\', $x))@endif');
        $this->assertStringContainsString(
            '<?php if (array_key_exists(\'a.b\', $x)): ?>',
            $compiled
        );
    }

    public function test_compile_writes_compiled_file(): void
    {
        $dir = sys_get_temp_dir() . '/absolute_trash_views_' . uniqid();
        mkdir($dir, 0777, true);
        $template = $dir . '/template.blade.php';
        file_put_contents($template, '{{ $greeting }}');
        $compiler = new Compiler($dir . '/cache');
        $compiled = $compiler->compile($template);
        $this->assertFileExists($compiled);
        $this->assertStringContainsString('<?php echo e($greeting); ?>', (string) file_get_contents($compiled));
    }

    public function test_custom_directive(): void
    {
        $compiler = $this->compiler;
        $compiler->directive('greet', fn(string $e) => "<?php echo 'hi ' . {$e}; ?>");
        $compiled = $compiler->compileString("@greet('world')");
        $this->assertStringContainsString("<?php echo 'hi ' . 'world'; ?>", $compiled);
    }
}
