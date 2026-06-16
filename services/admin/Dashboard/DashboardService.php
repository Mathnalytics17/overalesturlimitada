<?php

namespace app\Services\Admin\Dashboard;

use app\Models\Lead;
use app\Models\SalesOpportunity;
use app\Models\SalesPayment;
use app\Models\SalesOrder;
use app\Models\TourPackage;
use app\Models\PqrsCase;

class DashboardService
{
    public function getStats(): array
    {
        $leadsToday = Lead::countToday();
        $leadsYesterday = Lead::countYesterday();

        $leadsMonth = Lead::countThisMonth();
        $leadsLastMonth = Lead::countLastMonth();

        $wonMonth = SalesOpportunity::countWonThisMonth();
        $wonLastMonth = SalesOpportunity::countWonLastMonth();

        $verifiedMonth = SalesPayment::sumVerifiedThisMonth();
        $verifiedLastMonth = SalesPayment::sumVerifiedLastMonth();

        return [
            'leads_today' => [
                'value' => $leadsToday,
                'compare_label' => 'vs ayer',
                'compare_value' => $leadsToday - $leadsYesterday,
            ],
            'leads_month' => [
                'value' => $leadsMonth,
                'compare_label' => 'vs mes pasado',
                'compare_value' => $leadsMonth - $leadsLastMonth,
            ],
            'active_opportunities' => [
                'value' => SalesOpportunity::countActive(),
                'compare_label' => 'activas hoy',
                'compare_value' => 0,
            ],
            'won_month' => [
                'value' => $wonMonth,
                'compare_label' => 'vs mes pasado',
                'compare_value' => $wonMonth - $wonLastMonth,
            ],
            'verified_payments_month' => [
                'value' => $verifiedMonth,
                'compare_label' => 'vs mes pasado',
                'compare_value' => $verifiedMonth - $verifiedLastMonth,
            ],
            'open_pqrs' => [
                'value' => PqrsCase::countOpen(),
                'compare_label' => 'new / en progreso / esperando cliente',
                'compare_value' => 0,
            ],
        ];
    }

    public function getPipelineSummary(): array
    {
        $base = [
            'new' => 0,
            'contacted' => 0,
            'profiled' => 0,
            'quoted' => 0,
            'follow_up' => 0,
            'pending_payment' => 0,
            'payment_reported' => 0,
            'payment_validated' => 0,
            'won' => 0,
            'lost' => 0,
            'cancelled' => 0,
        ];

        $counts = SalesOpportunity::countByStage();

        foreach ($counts as $stage => $count) {
            $base[$stage] = $count;
        }

        return $base;
    }

    public function getAlerts(): array
    {
        return [
            'followups_overdue' => array_map(function ($item) {
                return [
                    'title' => (string)($item->customer_name ?? 'Sin nombre'),
                    'meta' => 'Etapa: ' . (string)($item->sales_stage ?? '') . ' · Seguimiento: ' . (string)($item->next_follow_up_at ?? ''),
                    'url' => '/admin/sales/show?id=' . (int)$item->id,
                    'priority' => 'high',
                ];
            }, SalesOpportunity::followUpsOverdue(5)),

            'unassigned_opportunities' => array_map(function ($item) {
                return [
                    'title' => (string)($item->customer_name ?? 'Sin nombre'),
                    'meta' => 'Interés: ' . (string)($item->interest_type ?? '') . ' · Etapa: ' . (string)($item->sales_stage ?? ''),
                    'url' => '/admin/sales/show?id=' . (int)$item->id,
                    'priority' => 'medium',
                ];
            }, SalesOpportunity::unassigned(5)),

            'payments_pending_validation' => array_map(function ($item) {
                return [
                    'title' => '$' . number_format((float)($item->amount ?? 0), 0, ',', '.') . ' ' . (string)($item->currency ?? 'COP'),
                    'meta' => 'Tipo: ' . (string)($item->payment_kind ?? '') . ' · Ref: ' . (string)($item->payment_reference ?? 'Sin referencia'),
                    'url' => '/admin/sales/show?id=' . (int)($item->sales_opportunity_id ?? 0),
                    'priority' => 'high',
                ];
            }, SalesPayment::pendingValidation(5)),

            'orders_pending_operation' => array_map(function ($item) {
                return [
                    'title' => (string)($item->order_number ?? ''),
                    'meta' => 'Cliente: ' . (string)($item->customer_name ?? '') . ' · Estado: ' . (string)($item->operational_status ?? ''),
                    'url' => '/admin/sales-orders/show?id=' . (int)$item->id,
                    'priority' => 'medium',
                ];
            }, SalesOrder::pendingOperational(5)),

            'open_pqrs' => array_map(function ($item) {
                return [
                    'title' => (string)($item->subject ?? 'PQRS'),
                    'meta' => 'Cliente: ' . (string)($item->full_name ?? '') . ' · Estado: ' . (string)($item->status ?? ''),
                    'url' => '/admin/pqrs/show?id=' . (int)$item->id,
                    'priority' => 'medium',
                ];
            }, PqrsCase::recentOpen(5)),
        ];
    }

    public function getTopProducts(): array
    {
        return [
            'interest_types' => SalesOpportunity::topInterestTypes(5),
            'packages' => TourPackage::topRequestedFromSales(5),
        ];
    }

    public function getFinanceSummary(): array
    {
        return [
            'quoted_total' => SalesOpportunity::sumQuotedOpen(),
            'verified_total' => SalesPayment::sumVerifiedAll(),
            'orders_balance_total' => SalesOrder::sumOpenBalance(),
        ];
    }

    public function getPqrsSummary(): array
    {
        return [
            'open' => PqrsCase::countOpen(),
            'new' => PqrsCase::countByStatus('new'),
            'in_progress' => PqrsCase::countByStatus('in_progress'),
            'waiting_customer' => PqrsCase::countByStatus('waiting_customer'),
            'resolved' => PqrsCase::countByStatus('resolved'),
            'closed' => PqrsCase::countByStatus('closed'),
        ];
    }
}