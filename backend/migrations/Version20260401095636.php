<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260401095636 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__events AS SELECT id, title, description, location, event_date, max_participants, is_published, created_at, organizer_id FROM events');
        $this->addSql('DROP TABLE events');
        $this->addSql('CREATE TABLE events (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description CLOB NOT NULL, location VARCHAR(255) NOT NULL, event_date DATETIME NOT NULL, max_participants INTEGER NOT NULL, is_published BOOLEAN DEFAULT 1 NOT NULL, created_at DATETIME NOT NULL, organizer_id INTEGER NOT NULL, CONSTRAINT FK_5387574A876C4DDA FOREIGN KEY (organizer_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO events (id, title, description, location, event_date, max_participants, is_published, created_at, organizer_id) SELECT id, title, description, location, event_date, max_participants, is_published, created_at, organizer_id FROM __temp__events');
        $this->addSql('DROP TABLE __temp__events');
        $this->addSql('CREATE INDEX IDX_5387574A876C4DDA ON events (organizer_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__events AS SELECT id, title, description, event_date, location, max_participants, is_published, created_at, organizer_id FROM events');
        $this->addSql('DROP TABLE events');
        $this->addSql('CREATE TABLE events (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description CLOB NOT NULL, event_date DATETIME NOT NULL, location VARCHAR(255) NOT NULL, max_participants INTEGER NOT NULL, is_published BOOLEAN NOT NULL, created_at DATETIME NOT NULL, organizer_id INTEGER NOT NULL, CONSTRAINT FK_5387574A876C4DDA FOREIGN KEY (organizer_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO events (id, title, description, event_date, location, max_participants, is_published, created_at, organizer_id) SELECT id, title, description, event_date, location, max_participants, is_published, created_at, organizer_id FROM __temp__events');
        $this->addSql('DROP TABLE __temp__events');
        $this->addSql('CREATE INDEX IDX_5387574A876C4DDA ON events (organizer_id)');
    }
}
