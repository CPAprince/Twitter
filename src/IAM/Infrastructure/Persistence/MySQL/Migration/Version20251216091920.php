<?php

declare(strict_types=1);

namespace Twitter\IAM\Infrastructure\Persistence\MySQL\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251216091920 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<'SQL'
            CREATE TABLE users (
              email VARCHAR(320) NOT NULL,
              password_hash CHAR(60) NOT NULL,
              roles JSON NOT NULL,
              created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
              email_verified_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
              id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)',
              UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email),
              PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci`
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE users');
    }
}
