<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReadyEmailService;
use Illuminate\Http\Request;

class ReadyEmailController extends Controller
{
    public function __construct(
        protected ReadyEmailService $readyEmailService,
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['email_sent', 'search']);
        $readyEmails = $this->readyEmailService->paginate($filters, 15);

        return view('admin.ready-emails.index', compact('readyEmails'));
    }

    public function edit(int $id)
    {
        $readyEmail = $this->readyEmailService->findById($id);
        return response()->json($readyEmail);
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'email' => 'required|string',
        ]);

        $this->readyEmailService->update($id, $data);

        return redirect()->route('admin.ready-emails.index')->with('success', 'Email updated successfully.');
    }

    public function send(int $id)
    {
        try {
            $this->readyEmailService->send($id);
            return redirect()->route('admin.ready-emails.index')->with('success', 'Email sent successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.ready-emails.index')->with('error', $e->getMessage());
        }
    }

    public function sendBulk(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return redirect()->route('admin.ready-emails.index')->with('error', 'No emails selected.');
        }

        $sent = $this->readyEmailService->sendBulk($ids);

        return redirect()->route('admin.ready-emails.index')
            ->with('success', "{$sent} email(s) sent successfully.");
    }
}