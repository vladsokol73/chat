<?php

namespace App\Repositories;

use App\Models\Chat;
use App\Models\Integration;
use Illuminate\Contracts\Pagination\CursorPaginator;

class ChatRepository
{
    public function findByIntegrationAndExternal(Integration $integration, string $externalId): Chat
    {
        return Chat::query()
            ->where('integration_id', $integration->id)
            ->where('external_id', $externalId)
            ->firstOrFail();
    }

    public function paginateWithCursor(
        ?string $cursor,
        int $limit,
        string $direction = 'down',
        string $sort = 'all',
        ?string $userId = null
    ): CursorPaginator {
        $query = Chat::query()
            ->with(['client', 'lastMessage'])
            ->sort($sort, $userId);

        if ($direction === 'up') {
            $query->orderBy('last_message_at', 'asc')
                ->orderBy('id', 'asc');
        } else {
            $query->orderBy('last_message_at', 'desc')
                ->orderBy('id', 'desc');
        }

        return $query->cursorPaginate(
            perPage: $limit,
            cursor: $cursor
        );
    }
}
