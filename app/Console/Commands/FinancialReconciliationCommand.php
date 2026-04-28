<?php

namespace App\Console\Commands;

use App\Models\AdminTask;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FinancialReconciliationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:reconcile';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compare shipment totals with payment settlements and flag discrepancies';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting financial reconciliation for shipments created in last 30 days...');

        $discrepancies = [];
        
        // Find shipments where total_amount doesn't match total settled payments
        $shipments = Shipment::query()
            ->where('created_at', '>=', now()->subDays(30))
            ->where('payment_status', 'paid')
            ->get();

        foreach ($shipments as $shipment) {
            $settledAmount = (float) Payment::query()
                ->where('shipment_id', $shipment->id)
                ->where('status', 'settlement')
                ->sum('amount');

            $expectedAmount = (float) $shipment->total_amount;

            if (abs($settledAmount - $expectedAmount) > 0.01) {
                $discrepancies[] = [
                    'shipment_id' => $shipment->id,
                    'tracking_number' => $shipment->tracking_number,
                    'expected' => $expectedAmount,
                    'actual_settled' => $settledAmount,
                    'gap' => $expectedAmount - $settledAmount,
                ];
            }
        }

        if (count($discrepancies) > 0) {
            $this->warn('Found ' . count($discrepancies) . ' financial discrepancies!');
            
            foreach ($discrepancies as $item) {
                $this->createInvestigationTask($item);
            }
        } else {
            $this->info('Reconciliation completed. All shipments are in sync.');
        }

        return Command::SUCCESS;
    }

    private function createInvestigationTask(array $data): void
    {
        $existingTask = AdminTask::query()
            ->where('task_type', 'financial_discrepancy_investigation')
            ->where('status', 'pending')
            ->where('action_data->shipment_id', $data['shipment_id'])
            ->exists();

        if ($existingTask) return;

        $adminId = User::where('role', 'admin')->value('id');

        AdminTask::create([
            'task_type' => 'financial_discrepancy_investigation',
            'title' => 'Gap Keuangan: ' . $data['tracking_number'],
            'description' => sprintf(
                "Selisih ditemukan! Nilai shipment Rp %s, tapi total bayar settled Rp %s. Selisih: Rp %s",
                number_format($data['expected'], 0, ',', '.'),
                number_format($data['actual_settled'], 0, ',', '.'),
                number_format($data['gap'], 0, ',', '.')
            ),
            'assigned_to' => null,
            'created_by' => $adminId ?: 1,
            'status' => 'pending',
            'priority' => 'high',
            'action_data' => $data,
            'notes' => 'Otomatis dibuat oleh system reconciliation command.',
        ]);
    }
}
