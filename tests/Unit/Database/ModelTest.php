<?php

declare(strict_types=1);

namespace Tests\Unit\Database;

use App\Models\Post;
use App\Models\User;
use Tests\TestCase;
use Trash\Database\Exceptions\ModelNotFoundException;

class ModelTest extends TestCase
{
    public function test_table_name_inferred(): void
    {
        $this->assertSame('users', User::getTable());
        $this->assertSame('posts', Post::getTable());
    }

    public function test_find_returns_model(): void
    {
        $user = User::find(1);
        $this->assertInstanceOf(User::class, $user);
        $this->assertTrue($user->exists);
        $this->assertSame('Alice', $user->name);
    }

    public function test_find_missing_returns_null(): void
    {
        $this->assertNull(User::find(999));
    }

    public function test_find_or_fail_throws(): void
    {
        $this->expectException(ModelNotFoundException::class);
        User::findOrFail(999);
    }

    public function test_all_and_where(): void
    {
        $this->assertCount(1, User::all());
        $this->assertSame('Alice', User::where('email', 'alice@example.com')->first()->name);
    }

    public function test_create_fills_id_and_timestamps(): void
    {
        $user = User::create([
            'name'     => 'Bob',
            'email'    => 'bob@example.com',
            'password' => 'secret',
        ]);
        $this->assertTrue($user->exists);
        $this->assertSame(2, $user->id);
        $this->assertNotNull($user->created_at);
        $this->assertSame('Bob', User::find(2)->name);
    }

    public function test_update_persists_changes(): void
    {
        $user = User::find(1);
        $this->assertTrue($user->update(['name' => 'Alicia']));
        $this->assertSame('Alicia', User::find(1)->name);
    }

    public function test_delete_removes_row(): void
    {
        $user = User::find(1);
        $this->assertTrue($user->delete());
        $this->assertFalse($user->exists);
        $this->assertNull(User::find(1));
    }

    public function test_fill_ignores_non_fillable(): void
    {
        $user = new User(['id' => 99, 'name' => 'X', 'admin' => true]);
        $this->assertNull($user->id);
        $this->assertSame('X', $user->name);
        $this->assertNull($user->admin);
    }

    public function test_author_relation(): void
    {
        $post = Post::find(1);
        $this->assertInstanceOf(User::class, $post->author());
        $this->assertSame('Alice', $post->author()->name);
    }
}