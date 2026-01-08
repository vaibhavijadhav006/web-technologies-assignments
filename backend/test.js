const mongoose = require('mongoose');

console.log('🔄 Testing MongoDB connection...');

// Direct connection - NO PASSWORD, NO COMPLEX SETTINGS
mongoose.connect('mongodb://127.0.0.1:27017/donation_app')
    .then(() => {
        console.log('✅ MongoDB Connected!');
        
        // Simple test
        const testSchema = new mongoose.Schema({
            name: String,
            email: String
        });
        
        const Test = mongoose.model('Test', testSchema);
        
        const testDoc = new Test({
            name: 'Test Document',
            email: 'test@test.com'
        });
        
        return testDoc.save();
    })
    .then(savedDoc => {
        console.log('✅ Document saved to database!');
        console.log('Document ID:', savedDoc._id);
        
        // Count documents
        return mongoose.connection.db.collection('tests').countDocuments();
    })
    .then(count => {
        console.log('✅ Total test documents:', count);
        mongoose.disconnect();
        console.log('🎉 TEST PASSED! MongoDB is working.');
        process.exit(0);
    })
    .catch(error => {
        console.error('❌ ERROR:', error.message);
        console.log('\n💡 Troubleshooting:');
        console.log('1. Run: mongod --dbpath "C:/data/db"');
        console.log('2. Make sure port 27017 is free');
        process.exit(1);
    });