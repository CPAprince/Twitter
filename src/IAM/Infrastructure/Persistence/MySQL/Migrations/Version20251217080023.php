<?php

declare(strict_types=1);

namespace Twitter\IAM\Infrastructure\Persistence\MySQL\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251217080023 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create refresh_tokens table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
CREATE TABLE refresh_tokens (
  id BINARY(16) NOT NULL,
  user_id BINARY(16) NOT NULL,
  token_hash BINARY(32) NOT NULL,
  expires_at DATETIME NOT NULL,
  revoked_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE INDEX uniq_refresh_token_hash (token_hash),
  INDEX idx_refresh_user_id (user_id),
  INDEX idx_refresh_expires_at (expires_at)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci
SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE refresh_tokens');
    }
}
