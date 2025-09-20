<?php

namespace App\Mcp\Servers;

use App\Models\Habit;
use Illuminate\Support\Facades\Log;
use Laravel\Mcp\Server;

class EcoTrackerServer extends Server
{
    /**
     * The MCP server's name.
     */
    protected string $name = 'Eco Tracker Server';

    /**
     * The MCP server's version.
     */
    protected string $version = '0.0.1';

    /**
     * The MCP server's instructions for the LLM.
     */
    protected string $instructions = 'Instructions describing how to use the server and its features.';

    /**
     * The tools registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Tool>>
     */
    protected array $tools = [
        //
    ];

    /**
     * The resources registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Resource>>
     */
    protected array $resources = [
        //
    ];

    /**
     * The prompts registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Prompt>>
     */
    protected array $prompts = [
        //
    ];

    public function boot(): void
    {
        // Tool 1: Log a habit
        $this->tool('logHabit', function (string $userId, string $type, string $value, string $date) {
            try {
                $habit = Habit::create([
                    'user_id' => $userId,
                    'type' => $type,
                    'value' => $value,
                    'date' => $date,
                ]);
                return ['success' => true, 'habit_id' => $habit->id];
            } catch (\Exception $e) {
                Log::error('Habit logging failed: ' . $e->getMessage());
                return ['success' => false, 'error' => 'Failed to log habit'];
            }
        })->description('Log a user habit (e.g., transport, meal)');

        // Tool 2: Calculate carbon footprint
        $this->tool('calculateCarbon', function (string $userId, string $startDate, string $endDate) {
            $habits = Habit::where('user_id', $userId)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();
            $totalCarbon = 0;
            foreach ($habits as $habit) {
                if ($habit->type === 'transport' && str_contains($habit->value, 'drove')) {
                    $km = (float) str_replace('drove_', '', $habit->value);
                    $totalCarbon += $km * 0.2; // 0.2kg CO2 per km
                } elseif ($habit->type === 'meal' && $habit->value === 'meat') {
                    $totalCarbon += 5; // 5kg CO2 per meat meal
                }
            }
            return ['total_carbon_kg' => $totalCarbon, 'habits_counted' => $habits->count()];
        })->description('Calculate carbon footprint for a date range');

        // Tool 3: Get eco suggestions (prompt-based)
        $this->prompt('ecoSuggestions', function (string $userId, string $startDate, string $endDate) {
            $carbonData = $this->callTool('calculateCarbon', [$userId, $startDate, $endDate]);
            $carbon = $carbonData['total_carbon_kg'];
            return "Your carbon footprint is {$carbon}kg. Suggestions: Try biking instead of driving to save ~0.2kg CO2/km, or switch one meat meal to vegetarian to save ~5kg CO2.";
        })->description('Generate eco-friendly suggestions based on habits');
    }
}
