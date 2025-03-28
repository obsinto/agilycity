<?php

namespace App\Console\Commands;

use App\Http\Controllers\FixedExpenseController;
use Illuminate\Console\Command;

class GenerateFixedExpenses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-fixed-expenses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gera as despesas fixas do mês atual';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = new FixedExpenseController();
        $result = $controller->generateMonthlyExpenses();

        $this->info($result);

        return Command::SUCCESS;
    }
}
