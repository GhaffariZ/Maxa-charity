<?php

declare(strict_types=1);

namespace Maksa\Controllers;

use Maksa\Core\Exceptions\ApiException;
use Maksa\Core\Request;
use Maksa\Core\Response;
use Maksa\Core\Validator;
use Maksa\Repositories\TicketRepository;
use Maksa\Repositories\UserRepository;

final class TicketController
{
    // ---- GET /tickets -------------------------------------------------------
    public function index(Request $request): void
    {
        $repo    = new TicketRepository();
        $tickets = $repo->listForUser($request->userId());
        Response::success(['tickets' => $tickets, 'total' => count($tickets)]);
    }

    // ---- POST /tickets ------------------------------------------------------
    public function create(Request $request): void
    {
        $data = (new Validator($request->body))
            ->string('subject',  min: 3, max: 200)
            ->string('message',  min: 5, max: 5000)
            ->in('category', ['general', 'donation', 'technical', 'campaign', 'other'], required: false)
            ->in('priority', ['low', 'normal', 'high'], required: false)
            ->string('contact', max: 255, required: false)
            ->validated();

        $userId = $request->userId();

        // Pull display name from the user's profile
        $userRepo = new UserRepository();
        $profile  = $userRepo->findById($userId);
        $name     = trim((string) ($profile['full_name'] ?? $profile['username'] ?? ''));
        if ($name === '') {
            $name = 'حامی #' . $userId;
        }

        $repo = new TicketRepository();
        $id   = $repo->create(
            userId:   $userId,
            name:     $name,
            subject:  $data['subject'],
            message:  $data['message'],
            category: $data['category'] ?? 'general',
            priority: $data['priority'] ?? 'normal',
            contact:  $data['contact']  ?? null,
        );

        Response::success([
            'message' => 'تیکت شما با موفقیت ثبت شد و پس از بررسی پاسخ داده می‌شود.',
            'id'      => $id,
        ], 201);
    }

    // ---- GET /tickets/{id} --------------------------------------------------
    public function show(Request $request): void
    {
        $ticketId = (int) ($request->params['id'] ?? 0);
        if ($ticketId <= 0) {
            throw ApiException::badRequest('شناسه‌ی تیکت نامعتبر است.', 'invalid_id');
        }

        $repo   = new TicketRepository();
        $ticket = $repo->findForUser($ticketId, $request->userId());
        if ($ticket === null) {
            throw ApiException::notFound('تیکت یافت نشد.', 'ticket_not_found');
        }

        Response::success(['ticket' => $ticket]);
    }

    // ---- POST /tickets/{id}/reply ------------------------------------------
    public function reply(Request $request): void
    {
        $ticketId = (int) ($request->params['id'] ?? 0);
        if ($ticketId <= 0) {
            throw ApiException::badRequest('شناسه‌ی تیکت نامعتبر است.', 'invalid_id');
        }

        $data = (new Validator($request->body))
            ->string('message', min: 1, max: 5000)
            ->validated();

        $repo    = new TicketRepository();
        $replyId = $repo->addUserReply($ticketId, $request->userId(), $data['message']);

        if ($replyId === null) {
            throw ApiException::badRequest('تیکت یافت نشد یا بسته است.', 'ticket_not_available');
        }

        Response::success(['message' => 'پیام شما ثبت شد.', 'reply_id' => $replyId], 201);
    }
}
