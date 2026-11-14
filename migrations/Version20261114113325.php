<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261114113325 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER INDEX idx_4bde4b4eb03a8386 RENAME TO IDX_9A2A212EB03A8386');
        $this->addSql('ALTER INDEX uniq_403d9ed3642b8210 RENAME TO UNIQ_456B2886642B8210');
        $this->addSql('ALTER INDEX uniq_2de3690c444f97dd RENAME TO UNIQ_9D8A0EB1444F97DD');
        $this->addSql('ALTER INDEX uniq_2de3690c9395c3f3 RENAME TO UNIQ_9D8A0EB19395C3F3');
        $this->addSql('ALTER INDEX uniq_4d6b2f4abe04ea9 RENAME TO UNIQ_AA2E00FBBE04EA9');
        $this->addSql('ALTER INDEX idx_4d6b2f48d9f6d38 RENAME TO IDX_AA2E00FB4DA1E751');
        $this->addSql('DROP INDEX idx_fb1ad3fc9395c3f3');
        $this->addSql('ALTER INDEX uniq_4b9a1f628de820d9 RENAME TO UNIQ_5A59131E8DE820D9');
        $this->addSql('ALTER INDEX uniq_3e9f97018de820d9 RENAME TO UNIQ_E099368E8DE820D9');
        $this->addSql('ALTER INDEX idx_3e9f9701fc6b21f1 RENAME TO IDX_E099368EFC6B21F1');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER INDEX idx_9a2a212eb03a8386 RENAME TO idx_4bde4b4eb03a8386');
        $this->addSql('ALTER INDEX uniq_456b2886642b8210 RENAME TO uniq_403d9ed3642b8210');
        $this->addSql('ALTER INDEX uniq_9d8a0eb1444f97dd RENAME TO uniq_2de3690c444f97dd');
        $this->addSql('ALTER INDEX uniq_9d8a0eb19395c3f3 RENAME TO uniq_2de3690c9395c3f3');
        $this->addSql('ALTER INDEX idx_aa2e00fb4da1e751 RENAME TO idx_4d6b2f48d9f6d38');
        $this->addSql('ALTER INDEX uniq_aa2e00fbbe04ea9 RENAME TO uniq_4d6b2f4abe04ea9');
        $this->addSql('CREATE INDEX idx_fb1ad3fc9395c3f3 ON seller (customer_id)');
        $this->addSql('ALTER INDEX uniq_5a59131e8de820d9 RENAME TO uniq_4b9a1f628de820d9');
        $this->addSql('ALTER INDEX uniq_e099368e8de820d9 RENAME TO uniq_3e9f97018de820d9');
        $this->addSql('ALTER INDEX idx_e099368efc6b21f1 RENAME TO idx_3e9f9701fc6b21f1');
    }
}
