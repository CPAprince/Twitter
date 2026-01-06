<?php

declare(strict_types=1);

namespace Twitter\Like\Infrastructure\Persistence\MySQL\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260106092629 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create likes table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE likes (
              created_at DATETIME NOT NULL,
              tweet_id BINARY(16) NOT NULL,
              user_id BINARY(16) NOT NULL,
              INDEX idx_user_id (user_id),
              PRIMARY KEY (tweet_id, user_id),
              FOREIGN KEY (tweet_id) REFERENCES tweets (id) ON DELETE CASCADE,
              FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci`
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE likes');
    }
}
