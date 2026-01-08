CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role ENUM('admin', 'student', 'mentor') NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE student_details (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    school VARCHAR(100),
    standard INT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE mentor_details (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    srn VARCHAR(50),
    contact VARCHAR(15),
    semester INT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    date DATE NOT NULL,
    venue TEXT NOT NULL,
    reporting_time TIME NOT NULL,
    status ENUM('upcoming', 'ongoing', 'completed') DEFAULT 'upcoming',
    event_type ENUM('ideathon', 'hackathon', 'other') DEFAULT 'other',
    INDEX idx_event_type (event_type)
);

CREATE TABLE event_registrations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    competition_id VARCHAR(50),
    team_name VARCHAR(100),
    title VARCHAR(200),
    team_lead_id INT NOT NULL,
    team_member1 VARCHAR(100),
    team_member2 VARCHAR(100),
    team_member3 VARCHAR(100),
    standard INT,
    contact_email VARCHAR(100),
    registration_count INT DEFAULT 0,
    team_number VARCHAR(20),
    mentor_id INT,
    edit_count INT DEFAULT 0,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (team_lead_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (mentor_id) REFERENCES mentor_details(id) ON DELETE SET NULL,

    INDEX idx_competition_id (competition_id)
);

CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL,
    role ENUM('student', 'mentor', 'admin', 'all') DEFAULT 'all',
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_user_role (user_id, role)
);

CREATE TABLE certificates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    registration_id INT NOT NULL,
    participant_id INT NOT NULL,
    certificate_code VARCHAR(50) UNIQUE,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    downloaded BOOLEAN DEFAULT FALSE,

    FOREIGN KEY (registration_id) REFERENCES event_registrations(id) ON DELETE CASCADE,
    FOREIGN KEY (participant_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE participation (
    id INT PRIMARY KEY AUTO_INCREMENT,
    registration_id INT NOT NULL,
    participant_id INT NOT NULL,
    event_id INT NOT NULL,
    attended BOOLEAN DEFAULT FALSE,

    FOREIGN KEY (registration_id) REFERENCES event_registrations(id) ON DELETE CASCADE,
    FOREIGN KEY (participant_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

CREATE TABLE email_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    recipient_email VARCHAR(255) NOT NULL,
    recipient_name VARCHAR(255),
    subject VARCHAR(255) NOT NULL,
    message TEXT,
    status ENUM('sent', 'failed', 'pending') DEFAULT 'pending',
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    error_message TEXT,
    INDEX idx_recipient_email (recipient_email),
    INDEX idx_sent_at (sent_at)
);

