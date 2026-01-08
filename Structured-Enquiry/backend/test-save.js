const mongoose = require('mongoose');

console.log('🧪 Testing MongoDB Save Operation...\n');

// Connect to MongoDB
mongoose.connect('mongodb://127.0.0.1:27017/donation_app', {
    useNewUrlParser: true,
    useUnifiedTopology: true
});

// Define User model (same as your User.js)
const userSchema = new mongoose.Schema({
    name: String,
    email: String,
    password: String,
    mobile: String,
    address: String,
    role: String,
    createdAt: { type: Date, default: Date.now }
});

const User = mongoose.model('UserTest', userSchema);

async function testSave() {
    try {
        console.log('1. Attempting to save user...');
        
        // Create test user
        const testUser = new User({
            name: 'Test User ' + Date.now(),
            email: 'test' + Date.now() + '@test.com',
            password: 'test123',
            mobile: '9876543210',
            address: 'Test Address',
            role: 'user'
        });
        
        // Save to database
        await testUser.save();
        console.log('✅ User saved successfully!');
        console.log('   ID:', testUser._id);
        console.log('   Email:', testUser.email);
        
        // Count users in database
        const count = await User.countDocuments();
        console.log('\n2. Total users in database:', count);
        
        // Find the user we just saved
        const foundUser = await User.findById(testUser._id);
        console.log('\n3. Found user by ID:', foundUser ? 'Yes' : 'No');
        if (foundUser) {
            console.log('   Name:', foundUser.name);
            console.log('   Email:', foundUser.email);
        }
        
        // List all users
        const allUsers = await User.find();
        console.log('\n4. All users in database:');
        allUsers.forEach((user, index) => {
            console.log(`   ${index + 1}. ${user.name} (${user.email})`);
        });
        
        console.log('\n🎉 TEST PASSED! Data is being saved to MongoDB.');
        
    } catch (error) {
        console.error('❌ TEST FAILED:', error.message);
    } finally {
        mongoose.disconnect();
    }
}

testSave();