ALTER TABLE usuarios
ADD COLUMN rol VARCHAR(20) NOT NULL DEFAULT 'cliente';

UPDATE usuarios
SET rol = 'admin'
WHERE correo = 'minimarketespinal@gmail.com';