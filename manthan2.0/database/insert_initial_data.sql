-- Insert Initial Events for Manthan 2.0
-- Run this AFTER importing schema_changes.sql

-- Insert Ideathon Event
INSERT INTO events (name, date, venue, reporting_time, status, event_type) 
VALUES ('Ideathon', '2026-12-06', 'KLE Technological University Belagavi', '10:00:00', 'upcoming', 'ideathon');

-- Insert Hackathon Event
INSERT INTO events (name, date, venue, reporting_time, status, event_type) 
VALUES ('Hackathon', '2027-01-03', 'KLE Technological University Belagavi', '10:00:00', 'upcoming', 'hackathon');

-- Verify events were inserted
SELECT * FROM events;
