<?php

namespace App\Console\Commands;

use App\Models\BoardDetail;
use App\Models\AutomationProject;
use App\Models\PurchaseDetail;
use App\Models\HourDetail;
use App\Models\Project;
use App\Models\Client;
use App\Models\CostCenter;
use Illuminate\Console\Command;

class MigrateHistoricalDataToForeignKeys extends Command
{
    protected $signature = 'migrate:historical-data-to-fks {--table=all : Tabla específica (board_details, automation_projects, purchase_details, hour_details, budgets, all)}';
    protected $description = 'Migra datos históricos TEXT a columnas FK (project_id, client_id, cost_center_id)';

    public function handle()
    {
        $table = $this->option('table');
        
        $this->info('🚀 Iniciando migración de datos históricos a Foreign Keys...');
        $this->newLine();
        
        if ($table === 'all' || $table === 'board_details') {
            $this->migrateBoardDetails();
        }
        
        if ($table === 'all' || $table === 'automation_projects') {
            $this->migrateAutomationProjects();
        }
        
        if ($table === 'all' || $table === 'purchase_details') {
            $this->migratePurchaseDetails();
        }
        
        if ($table === 'all' || $table === 'hour_details') {
            $this->migrateHourDetails();
        }
        
        if ($table === 'all' || $table === 'budgets') {
            $this->migrateBudgets();
        }
        
        $this->newLine();
        $this->info('✅ Migración completada!');
    }
    
    private function migrateBoardDetails()
    {
        $this->info('📋 Migrando board_details...');
        
        $boards = BoardDetail::whereNull('project_id')
                            ->orWhereNull('client_id')
                            ->get();
        
        if ($boards->isEmpty()) {
            $this->warn('  ⏭️  No hay registros pendientes de migrar');
            return;
        }
        
        $bar = $this->output->createProgressBar($boards->count());
        $bar->start();
        
        foreach ($boards as $board) {
            // Migrar project_id
            if (!$board->project_id && $board->proyecto_numero) {
                $project = Project::where('id', trim($board->proyecto_numero))->first();
                if ($project) {
                    $board->project_id = $project->id;
                }
            }
            
            // Migrar client_id
            if (!$board->client_id && $board->cliente) {
                $client = Client::where('nombre', 'LIKE', '%' . trim($board->cliente) . '%')->first();
                if ($client) {
                    $board->client_id = $client->id;
                }
            }
            
            $board->save();
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info('  ✅ board_details migrado');
    }
    
    private function migrateAutomationProjects()
    {
        $this->info('🤖 Migrando automation_projects...');
        
        $projects = AutomationProject::whereNull('project_id')
                                     ->orWhereNull('client_id')
                                     ->get();
        
        if ($projects->isEmpty()) {
            $this->warn('  ⏭️  No hay registros pendientes de migrar');
            return;
        }
        
        $bar = $this->output->createProgressBar($projects->count());
        $bar->start();
        
        foreach ($projects as $ap) {
            // Migrar project_id (usando proyecto_codigo que antes era proyecto_id)
            if (!$ap->project_id && isset($ap->proyecto_codigo)) {
                $project = Project::where('id', trim($ap->proyecto_codigo))->first();
                if ($project) {
                    $ap->project_id = $project->id;
                }
            }
            
            // Migrar client_id
            if (!$ap->client_id && $ap->cliente) {
                $client = Client::where('nombre', 'LIKE', '%' . trim($ap->cliente) . '%')->first();
                if ($client) {
                    $ap->client_id = $client->id;
                }
            }
            
            $ap->save();
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info('  ✅ automation_projects migrado');
    }
    
    private function migratePurchaseDetails()
    {
        $this->info('💰 Migrando purchase_details...');
        
        $purchases = PurchaseDetail::whereNull('cost_center_id')->get();
        
        if ($purchases->isEmpty()) {
            $this->warn('  ⏭️  No hay registros pendientes de migrar');
            return;
        }
        
        $bar = $this->output->createProgressBar($purchases->count());
        $bar->start();
        
        foreach ($purchases as $purchase) {
            // Migrar cost_center_id
            if (!$purchase->cost_center_id && $purchase->cc) {
                $costCenter = CostCenter::where('code', trim($purchase->cc))->first();
                if ($costCenter) {
                    $purchase->cost_center_id = $costCenter->id;
                }
            }
            
            $purchase->save();
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info('  ✅ purchase_details migrado');
    }
    
    private function migrateHourDetails()
    {
        $this->info('⏰ Migrando hour_details...');
        
        $hours = HourDetail::whereNull('project_id')->get();
        
        if ($hours->isEmpty()) {
            $this->warn('  ⏭️  No hay registros pendientes de migrar');
            return;
        }
        
        $bar = $this->output->createProgressBar($hours->count());
        $bar->start();
        
        foreach ($hours as $hour) {
            // Migrar project_id
            if (!$hour->project_id && $hour->proyecto) {
                $project = Project::where('id', trim($hour->proyecto))->first();
                if ($project) {
                    $hour->project_id = $project->id;
                }
            }
            
            $hour->save();
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info('  ✅ hour_details migrado');
    }
    
    private function migrateBudgets()
    {
        $this->info('💼 Migrando budgets...');
        
        $budgets = \App\Models\Budget::whereNull('project_id')
                                     ->orWhereNull('cost_center_id')
                                     ->get();
        
        if ($budgets->isEmpty()) {
            $this->warn('  ⏭️  No hay registros pendientes de migrar');
            return;
        }
        
        $bar = $this->output->createProgressBar($budgets->count());
        $bar->start();
        
        foreach ($budgets as $budget) {
            // Migrar project_id
            if (!$budget->project_id && $budget->nombre_proyecto) {
                $project = Project::where('id', trim($budget->nombre_proyecto))->first();
                if (!$project) {
                    // Intentar buscar por nombre
                    $project = Project::where('name', 'LIKE', '%' . trim($budget->nombre_proyecto) . '%')->first();
                }
                if ($project) {
                    $budget->project_id = $project->id;
                }
            }
            
            // Migrar cost_center_id
            if (!$budget->cost_center_id && $budget->centro_costo) {
                $costCenter = CostCenter::where('code', trim($budget->centro_costo))->first();
                if ($costCenter) {
                    $budget->cost_center_id = $costCenter->id;
                }
            }
            
            $budget->save();
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info('  ✅ budgets migrado');
    }
}
