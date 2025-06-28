<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250628124129 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contributor_profiles (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, european_id VARCHAR(20) NOT NULL, full_name VARCHAR(255) NOT NULL, company VARCHAR(255) DEFAULT NULL, position VARCHAR(255) DEFAULT NULL, phone VARCHAR(20) NOT NULL, linkedin_profile VARCHAR(255) DEFAULT NULL, languages JSON NOT NULL, expertise_areas JSON NOT NULL, geographic_coverage JSON NOT NULL, verification_status VARCHAR(20) NOT NULL, identity_verified TINYINT(1) NOT NULL, company_verified TINYINT(1) NOT NULL, phone_verified TINYINT(1) NOT NULL, email_verified TINYINT(1) NOT NULL, professional_references INT NOT NULL, total_submissions INT NOT NULL, approved_properties INT NOT NULL, rejected_properties INT NOT NULL, accuracy_rate NUMERIC(5, 2) NOT NULL, avg_processing_time NUMERIC(4, 1) NOT NULL, contribution_score INT NOT NULL, tier VARCHAR(20) NOT NULL, badges JSON NOT NULL, total_earnings NUMERIC(10, 2) NOT NULL, free_vip_promotions INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', verified_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', last_activity_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_BDF60006DF001023 (european_id), UNIQUE INDEX UNIQ_BDF60006A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contributor_rewards (id INT AUTO_INCREMENT NOT NULL, contributor_id INT NOT NULL, related_property_id INT DEFAULT NULL, related_submission_id INT DEFAULT NULL, referred_contributor_id INT DEFAULT NULL, approved_by INT DEFAULT NULL, type VARCHAR(30) NOT NULL, status VARCHAR(20) NOT NULL, amount NUMERIC(10, 2) NOT NULL, currency VARCHAR(3) NOT NULL, description VARCHAR(255) NOT NULL, notes LONGTEXT DEFAULT NULL, transaction_id VARCHAR(255) DEFAULT NULL, metadata JSON DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', approved_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', paid_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_2CEDDE9D7A19A357 (contributor_id), INDEX IDX_2CEDDE9D9F796D68 (related_property_id), INDEX IDX_2CEDDE9D9AF83AC (related_submission_id), INDEX IDX_2CEDDE9DDAE7B0D1 (referred_contributor_id), INDEX IDX_2CEDDE9D4EA3CB3D (approved_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE property_submissions (id INT AUTO_INCREMENT NOT NULL, submitted_by_id INT NOT NULL, type_id INT DEFAULT NULL, category_id INT DEFAULT NULL, reviewed_by INT DEFAULT NULL, converted_property_id INT DEFAULT NULL, submission_id VARCHAR(20) NOT NULL, status VARCHAR(30) NOT NULL, title_bg VARCHAR(255) NOT NULL, title_en VARCHAR(255) DEFAULT NULL, title_de VARCHAR(255) DEFAULT NULL, title_ru VARCHAR(255) DEFAULT NULL, description_bg LONGTEXT DEFAULT NULL, description_en LONGTEXT DEFAULT NULL, description_de LONGTEXT DEFAULT NULL, description_ru LONGTEXT DEFAULT NULL, location_bg VARCHAR(255) NOT NULL, location_en VARCHAR(255) DEFAULT NULL, location_de VARCHAR(255) DEFAULT NULL, location_ru VARCHAR(255) DEFAULT NULL, area NUMERIC(10, 2) DEFAULT NULL, price NUMERIC(10, 2) DEFAULT NULL, price_per_sqm NUMERIC(10, 2) DEFAULT NULL, year_built INT DEFAULT NULL, available_from DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', latitude NUMERIC(10, 8) DEFAULT NULL, longitude NUMERIC(11, 8) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, country VARCHAR(2) NOT NULL, ai_confidence_score INT DEFAULT NULL, ai_suggestions JSON DEFAULT NULL, ai_enhancements JSON DEFAULT NULL, ai_suggested_price NUMERIC(10, 2) DEFAULT NULL, duplicate_detected TINYINT(1) NOT NULL, content_quality_score INT DEFAULT NULL, community_upvotes INT NOT NULL, community_downvotes INT NOT NULL, community_rating NUMERIC(3, 2) DEFAULT NULL, admin_notes LONGTEXT DEFAULT NULL, rejection_reason LONGTEXT DEFAULT NULL, required_revisions JSON DEFAULT NULL, submitted_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ai_processed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', community_review_started_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', admin_review_started_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', approved_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', rejected_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_8004A9C1E1FD4933 (submission_id), INDEX IDX_8004A9C179F7D87D (submitted_by_id), INDEX IDX_8004A9C1C54C8C93 (type_id), INDEX IDX_8004A9C112469DE2 (category_id), INDEX IDX_8004A9C185D7FB47 (reviewed_by), UNIQUE INDEX UNIQ_8004A9C1D62EAFDC (converted_property_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE submission_documents (id INT AUTO_INCREMENT NOT NULL, submission_id INT NOT NULL, filename VARCHAR(255) NOT NULL, original_name VARCHAR(255) NOT NULL, mime_type VARCHAR(100) NOT NULL, file_size INT NOT NULL, document_type VARCHAR(50) NOT NULL, description LONGTEXT DEFAULT NULL, document_date DATE DEFAULT NULL, issued_by VARCHAR(100) DEFAULT NULL, is_verified TINYINT(1) NOT NULL, metadata JSON DEFAULT NULL, uploaded_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_processed TINYINT(1) NOT NULL, ai_analysis JSON DEFAULT NULL, extracted_text LONGTEXT DEFAULT NULL, INDEX IDX_120F763CE1FD4933 (submission_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE submission_images (id INT AUTO_INCREMENT NOT NULL, submission_id INT NOT NULL, filename VARCHAR(255) NOT NULL, original_name VARCHAR(255) NOT NULL, mime_type VARCHAR(100) NOT NULL, file_size INT NOT NULL, image_type VARCHAR(50) NOT NULL, description LONGTEXT DEFAULT NULL, is_primary TINYINT(1) NOT NULL, metadata JSON DEFAULT NULL, uploaded_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_processed TINYINT(1) NOT NULL, ai_analysis JSON DEFAULT NULL, INDEX IDX_D81AE3D4E1FD4933 (submission_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE submission_reviews (id INT AUTO_INCREMENT NOT NULL, submission_id INT NOT NULL, reviewer_id INT DEFAULT NULL, type VARCHAR(20) NOT NULL, status VARCHAR(20) NOT NULL, score INT NOT NULL, comments LONGTEXT DEFAULT NULL, metadata JSON DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', reviewed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_459170D9E1FD4933 (submission_id), INDEX IDX_459170D970574616 (reviewer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contributor_profiles ADD CONSTRAINT FK_BDF60006A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE contributor_rewards ADD CONSTRAINT FK_2CEDDE9D7A19A357 FOREIGN KEY (contributor_id) REFERENCES contributor_profiles (id)');
        $this->addSql('ALTER TABLE contributor_rewards ADD CONSTRAINT FK_2CEDDE9D9F796D68 FOREIGN KEY (related_property_id) REFERENCES properties (id)');
        $this->addSql('ALTER TABLE contributor_rewards ADD CONSTRAINT FK_2CEDDE9D9AF83AC FOREIGN KEY (related_submission_id) REFERENCES property_submissions (id)');
        $this->addSql('ALTER TABLE contributor_rewards ADD CONSTRAINT FK_2CEDDE9DDAE7B0D1 FOREIGN KEY (referred_contributor_id) REFERENCES contributor_profiles (id)');
        $this->addSql('ALTER TABLE contributor_rewards ADD CONSTRAINT FK_2CEDDE9D4EA3CB3D FOREIGN KEY (approved_by) REFERENCES users (id)');
        $this->addSql('ALTER TABLE property_submissions ADD CONSTRAINT FK_8004A9C179F7D87D FOREIGN KEY (submitted_by_id) REFERENCES contributor_profiles (id)');
        $this->addSql('ALTER TABLE property_submissions ADD CONSTRAINT FK_8004A9C1C54C8C93 FOREIGN KEY (type_id) REFERENCES property_types (id)');
        $this->addSql('ALTER TABLE property_submissions ADD CONSTRAINT FK_8004A9C112469DE2 FOREIGN KEY (category_id) REFERENCES property_categories (id)');
        $this->addSql('ALTER TABLE property_submissions ADD CONSTRAINT FK_8004A9C185D7FB47 FOREIGN KEY (reviewed_by) REFERENCES users (id)');
        $this->addSql('ALTER TABLE property_submissions ADD CONSTRAINT FK_8004A9C1D62EAFDC FOREIGN KEY (converted_property_id) REFERENCES properties (id)');
        $this->addSql('ALTER TABLE submission_documents ADD CONSTRAINT FK_120F763CE1FD4933 FOREIGN KEY (submission_id) REFERENCES property_submissions (id)');
        $this->addSql('ALTER TABLE submission_images ADD CONSTRAINT FK_D81AE3D4E1FD4933 FOREIGN KEY (submission_id) REFERENCES property_submissions (id)');
        $this->addSql('ALTER TABLE submission_reviews ADD CONSTRAINT FK_459170D9E1FD4933 FOREIGN KEY (submission_id) REFERENCES property_submissions (id)');
        $this->addSql('ALTER TABLE submission_reviews ADD CONSTRAINT FK_459170D970574616 FOREIGN KEY (reviewer_id) REFERENCES users (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contributor_profiles DROP FOREIGN KEY FK_BDF60006A76ED395');
        $this->addSql('ALTER TABLE contributor_rewards DROP FOREIGN KEY FK_2CEDDE9D7A19A357');
        $this->addSql('ALTER TABLE contributor_rewards DROP FOREIGN KEY FK_2CEDDE9D9F796D68');
        $this->addSql('ALTER TABLE contributor_rewards DROP FOREIGN KEY FK_2CEDDE9D9AF83AC');
        $this->addSql('ALTER TABLE contributor_rewards DROP FOREIGN KEY FK_2CEDDE9DDAE7B0D1');
        $this->addSql('ALTER TABLE contributor_rewards DROP FOREIGN KEY FK_2CEDDE9D4EA3CB3D');
        $this->addSql('ALTER TABLE property_submissions DROP FOREIGN KEY FK_8004A9C179F7D87D');
        $this->addSql('ALTER TABLE property_submissions DROP FOREIGN KEY FK_8004A9C1C54C8C93');
        $this->addSql('ALTER TABLE property_submissions DROP FOREIGN KEY FK_8004A9C112469DE2');
        $this->addSql('ALTER TABLE property_submissions DROP FOREIGN KEY FK_8004A9C185D7FB47');
        $this->addSql('ALTER TABLE property_submissions DROP FOREIGN KEY FK_8004A9C1D62EAFDC');
        $this->addSql('ALTER TABLE submission_documents DROP FOREIGN KEY FK_120F763CE1FD4933');
        $this->addSql('ALTER TABLE submission_images DROP FOREIGN KEY FK_D81AE3D4E1FD4933');
        $this->addSql('ALTER TABLE submission_reviews DROP FOREIGN KEY FK_459170D9E1FD4933');
        $this->addSql('ALTER TABLE submission_reviews DROP FOREIGN KEY FK_459170D970574616');
        $this->addSql('DROP TABLE contributor_profiles');
        $this->addSql('DROP TABLE contributor_rewards');
        $this->addSql('DROP TABLE property_submissions');
        $this->addSql('DROP TABLE submission_documents');
        $this->addSql('DROP TABLE submission_images');
        $this->addSql('DROP TABLE submission_reviews');
    }
}
