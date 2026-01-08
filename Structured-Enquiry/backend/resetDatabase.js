const mongoose = require('mongoose');

async function resetDatabase() {
    try {
        await mongoose.connect('mongodb://127.0.0.1:27017/donation_app', {
            useNewUrlParser: true,
            useUnifiedTopology: true
        });
        
        console.log('✅ Connected to MongoDB');
        
        // Drop database
        await mongoose.connection.db.dropDatabase();
        console.log('🗑️ Database dropped');
        
        // Create collections
        await mongoose.connection.createCollection('users');
        await mongoose.connection.createCollection('campaigns');
        await mongoose.connection.createCollection('donations');
        
        console.log('✅ Collections created');
        console.log('📁 Collections: users, campaigns, donations');
        
        process.exit(0);
    } catch (error) {
        console.error('❌ Error:', error);
        process.exit(1);
    }
}

resetDatabase();