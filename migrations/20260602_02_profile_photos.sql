ALTER TABLE customers
  ADD COLUMN profile_photo_path VARCHAR(255) NULL AFTER address;

ALTER TABLE admin_users
  ADD COLUMN profile_photo_path VARCHAR(255) NULL AFTER phone;
