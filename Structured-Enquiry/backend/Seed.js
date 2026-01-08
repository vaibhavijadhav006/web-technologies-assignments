const mongoose = require('mongoose');
const bcrypt = require('bcryptjs');

// MongoDB Connection
mongoose.connect('mongodb://127.0.0.1:27017/donation_app', {
    useNewUrlParser: true,
    useUnifiedTopology: true
});

// Define Models
const userSchema = new mongoose.Schema({
    name: String,
    email: { type: String, unique: true },
    password: String,
    mobile: String,
    address: String,
    role: { type: String, default: 'user' },
    totalDonations: { type: Number, default: 0 },
    createdAt: { type: Date, default: Date.now }
});

const campaignSchema = new mongoose.Schema({
    title: String,
    description: String,
    category: String,
    targetAmount: Number,
    currentAmount: { type: Number, default: 0 },
    createdBy: mongoose.Schema.Types.ObjectId,
    donorsCount: { type: Number, default: 0 },
    status: { type: String, default: 'active' },
    endDate: Date,
    createdAt: { type: Date, default: Date.now }
});

const donationSchema = new mongoose.Schema({
    user: mongoose.Schema.Types.ObjectId,
    campaign: mongoose.Schema.Types.ObjectId,
    amount: Number,
    createdAt: { type: Date, default: Date.now }
});

const User = mongoose.model('User', userSchema);
const Campaign = mongoose.model('Campaign', campaignSchema);
const Donation = mongoose.model('Donation', donationSchema);

async function seedDatabase() {
    console.log('🌱 STARTING DATABASE SEEDING...\n');
    
    try {
        // Clear existing data
        console.log('1. Clearing existing data...');
        await User.deleteMany({});
        await Campaign.deleteMany({});
        await Donation.deleteMany({});
        console.log('✅ All data cleared\n');
        
        // Hash passwords
        const salt = await bcrypt.genSalt(10);
        const adminHash = await bcrypt.hash('admin123', salt);
        const userHash = await bcrypt.hash('user123', salt);
        const donorHash = await bcrypt.hash('donor123', salt);
        
        // Create Admin User
        console.log('2. Creating admin user...');
        const admin = new User({
            name: 'Admin User',
            email: 'admin@example.com',
            password: adminHash,
            mobile: '9876543210',
            address: '123 Admin Street, Mumbai',
            role: 'admin'
        });
        await admin.save();
        console.log('✅ Admin created');
        console.log('   Email: admin@example.com');
        console.log('   Password: admin123\n');
        
        // Create Regular Users
        console.log('3. Creating regular users...');
        const users = [
            {
                name: 'John Doe',
                email: 'john@example.com',
                password: userHash,
                mobile: '9876543211',
                address: '456 Main Street, Delhi',
                role: 'user'
            },
            {
                name: 'Sarah Smith',
                email: 'sarah@example.com',
                password: donorHash,
                mobile: '9876543212',
                address: '789 Park Avenue, Bangalore',
                role: 'user'
            },
            {
                name: 'Mike Johnson',
                email: 'mike@example.com',
                password: userHash,
                mobile: '9876543213',
                address: '101 Tech Park, Hyderabad',
                role: 'user'
            }
        ];
        
        const createdUsers = [];
        for (const userData of users) {
            const user = new User(userData);
            await user.save();
            createdUsers.push(user);
            console.log(`✅ ${user.name} created`);
        }
        console.log('');
        
        // Create Campaigns
        console.log('4. Creating campaigns...');
        const campaigns = [
            {
                title: 'Education for Underprivileged Children',
                description: 'Help provide education, books, and uniforms to children from low-income families. Every donation helps a child go to school.',
                category: 'Education',
                targetAmount: 500000,
                createdBy: admin._id,
                endDate: new Date('2024-06-30')
            },
            {
                title: 'Healthcare for Rural Areas',
                description: 'Provide medical camps, medicines, and healthcare services to remote villages without access to proper medical facilities.',
                category: 'Healthcare',
                targetAmount: 300000,
                createdBy: admin._id,
                endDate: new Date('2024-07-15')
            },
            {
                title: 'Disaster Relief - Flood Victims',
                description: 'Emergency aid, food, and shelter for families affected by recent floods. Your donation provides immediate relief.',
                category: 'Disaster Relief',
                targetAmount: 1000000,
                createdBy: admin._id,
                endDate: new Date('2024-08-01')
            },
            {
                title: 'Animal Shelter Support',
                description: 'Food, medical care, and shelter for abandoned and stray animals. Help us provide a safe haven.',
                category: 'Animal Welfare',
                targetAmount: 200000,
                createdBy: admin._id,
                endDate: new Date('2024-09-01')
            },
            {
                title: 'Elderly Care Program',
                description: 'Support for senior citizens with food, medical checkups, and companionship services.',
                category: 'Elderly',
                targetAmount: 250000,
                createdBy: admin._id,
                endDate: new Date('2024-10-01')
            }
        ];
        
        const createdCampaigns = [];
        for (const campaignData of campaigns) {
            const campaign = new Campaign(campaignData);
            await campaign.save();
            createdCampaigns.push(campaign);
            console.log(`✅ ${campaign.title}`);
        }
        console.log('');
        
        // Create Donations
        console.log('5. Creating sample donations...');
        const donations = [
            // John's donations
            { user: createdUsers[0]._id, campaign: createdCampaigns[0]._id, amount: 5000 },
            { user: createdUsers[0]._id, campaign: createdCampaigns[1]._id, amount: 2500 },
            { user: createdUsers[0]._id, campaign: createdCampaigns[2]._id, amount: 10000 },
            
            // Sarah's donations
            { user: createdUsers[1]._id, campaign: createdCampaigns[0]._id, amount: 3000 },
            { user: createdUsers[1]._id, campaign: createdCampaigns[3]._id, amount: 2000 },
            
            // Mike's donations
            { user: createdUsers[2]._id, campaign: createdCampaigns[1]._id, amount: 1500 },
            { user: createdUsers[2]._id, campaign: createdCampaigns[4]._id, amount: 5000 }
        ];
        
        for (const donationData of donations) {
            const donation = new Donation(donationData);
            await donation.save();
            
            // Update user
            await User.findByIdAndUpdate(donationData.user, {
                $inc: { totalDonations: donationData.amount }
            });
            
            // Update campaign
            await Campaign.findByIdAndUpdate(donationData.campaign, {
                $inc: { 
                    currentAmount: donationData.amount,
                    donorsCount: 1 
                }
            });
            
            console.log(`✅ Donation of ₹${donationData.amount} saved`);
        }
        console.log('');
        
        // Display Summary
        console.log('='.repeat(50));
        console.log('📊 DATABASE SEEDING COMPLETE!');
        console.log('='.repeat(50));
        
        const userCount = await User.countDocuments();
        const campaignCount = await Campaign.countDocuments();
        const donationCount = await Donation.countDocuments();
        
        const totalDonations = await Donation.aggregate([
            { $group: { _id: null, total: { $sum: '$amount' } } }
        ]);
        
        console.log(`👥 Users: ${userCount}`);
        console.log(`🎯 Campaigns: ${campaignCount}`);
        console.log(`💰 Donations: ${donationCount}`);
        console.log(`💵 Total Amount Donated: ₹${totalDonations[0]?.total || 0}`);
        
        console.log('\n🔑 LOGIN CREDENTIALS:');
        console.log('Admin:');
        console.log('  Email: admin@example.com');
        console.log('  Password: admin123');
        console.log('\nRegular Users:');
        console.log('  Email: john@example.com / Password: user123');
        console.log('  Email: sarah@example.com / Password: donor123');
        console.log('  Email: mike@example.com / Password: user123');
        
        console.log('\n🚀 Seed completed successfully!');
        
    } catch (error) {
        console.error('❌ SEEDING FAILED:', error.message);
        console.error(error.stack);
    } finally {
        mongoose.disconnect();
        console.log('\n🔌 Disconnected from MongoDB');
    }
}

// Run the seeding
seedDatabase();