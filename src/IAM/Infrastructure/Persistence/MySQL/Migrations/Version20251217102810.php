<?php

declare(strict_types=1);

namespace Twitter\IAM\Infrastructure\Persistence\MySQL\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251217102810 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE users (
                    roles JSON NOT NULL,
                    email VARCHAR(320) NOT NULL,
                    password_hash VARCHAR(60) NOT NULL,
                    created_at DATETIME NOT NULL,
                    updated_at DATETIME NOT NULL,
                    id CHAR(36) NOT NULL,
                    UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email),
                    PRIMARY KEY (id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci`',
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE users');
    }
}
