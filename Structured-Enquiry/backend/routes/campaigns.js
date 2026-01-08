const express = require('express');
const router = express.Router();
const { auth, adminAuth } = require('../middleware/auth');
const Campaign = require('../models/Campaign');
const User = require('../models/User');
const Donation = require('../models/Donation');

// Get all campaigns
router.get('/', async (req, res) => {
    try {
        const campaigns = await Campaign.find()
            .populate('createdBy', 'name email')
            .sort({ createdAt: -1 });
        
        // Get unique categories
        const categories = await Campaign.distinct('category');
        
        res.json({
            success: true,
            campaigns,
            categories: ['all', ...categories]
        });
    } catch (error) {
        console.error('Get campaigns error:', error);
        res.status(500).json({ 
            success: false,
            message: 'Server error' 
        });
    }
});

// Create campaign (Admin only)
router.post('/', [auth, adminAuth], async (req, res) => {
    try {
        const campaign = new Campaign({
            ...req.body,
            createdBy: req.userId
        });
        
        await campaign.save();
        
        res.status(201).json({
            success: true,
            message: 'Campaign created',
            campaign
        });
    } catch (error) {
        console.error('Create campaign error:', error);
        res.status(500).json({ 
            success: false,
            message: 'Server error' 
        });
    }
});

// Donate to campaign
// Add this route to your campaigns.js
// In backend/routes/campaigns.js, UPDATE the donate function:

router.post('/:id/donate', auth, async (req, res) => {
    try {
        const { amount } = req.body;
        const campaignId = req.params.id;
        const userId = req.userId;

        console.log('💰 Donation attempt:', { userId, campaignId, amount });
        
        // Check campaign exists
        const campaign = await Campaign.findById(campaignId);
        if (!campaign) {
            console.log('❌ Campaign not found:', campaignId);
            return res.status(404).json({ 
                success: false,
                message: 'Campaign not found' 
            });
        }

        // Check user exists
        const user = await User.findById(userId);
        if (!user) {
            console.log('❌ User not found:', userId);
            return res.status(404).json({ 
                success: false,
                message: 'User not found' 
            });
        }

        // Create donation record
        const donation = new Donation({
            user: userId,
            campaign: campaignId,
            amount: parseInt(amount),
            paymentMethod: 'online',
            transactionId: 'TXN' + Date.now(),
            status: 'completed'
        });

        console.log('💾 Saving donation to database...');
        await donation.save();
        console.log('✅ Donation saved:', donation._id);

        // Update campaign
        campaign.currentAmount += parseInt(amount);
        campaign.donorsCount += 1;
        await campaign.save();
        console.log('✅ Campaign updated');

        // Update user
        user.totalDonations += parseInt(amount);
        await user.save();
        console.log('✅ User updated');

        // Get updated user data
        const updatedUser = await User.findById(userId).select('-password');

        res.json({
            success: true,
            message: 'Donation successful! Thank you for your contribution.',
            donation: {
                id: donation._id,
                amount: donation.amount,
                transactionId: donation.transactionId,
                date: donation.donatedAt,
                campaign: campaign.title
            },
            user: updatedUser
        });

    } catch (error) {
        console.error('❌ Donation error:', error);
        res.status(500).json({ 
            success: false,
            message: 'Server error during donation',
            error: error.message 
        });
    }
});

module.exports = router;