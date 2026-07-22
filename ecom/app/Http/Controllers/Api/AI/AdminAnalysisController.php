<?php

namespace App\Http\Controllers\Api\AI;

use App\Http\Controllers\Controller;
use App\Http\Requests\AI\CustomerSummaryRequest;
use App\Http\Requests\AI\SearchInquiriesRequest;
use App\Http\Requests\AI\TicketAnalysisRequest;
use App\Http\Requests\AI\TrendsRequest;
use App\Services\AdminAnalysisService;

class AdminAnalysisController extends Controller
{
    public function __construct(
        protected AdminAnalysisService $analysisService,
    ) {}

    public function searchInquiries(SearchInquiriesRequest $req)
    {
        return response()->json(
            $this->analysisService->searchInquiries(
                keyword: $req->keyword,
                category: $req->category,
                dateFrom: $req->date_from,
                dateTo: $req->date_to,
                limit: $req->limit,
            )
        );
    }

    public function customerSummary(CustomerSummaryRequest $req)
    {
        return response()->json(
            $this->analysisService->customerSummary(
                customerId: $req->customer_id,
                email: $req->email,
            )
        );
    }

    public function trends(TrendsRequest $req)
    {
        return response()->json(
            $this->analysisService->trends(
                period: $req->period,
                dateFrom: $req->date_from,
                dateTo: $req->date_to,
            )
        );
    }

    public function ticketAnalysis(TicketAnalysisRequest $req)
    {
        return response()->json(
            $this->analysisService->ticketAnalysis(
                dateFrom: $req->date_from,
                dateTo: $req->date_to,
                category: $req->category,
            )
        );
    }
}
