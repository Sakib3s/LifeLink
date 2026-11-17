<?php

declare(strict_types=1);

namespace Tests;

use App\Core\Validator;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase
{
    public function test_required_fails_on_missing_or_empty_value(): void
    {
        $v = new Validator([], ['name' => 'required']);
        self::assertFalse($v->passes());
        self::assertArrayHasKey('name', $v->errors());

        $v = new Validator(['name' => '   '], ['name' => 'required']);
        self::assertFalse($v->passes());
    }

    public function test_nullable_field_may_be_omitted(): void
    {
        $v = new Validator([], ['phone' => 'nullable|string|max:30']);
        self::assertTrue($v->passes());
        self::assertNull($v->validated()['phone']);
    }

    public function test_email_rule(): void
    {
        $v = new Validator(['email' => 'not-an-email'], ['email' => 'required|email']);
        self::assertFalse($v->passes());

        $v = new Validator(['email' => 'donor@lifelink.test'], ['email' => 'required|email']);
        self::assertTrue($v->passes());
    }

    public function test_integer_rule_rejects_decimals_and_letters(): void
    {
        $v = new Validator(['units' => '2.5'], ['units' => 'required|integer']);
        self::assertFalse($v->passes());

        $v = new Validator(['units' => 'two'], ['units' => 'required|integer']);
        self::assertFalse($v->passes());

        $v = new Validator(['units' => '2'], ['units' => 'required|integer']);
        self::assertTrue($v->passes());
    }

    public function test_min_and_max_on_numbers_compare_the_value(): void
    {
        $v = new Validator(['units' => '0'], ['units' => 'required|integer|min:1|max:20']);
        self::assertFalse($v->passes());

        $v = new Validator(['units' => '25'], ['units' => 'required|integer|min:1|max:20']);
        self::assertFalse($v->passes());

        $v = new Validator(['units' => '5'], ['units' => 'required|integer|min:1|max:20']);
        self::assertTrue($v->passes());
    }

    public function test_min_and_max_on_strings_compare_the_length(): void
    {
        $v = new Validator(['name' => 'Al'], ['name' => 'required|string|min:3']);
        self::assertFalse($v->passes());

        $v = new Validator(['name' => 'Alex'], ['name' => 'required|string|min:3|max:10']);
        self::assertTrue($v->passes());
    }

    public function test_in_rule_only_accepts_listed_values(): void
    {
        $v = new Validator(['blood_group' => 'Z+'], ['blood_group' => 'required|in:O-,O+,A-,A+,B-,B+,AB-,AB+']);
        self::assertFalse($v->passes());

        $v = new Validator(['blood_group' => 'O+'], ['blood_group' => 'required|in:O-,O+,A-,A+,B-,B+,AB-,AB+']);
        self::assertTrue($v->passes());
    }

    public function test_confirmed_rule_requires_a_matching_confirmation_field(): void
    {
        $v = new Validator(
            ['password' => 'Password123!', 'password_confirmation' => 'different'],
            ['password' => 'required|confirmed'],
        );
        self::assertFalse($v->passes());

        $v = new Validator(
            ['password' => 'Password123!', 'password_confirmation' => 'Password123!'],
            ['password' => 'required|confirmed'],
        );
        self::assertTrue($v->passes());
    }

    public function test_lat_and_lng_rules_bound_coordinates(): void
    {
        $v = new Validator(['lat' => '95'], ['lat' => 'nullable|lat']);
        self::assertFalse($v->passes());

        $v = new Validator(['lng' => '-200'], ['lng' => 'nullable|lng']);
        self::assertFalse($v->passes());

        $v = new Validator(['lat' => '23.81', 'lng' => '90.41'], ['lat' => 'nullable|lat', 'lng' => 'nullable|lng']);
        self::assertTrue($v->passes());
    }

    public function test_first_error_per_field_wins_and_validated_only_contains_passing_fields(): void
    {
        $v = new Validator(
            ['email' => 'bad', 'name' => 'Alex'],
            ['email' => 'required|email|max:5', 'name' => 'required|string'],
        );
        self::assertFalse($v->passes());
        self::assertCount(1, $v->errors()); // only "email" fails
        self::assertArrayNotHasKey('email', $v->validated());
        self::assertSame('Alex', $v->validated()['name']);
    }
}
