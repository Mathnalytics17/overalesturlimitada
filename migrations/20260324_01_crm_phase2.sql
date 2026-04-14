CREATE TABLE IF NOT EXISTS crm_leads (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_id BIGINT UNSIGNED NULL,
  full_name VARCHAR(180) NOT NULL,
  email VARCHAR(180) NOT NULL,
  phone VARCHAR(40) NOT NULL,
  source_type VARCHAR(40) NOT NULL,
  channel VARCHAR(40) NOT NULL DEFAULT 'web',
  subject VARCHAR(190) NOT NULL,
  message TEXT NULL,
  package_id BIGINT UNSIGNED NULL,
  package_slug VARCHAR(180) NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'new',
  priority VARCHAR(30) NOT NULL DEFAULT 'medium',
  assigned_admin_user_id BIGINT UNSIGNED NULL,
  whatsapp_opt_in TINYINT(1) NOT NULL DEFAULT 0,
  consent_accepted_at DATETIME NULL,
  metadata_json JSON NULL,
  last_contact_at DATETIME NULL,
  closed_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  deleted_at DATETIME NULL,
  INDEX idx_crm_leads_status (status),
  INDEX idx_crm_leads_source_type (source_type),
  INDEX idx_crm_leads_assigned_admin (assigned_admin_user_id),
  INDEX idx_crm_leads_email (email),
  INDEX idx_crm_leads_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS crm_lead_interactions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  lead_id BIGINT UNSIGNED NOT NULL,
  admin_user_id BIGINT UNSIGNED NULL,
  channel VARCHAR(40) NOT NULL,
  direction VARCHAR(30) NOT NULL,
  event_type VARCHAR(60) NOT NULL,
  message TEXT NOT NULL,
  meta_json JSON NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  INDEX idx_crm_interactions_lead (lead_id),
  INDEX idx_crm_interactions_event (event_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS crm_lead_tasks (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  lead_id BIGINT UNSIGNED NOT NULL,
  admin_user_id BIGINT UNSIGNED NULL,
  title VARCHAR(180) NOT NULL,
  description TEXT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'pending',
  due_at DATETIME NULL,
  completed_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  INDEX idx_crm_tasks_lead (lead_id),
  INDEX idx_crm_tasks_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
