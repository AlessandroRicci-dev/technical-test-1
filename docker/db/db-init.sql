-- Creazione dell'utente manuale nel caso non sia stato creato correttamente
CREATE USER IF NOT EXISTS 'user'@'%' IDENTIFIED BY 'password';

-- Assegna tutti i permessi all'utente sul database
GRANT ALL PRIVILEGES ON `project-be-orders`.* TO 'user'@'%';

-- Assicurati che i permessi vengano applicati subito
FLUSH PRIVILEGES;
