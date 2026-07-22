<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Interfaces\CustomerRepositoryInterface;
use App\Repositories\Interfaces\InquiryRepositoryInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;

class AdminAnalysisService
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected InquiryRepositoryInterface $inquiryRepository,
        protected CustomerRepositoryInterface $customerRepository,
    ) {}

    public function searchInquiries(?string $keyword, ?string $category, ?string $dateFrom, ?string $dateTo, ?int $limit): array
    {
        $filters = array_filter([
            'keyword' => $keyword,
            'category' => $category,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'limit' => $limit,
        ]);

        $result = $this->inquiryRepository->search($filters, $limit ?? 15);

        $inquiries = $result->map(fn ($i) => [
            'id' => $i->id,
            'category' => $i->category,
            'inquiry' => $i->inquiry,
            'created_at' => $i->created_at?->toDateTimeString(),
        ]);

        return [
            'success' => true,
            'total' => $result->total(),
            'inquiries' => $inquiries->toArray(),
        ];
    }

    public function customerSummary(?int $customerId, ?string $email): array
    {
        if ($email) {
            $user = User::where('email', $email)->first();
            if (!$user || !$user->customer) {
                return ['success' => false, 'error' => 'Customer not found with that email.'];
            }
            $customer = $user->customer;
        } else {
            try {
                $customer = $this->customerRepository->findById($customerId);
            } catch (\Exception $e) {
                return ['success' => false, 'error' => 'Customer not found.'];
            }
        }

        $orders = $customer->orders()->get();
        $totalOrders = $orders->count();
        $totalSpent = $orders->sum('total_amount');

        $statusBreakdown = $orders->groupBy(fn ($o) => $o->status->value)
            ->map(fn ($group) => $group->count())
            ->toArray();

        $recentOrders = $orders->sortByDesc('created_at')->take(5)->map(fn ($o) => [
            'id' => $o->id,
            'order_number' => $o->order_number,
            'total_amount' => $o->total_amount,
            'status' => $o->status->value,
            'created_at' => $o->created_at?->toDateTimeString(),
        ])->values()->toArray();

        return [
            'success' => true,
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
            ],
            'summary' => [
                'total_orders' => $totalOrders,
                'total_spent' => round($totalSpent, 2),
                'status_breakdown' => $statusBreakdown,
            ],
            'recent_orders' => $recentOrders,
        ];
    }

    public function trends(?string $period, ?string $dateFrom, ?string $dateTo): array
    {
        $months = 12;
        if ($dateFrom && $dateTo) {
            $start = new \DateTime($dateFrom);
            $end = new \DateTime($dateTo);
            $diff = $start->diff($end);
            $months = max(1, $diff->m + $diff->y * 12);
        }

        $monthly = $this->orderRepository->getMonthlyOrders($months);

        $totalRevenue = $monthly->sum('revenue');
        $totalOrders = $monthly->sum('count');

        if ($dateFrom && $dateTo) {
            $totalRevenue = $this->orderRepository->getRevenueByDateRange($dateFrom, $dateTo);
            $totalOrders = $monthly->sum('count');
        }

        $breakdown = $monthly->map(fn ($row) => [
            'year' => $row->year,
            'month' => $row->month,
            'orders' => $row->count,
            'revenue' => round((float) $row->revenue, 2),
        ])->values()->toArray();

        return [
            'success' => true,
            'period' => $period ?? 'monthly',
            'total_revenue' => round($totalRevenue, 2),
            'total_orders' => $totalOrders,
            'breakdown' => $breakdown,
        ];
    }

    public function ticketAnalysis(?string $dateFrom, ?string $dateTo, ?string $category): array
    {
        $query = $this->inquiryRepository->search(array_filter([
            'category' => $category,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]), 1);

        $totalInquiries = $query->total();
        $byCategory = $this->inquiryRepository->getCategoryCounts($dateFrom, $dateTo)
            ->map(fn ($row) => [
                'category' => $row->category,
                'count' => $row->total,
            ])->toArray();

        $byDate = $this->inquiryRepository->getDailyCounts($dateFrom, $dateTo)
            ->map(fn ($row) => [
                'date' => $row->date,
                'count' => $row->total,
            ])->toArray();

        return [
            'success' => true,
            'total_inquiries' => $totalInquiries,
            'by_category' => $byCategory,
            'by_date' => $byDate,
        ];
    }
}
