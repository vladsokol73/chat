<?php

namespace Database\Seeders;

use App\Models\Chat;
use App\Models\Client;
use App\Models\Integration;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class IntegrationDemoSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create();

        // 1 integration
        /** @var Integration $integration */
        $integration = Integration::query()->create([
            'name' => 'Telegram Demo',
            'service' => 'telegram',
            'token' => 'demo-token-'.Str::random(16),
        ]);

        // 500 clients and 500 chats
        for ($i = 1; $i <= 500; $i++) {
            DB::beginTransaction();
            try {
                $externalClientId = random_int(100_000_000, 999_999_999);

                // Pre-generate UUID to avoid null PK with DB default UUID
                $clientId = Uuid::uuid4()->toString();
                /** @var Client $client */
                $client = Client::query()->create([
                    'id' => $clientId,
                    'integration_id' => $integration->id,
                    'external_id' => $externalClientId,
                    'name' => $faker->name(),
                    'phone' => $faker->e164PhoneNumber(),
                    'avatar' => '',
                    'comment' => null,
                ]);

                // Pre-generate UUID for chat as well
                $chatId = Uuid::uuid4()->toString();
                /** @var Chat $chat */
                $chat = Chat::query()->create([
                    'id' => $chatId,
                    'integration_id' => $integration->id,
                    'client_id' => $client->id,
                    'external_id' => (string) $externalClientId,
                    'channel' => 'telegram',
                    'status' => 'open',
                    'unread_count' => random_int(0, 10),
                ]);

                $messagesCount = random_int(3, 10);
                $lastMessage = null;
                $previousMessageIds = [];

                for ($m = 1; $m <= $messagesCount; $m++) {
                    $direction = ($m % 2 === 0) ? 'out' : 'in';
                    $sentAt = Carbon::now()->subDays(random_int(0, 7))->subMinutes(random_int(0, 60 * 24));

                    $replyToId = null;
                    if (! empty($previousMessageIds) && random_int(0, 100) < 25) {
                        $replyToId = $previousMessageIds[array_rand($previousMessageIds)];
                    }

                    // Pre-generate UUID for message
                    $msgId = Uuid::uuid4()->toString();
                    /** @var Message $msg */
                    $msg = Message::query()->create([
                        'id' => $msgId,
                        'chat_id' => $chat->id,
                        'user_id' => null,
                        'external_message_id' => (string) $m,
                        'direction' => $direction,
                        'type' => 'text',
                        'status' => 'delivered',
                        'text' => $faker->sentence(random_int(3, 12)),
                        'payload' => null,
                        'reply_to_message_id' => $replyToId,
                        'sent_at' => $sentAt,
                        'delivered_at' => (clone $sentAt)->addSeconds(random_int(0, 60)),
                        'read_at' => null,
                    ]);

                    $previousMessageIds[] = $msg->id;
                    $lastMessage = $msg;
                }

                if ($lastMessage !== null) {
                    $chat->update([
                        'last_message_id' => $lastMessage->id,
                        'last_message_at' => $lastMessage->sent_at ?? $lastMessage->created_at,
                    ]);
                }

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                throw $e;
            }
        }
    }
}
