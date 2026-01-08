const sqlite3 = require('sqlite3').verbose();
const bcrypt = require('bcryptjs');
const path = require('path');

// Create database in current directory
const dbPath = path.join(__dirname, 'donation.db');
const db = new sqlite3.Database(dbPath);

console.log('📁 Database path:', dbPath);

// Initialize database
function initializeDatabase() {
  return new Promise((resolve, reject) => {
    db.serialize(() => {
      // 1. Create users table
      db.run(`
        CREATE TABLE IF NOT EXISTS users (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          name TEXT NOT NULL,
          email TEXT UNIQUE NOT NULL,
          password TEXT NOT NULL,
          mobile TEXT NOT NULL,
          address TEXT NOT NULL,
          role TEXT CHECK(role IN ('admin', 'user')) DEFAULT 'user',
          totalDonations REAL DEFAULT 0,
          isActive BOOLEAN DEFAULT 1,
          createdAt DATETIME DEFAULT CURRENT_TIMESTAMP
        )
      `);

      // 2. Create campaigns table
      db.run(`
        CREATE TABLE IF NOT EXISTS campaigns (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          title TEXT NOT NULL,
          description TEXT NOT NULL,
          category TEXT NOT NULL,
          targetAmount REAL NOT NULL,
          currentAmount REAL DEFAULT 0,
          imageUrl TEXT,
          createdBy INTEGER NOT NULL,
          donorsCount INTEGER DEFAULT 0,
          status TEXT CHECK(status IN ('active', 'completed', 'cancelled')) DEFAULT 'active',
          startDate DATETIME DEFAULT CURRENT_TIMESTAMP,
          endDate DATETIME NOT NULL,
          createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
          FOREIGN KEY (createdBy) REFERENCES users(id)
        )
      `);

      // 3. Create donations table
      db.run(`
        CREATE TABLE IF NOT EXISTS donations (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          userId INTEGER NOT NULL,
          campaignId INTEGER NOT NULL,
          amount REAL NOT NULL,
          paymentMethod TEXT DEFAULT 'card',
          status TEXT CHECK(status IN ('pending', 'completed', 'failed')) DEFAULT 'completed',
          transactionId TEXT UNIQUE NOT NULL,
          donatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
          FOREIGN KEY (userId) REFERENCES users(id),
          FOREIGN KEY (campaignId) REFERENCES campaigns(id)
        )
      `);

      // 4. Create volunteers table
      db.run(`
        CREATE TABLE IF NOT EXISTS volunteers (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          userId INTEGER NOT NULL,
          campaignId INTEGER NOT NULL,
          volunteeredAt DATETIME DEFAULT CURRENT_TIMESTAMP,
          FOREIGN KEY (userId) REFERENCES users(id),
          FOREIGN KEY (campaignId) REFERENCES campaigns(id),
          UNIQUE(userId, campaignId)
        )
      `);

      console.log('✅ Tables created successfully');
      resolve();
    });
  });
}

// Insert sample data
async function insertSampleData() {
  return new Promise(async (resolve, reject) => {
    try {
      // Hash passwords
      const adminPassword = await bcrypt.hash('admin123', 10);
      const userPassword = await bcrypt.hash('user123', 10);

      // Insert admin user
      db.run(
        `INSERT OR IGNORE INTO users (name, email, password, mobile, address, role) VALUES (?, ?, ?, ?, ?, ?)`,
        ['Admin User', 'admin@example.com', adminPassword, '9876543210', '123 Admin Street, Admin City', 'admin'],
        function(err) {
          if (err) console.error('Admin insert error:', err);
          else console.log('👑 Admin user created (ID:', this.lastID, ')');
        }
      );

      // Insert regular user
      db.run(
        `INSERT OR IGNORE INTO users (name, email, password, mobile, address) VALUES (?, ?, ?, ?, ?)`,
        ['John Doe', 'user@example.com', userPassword, '9876543211', '456 User Street, User City'],
        function(err) {
          if (err) console.error('User insert error:', err);
          else console.log('👤 Regular user created (ID:', this.lastID, ')');
        }
      );

      // Wait a bit for users to be inserted
      setTimeout(async () => {
        // Get admin ID
        db.get(`SELECT id FROM users WHERE email = 'admin@example.com'`, async (err, admin) => {
          if (err) {
            console.error('Error getting admin:', err);
            reject(err);
            return;
          }

          if (admin) {
            // Insert sample campaigns
            const campaigns = [
              {
                title: 'Education for Underprivileged Children',
                description: 'Provide education and school supplies to children from low-income families',
                category: 'Education',
                targetAmount: 200000,
                currentAmount: 75000,
                createdBy: admin.id,
                endDate: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString(),
                donorsCount: 45
              },
              {
                title: 'Healthcare for Rural Communities',
                description: 'Support medical camps in remote villages',
                category: 'Healthcare',
                targetAmount: 300000,
                currentAmount: 120000,
                createdBy: admin.id,
                endDate: new Date(Date.now() + 45 * 24 * 60 * 60 * 1000).toISOString(),
                donorsCount: 68
              },
              {
                title: 'Disaster Relief for Flood Victims',
                description: 'Provide immediate relief to flood-affected families',
                category: 'Disaster Relief',
                targetAmount: 500000,
                currentAmount: 180000,
                createdBy: admin.id,
                endDate: new Date(Date.now() + 60 * 24 * 60 * 60 * 1000).toISOString(),
                donorsCount: 92
              },
              {
                title: 'Animal Shelter Support',
                description: 'Help provide food and care to stray animals',
                category: 'Animal Welfare',
                targetAmount: 100000,
                currentAmount: 45000,
                createdBy: admin.id,
                endDate: new Date(Date.now() + 90 * 24 * 60 * 60 * 1000).toISOString(),
                donorsCount: 28
              }
            ];

            for (const campaign of campaigns) {
              db.run(
                `INSERT INTO campaigns (title, description, category, targetAmount, currentAmount, createdBy, endDate, donorsCount) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
                [campaign.title, campaign.description, campaign.category, campaign.targetAmount, 
                 campaign.currentAmount, campaign.createdBy, campaign.endDate, campaign.donorsCount],
                function(err) {
                  if (err) console.error('Campaign insert error:', err);
                  else console.log('📢 Campaign created:', campaign.title);
                }
              );
            }

            console.log('\n✅ Database setup complete!');
            console.log('\n📋 Login Credentials:');
            console.log('Admin: admin@example.com / admin123');
            console.log('User: user@example.com / user123');
            console.log('\n🚀 Start your application:');
            console.log('Backend: npm run dev (in backend folder)');
            console.log('Frontend: npm start (in frontend folder)');
            
            resolve();
          }
        });
      }, 1000);
    } catch (error) {
      console.error('Error inserting sample data:', error);
      reject(error);
    }
  });
}

// Export database instance and functions
module.exports = {
  db,
  initializeDatabase,
  insertSampleData
};