<?php

namespace Database\Seeders;

use App\Models\Rule;
use App\Models\Condition;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RuleSeeder extends Seeder
{
    private $rules = [
        [
            'name' => 'Greater Than',
            'fn' => 'greaterThan(__CONDITION_VALUE__)',
            'enabled' => 1,
        ],
        [
            'name' => 'Greater Than Or Equal',
            'fn' => 'greaterThanOrEqualTo(__CONDITION_VALUE__)',
            'enabled' => 1,
        ],
        [
            'name' => 'Less Than',
            'fn' => 'lessThan(__CONDITION_VALUE__)',
            'enabled' => 1,
        ],
        [
            'name' => 'Less Than Or Equal',
            'fn' => 'lessThanOrEqualTo(__CONDITION_VALUE__)',
            'enabled' => 1,
        ],
        [
            'name' => 'Equal To',
            'fn' => 'equalTo(__CONDITION_VALUE__)',
            'enabled' => 1,
        ],
        [
            'name' => 'Not Equal To',
            'fn' => 'notEqualTo(__CONDITION_VALUE__)',
            'enabled' => 1,
        ],
        [
            'name' => 'String Contains',
            'fn' => 'stringContains(__CONDITION_VALUE__)',
            'enabled' => 1,
        ],
        [
            'name' => 'String Does Not Contain',
            'fn' => 'stringDoesNotContain(__CONDITION_VALUE__)',
            'enabled' => 1,
        ],
        [
            'name' => 'Count of',
            'fn' => 'count(__CONDITION_VALUE__)',
            'enabled' => 1,
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed the rules engine with all available (currently) rules and conditions
        foreach ($this->rules as $rule) {
            Rule::create([
                'name' => $rule['name'],
                'fn' => $rule['fn'],
                'enabled' => $rule['enabled'],
            ]);
        }
    }
}
