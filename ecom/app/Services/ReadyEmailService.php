<?php

namespace App\Services;

use App\Models\ReadyEmail;
use App\Repositories\Interfaces\ReadyEmailRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Mail;

class ReadyEmailService
{
    public function __construct(
        protected ReadyEmailRepositoryInterface $readyEmailRepository,
    ) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->readyEmailRepository->paginate($filters, $perPage);
    }

    public function findById(int $id): ReadyEmail
    {
        return $this->readyEmailRepository->findById($id);
    }

    public function update(int $id, array $data): ReadyEmail
    {
        return $this->readyEmailRepository->update($id, $data);
    }

    public function send(int $id): bool
    {
        $readyEmail = $this->readyEmailRepository->findById($id);
        $customer = $readyEmail->customer;

        if (!$customer || !$customer->email) {
            throw new \RuntimeException("No email address found for this ready email.");
        }

        Mail::html($readyEmail->email, function ($message) use ($readyEmail, $customer) {
            $message->to($customer->email)
                    ->subject($readyEmail->title);
        });

        return $this->readyEmailRepository->markAsSent($id);
    }

    public function sendBulk(array $ids): int
    {
        $sent = 0;
        foreach ($ids as $id) {
            try {
                $this->send($id);
                $sent++;
            } catch (\Exception $e) {
                \Log::error("Failed to send ready email {$id}: {$e->getMessage()}");
            }
        }
        return $sent;
    }
}