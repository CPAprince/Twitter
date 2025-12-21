<?php

declare(strict_types=1);

namespace Twitter\IAM\Infrastructure\Persistence\MySQL\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251221183820 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            <<<'SQL'
            CREATE TABLE profiles (
              name VARCHAR(50) NOT NULL,
              bio VARCHAR(300) DEFAULT NULL,
              updated_at DATETIME DEFAULT NULL,
              user_id BINARY(16) NOT NULL,
              PRIMARY KEY (user_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci`
        SQL
        );
        $this->addSql(
            <<<'SQL'
            ALTER TABLE
              profiles
            ADD
              CONSTRAINT FK_8B308530A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        SQL
        );
        $this->addSql(
            <<<'SQL'
            ALTER TABLE
              users
            CHANGE
              created_at created_at DATETIME NOT NULL,
            CHANGE
              email_verified_at email_verified_at DATETIME DEFAULT NULL,
            CHANGE
              id id BINARY(16) NOT NULL
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE profiles DROP FOREIGN KEY FK_8B308530A76ED395');
        $this->addSql('DROP TABLE profiles');
        $this->addSql(
            <<<'SQL'
            ALTER TABLE
              users
            CHANGE
              created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
            CHANGE
              email_verified_at email_verified_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
            CHANGE
              id id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)'
        SQL
        );
    }
}
