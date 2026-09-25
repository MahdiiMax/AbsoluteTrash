<?php

declare(strict_types=1);

namespace Tests\Unit\Validation;

use Tests\TestCase;
use Trash\Validation\Validator;

class ValidatorTest extends TestCase
{
    public function test_passes_and_fails(): void
    {
        $validator = new Validator(['name' => 'Alice'], ['name' => 'required']);
        $this->assertTrue($validator->passes());
        $this->assertFalse($validator->fails());
        $invalid = new Validator(['name' => ''], ['name' => 'required']);
        $this->assertFalse($invalid->passes());
        $this->assertTrue($invalid->fails());
    }

    public function test_required_rejects_null_empty_and_false(): void
    {
        foreach ([null, '', false] as $value) {
            $validator = new Validator(['name' => $value], ['name' => 'required']);
            $this->assertFalse($validator->passes(), "value: " . var_export($value, true));
        }
        foreach ([0, '0', ' ', 'Alice'] as $value) {
            $validator = new Validator(['name' => $value], ['name' => 'required']);
            $this->assertTrue($validator->passes(), "value: " . var_export($value, true));
        }
    }

    public function test_string_rule(): void
    {
        $this->assertTrue((new Validator(['name' => 'Alice'], ['name' => 'string']))->passes());
        $this->assertTrue((new Validator(['name' => ''], ['name' => 'string']))->passes());
        $this->assertFalse((new Validator(['name' => 123], ['name' => 'string']))->passes());
        $this->assertTrue((new Validator(['missing' => 'x'], ['name' => 'string']))->passes());
    }

    public function test_email_rule(): void
    {
        $this->assertTrue((new Validator(['email' => 'a@b.com'], ['email' => 'email']))->passes());
        $this->assertTrue((new Validator(['email' => ''], ['email' => 'email']))->passes());
        $this->assertFalse((new Validator(['email' => 'nope'], ['email' => 'email']))->passes());
    }

    public function test_numeric_rule(): void
    {
        $this->assertTrue((new Validator(['age' => '25'], ['age' => 'numeric']))->passes());
        $this->assertTrue((new Validator(['age' => 25], ['age' => 'numeric']))->passes());
        $this->assertFalse((new Validator(['age' => 'abc'], ['age' => 'numeric']))->passes());
    }

    public function test_boolean_rule(): void
    {
        foreach ([true, false, 0, 1, '0', '1', 'true', 'false'] as $value) {
            $this->assertTrue((new Validator(['flag' => $value], ['flag' => 'boolean']))->passes());
        }
        foreach ([null, 'yes', '', 'on', 2] as $value) {
            $this->assertFalse((new Validator(['flag' => $value], ['flag' => 'boolean']))->passes());
        }
    }

    public function test_min_rule(): void
    {
        $this->assertTrue((new Validator(['name' => 'hello'], ['name' => 'min:5']))->passes());
        $this->assertFalse((new Validator(['name' => 'hi'], ['name' => 'min:5']))->passes());
        $this->assertTrue((new Validator(['age' => 25], ['age' => 'min:18']))->passes());
        $this->assertFalse((new Validator(['age' => 10], ['age' => 'min:18']))->passes());
    }

    public function test_max_rule(): void
    {
        $this->assertTrue((new Validator(['name' => 'hello'], ['name' => 'max:5']))->passes());
        $this->assertFalse((new Validator(['name' => 'hello!'], ['name' => 'max:5']))->passes());
    }

    public function test_in_rule(): void
    {
        $this->assertTrue((new Validator(['role' => 'admin'], ['role' => 'in:admin,user']))->passes());
        $this->assertFalse((new Validator(['role' => 'guest'], ['role' => 'in:admin,user']))->passes());
        $this->assertTrue((new Validator(['role' => ''], ['role' => 'in:admin,user']))->passes());
    }

    public function test_confirmed_rule(): void
    {
        $this->assertTrue((new Validator(
            ['password' => 'secret', 'password_confirmation' => 'secret'],
            ['password' => 'confirmed']
        ))->passes());
        $this->assertFalse((new Validator(
            ['password' => 'secret', 'password_confirmation' => 'other'],
            ['password' => 'confirmed']
        ))->passes());
        $this->assertFalse((new Validator(
            ['password' => 'secret'],
            ['password' => 'confirmed']
        ))->passes());
    }

    public function test_unique_rule_against_database(): void
    {
        $this->assertFalse((new Validator(
            ['email' => 'alice@example.com'],
            ['email' => 'unique:users,email']
        ))->passes());
        $this->assertTrue((new Validator(
            ['email' => 'bob@example.com'],
            ['email' => 'unique:users,email']
        ))->passes());
        $this->assertTrue((new Validator(['email' => ''], ['email' => 'unique:users,email']))->passes());
    }

    public function test_error_messages(): void
    {
        $validator = new Validator(['name' => 'ab'], ['name' => 'required|min:5']);
        $this->assertSame(
            ['name' => ['The name field must be at least 5 characters.']],
            $validator->errors()
        );

        $required = new Validator([], ['email' => 'required']);
        $this->assertSame(
            ['email' => ['The email field is required.']],
            $required->errors()
        );

        $in = new Validator(['role' => 'guest'], ['role' => 'in:admin,user']);
        $this->assertSame(
            ['role' => ['The role field must be one of: admin,user.']],
            $in->errors()
        );

        $unique = new Validator(['email' => 'alice@example.com'], ['email' => 'unique:users,email']);
        $this->assertSame(
            ['email' => ['The email has already been taken.']],
            $unique->errors()
        );
    }

    public function test_rules_may_be_given_as_array(): void
    {
        $validator = new Validator(['name' => ''], ['name' => ['required', 'string']]);
        $this->assertTrue($validator->fails());
        $this->assertSame(
            ['name' => ['The name field is required.']],
            $validator->errors()
        );
    }

    public function test_validated_contains_only_rule_keys_present_in_data(): void
    {
        $validator = new Validator(
            ['name' => 'Alice', 'unrelated' => 'x'],
            ['name' => 'required']
        );
        $this->assertSame(['name' => 'Alice'], $validator->validated());
    }

    public function test_get_data_returns_raw_data(): void
    {
        $data = ['name' => 'Alice', 'unrelated' => 'x'];
        $validator = new Validator($data, ['name' => 'required']);
        $this->assertSame($data, $validator->getData());
    }

    public function test_unknown_rule_is_ignored(): void
    {
        $this->assertTrue((new Validator(['name' => 'Alice'], ['name' => 'not_a_real_rule']))->passes());
    }
}
