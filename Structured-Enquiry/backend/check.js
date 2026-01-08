const mongoose = require('mongoose');

console.log('🔍 Checking MongoDB connection...');

mongoose.connect('mongodb://127.0.0.1:27017/donation_app')
    .then(async () => {
        console.log('✅ Connected to donation_app database');
        
        // List collections
        const collections = await mongoose.connection.db.listCollections().toArray();
        console.log('📚 Collections found:', collections.map(c => c.name));
        
        // Check users
        try {
            const User = mongoose.model('User');
            const count = await User.countDocuments();
            console.log(`👥 Total users in database: ${count}`);
            
            if (count > 0) {
                const users = await User.find().limit(3);
                console.log('📋 Sample users:');
                users.forEach(u => console.log(`  - ${u.name} (${u.email})`));
            }
        } catch (e) {
            console.log('⚠️ User collection might not exist yet');
        }
        
        mongoose.disconnect();
        console.log('✅ Check complete!');
    })
    .catch(err => {
        console.error('❌ Connection failed:', err.message);
    });