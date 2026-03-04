# RabbitMQ / Messenger Integration

## Overview

Likes counter updates and Mercure broadcasts were moved from synchronous event subscribers to asynchronous processing via Symfony Messenger and RabbitMQ. The HTTP response no longer waits for DB writes or Mercure HTTP calls; a worker consumes messages and performs the work.

## Architecture

**Bridge pattern:** `ToggleLikeCommandHandler` still dispatches domain events (`TweetWasLiked`, `TweetWasUnliked`) via the Symfony EventDispatcher. A bridge subscriber (`LikeMessageBridgeSubscriber`) translates these into Messenger messages and dispatches them to the bus. The `ToggleLikeCommandHandler` and domain layer remain unchanged.

**Flow:** Toggle like → persist Like entity → dispatch event → bridge emits `UpdateTweetLikesCountMessage` → message bus routes to RabbitMQ → worker consumes → atomic counter update → Mercure broadcast.

## Decisions

### Atomic SQL over ORM

The handler uses `UPDATE tweets SET likes_count = GREATEST(0, likes_count + :delta) WHERE id = :id` instead of loading the entity via Doctrine. Rationale:

- Eliminates race conditions regardless of worker concurrency.
- Avoids Doctrine identity map issues in long-running workers.
- `GREATEST(0, ...)` prevents negative counts on rapid unlikes.

### DLQ and retry

- **Retry:** 3 attempts with exponential backoff (1s, 2s, 4s).
- **Failure transport:** After retries, messages go to the `failed` queue.
- **Retry:** `bin/console messenger:failed:retry`
- **DLQ:** RabbitMQ dead-letter exchange `twitter_dlx` routes rejected/unacked messages to `failed`.

### Worker lifecycle

- `messenger:consume async` runs in the `php-worker` Docker service.
- Options: `--time-limit=3600 --memory-limit=128M` (restart after 1h or 128MB).
- Docker `restart: unless-stopped` handles restarts.

### Integration with Redis

Redis handles cache invalidation (decorators on query handlers) and rate limiting. RabbitMQ handles write serialization for counters. No overlap: Redis guards the read path, RabbitMQ the write path.

## Monitoring

RabbitMQ Prometheus plugin exposes metrics at `:15692`. Prometheus scrapes `rabbitmq:15692` (see `infra/prometheus/prometheus.yml`). Relevant metrics:

- `rabbitmq_queue_messages` — queue depth
- `rabbitmq_queue_messages_unacked` — in-flight
- `rabbitmq_channel_messages_`* — publish/consume rates

Create a Grafana dashboard for queue depth, DLQ size, and throughput.

## Configuration

- **Transport DSN:** `MESSENGER_TRANSPORT_DSN` in `.env` (default: `amqp://app:app@rabbitmq:5672/%2f/messages`).
- **Exchanges:** `twitter` (main), `twitter_dlx` (dead-letter).
- **Queues:** `likes` (async work), `failed` (DLQ).

