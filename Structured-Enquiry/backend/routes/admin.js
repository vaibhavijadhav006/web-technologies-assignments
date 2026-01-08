const express = require('express');
const router = express.Router();
const { auth, adminAuth } = require('../middleware/auth');
const User = require('../models/User');
const Campaign = require('../models/Campaign');
const Donation = require('../models/Donation');

// @route   GET /api/admin/dashboard
// @desc    Get admin dashboard stats
// @access  Private/Admin
router.get('/dashboard', [auth, adminAuth], async (req, res) => {
    try {
        const [
            totalCampaigns,
            activeCampaigns,
            totalUsers,
            totalDonations,
            recentDonations,
            recentUsers
        ] = await Promise.all([
            Campaign.countDocuments(),
            Campaign.countDocuments({ status: 'active' }),
            User.countDocuments(),
            Donation.countDocuments(),
            Donation.find()
                .sort({ donatedAt: -1 })
                .limit(10)
                .populate('user', 'name email')
                .populate('campaign', 'title'),
            User.find()
                .sort({ createdAt: -1 })
                .limit(10)
                .select('name email role totalDonations createdAt')
        ]);

        // Get total donation amount
        const donationStats = await Donation.aggregate([
            {
                $group: {
                    _id: null,
                    totalAmount: { $sum: '$amount' },
                    avgAmount: { $avg: '$amount' }
                }
            }
        ]);

        // Get category-wise campaign count
        const categoryStats = await Campaign.aggregate([
            {
                $group: {
                    _id: '$category',
                    count: { $sum: 1 },
                    totalAmount: { $sum: '$currentAmount' }
                }
            },
            { $sort: { count: -1 } }
        ]);

        res.json({
            success: true,
            stats: {
                totalCampaigns,
                activeCampaigns,
                totalUsers,
                totalDonations,
                totalAmount: donationStats[0]?.totalAmount || 0,
                avgDonation: donationStats[0]?.avgAmount || 0
            },
            recentDonations,
            recentUsers,
            categoryStats
        });
    } catch (error) {
        console.error('Dashboard error:', error);
        res.status(500).json({ 
            success: false,
            message: 'Server error' 
        });
    }
});

// @route   GET /api/admin/users
// @desc    Get all users
// @access  Private/Admin
router.get('/users', [auth, adminAuth], async (req, res) => {
    try {
        const users = await User.find()
            .select('-password')
            .sort({ createdAt: -1 });
        
        res.json({
            success: true,
            count: users.length,
            users
        });
    } catch (error) {
        console.error('Get users error:', error);
        res.status(500).json({ 
            success: false,
            message: 'Server error' 
        });
    }
});

// @route   PUT /api/admin/users/:id
// @desc    Update user
// @access  Private/Admin
router.put('/users/:id', [auth, adminAuth], async (req, res) => {
    try {
        const { isActive, role } = req.body;
        
        const updateData = {};
        if (isActive !== undefined) updateData.isActive = isActive;
        if (role) updateData.role = role;
        
        const user = await User.findByIdAndUpdate(
            req.params.id,
            { $set: updateData },
            { new: true, runValidators: true }
        ).select('-password');
        
        if (!user) {
            return res.status(404).json({ 
                success: false,
                message: 'User not found' 
            });
        }
        
        res.json({
            success: true,
            message: 'User updated successfully',
            user
        });
    } catch (error) {
        console.error('Update user error:', error);
        res.status(500).json({ 
            success: false,
            message: 'Server error' 
        });
    }
});

// @route   DELETE /api/admin/users/:id
// @desc    Delete user
// @access  Private/Admin
router.delete('/users/:id', [auth, adminAuth], async (req, res) => {
    try {
        const user = await User.findById(req.params.id);
        
        if (!user) {
            return res.status(404).json({ 
                success: false,
                message: 'User not found' 
            });
        }
        
        // Don't allow admin to delete themselves
        if (user._id.toString() === req.userId) {
            return res.status(400).json({ 
                success: false,
                message: 'Cannot delete your own account' 
            });
        }
        
        await user.deleteOne();
        
        res.json({
            success: true,
            message: 'User deleted successfully'
        });
    } catch (error) {
        console.error('Delete user error:', error);
        res.status(500).json({ 
            success: false,
            message: 'Server error' 
        });
    }
});

// @route   GET /api/admin/campaigns
// @desc    Get all campaigns with details
// @access  Private/Admin
router.get('/campaigns', [auth, adminAuth], async (req, res) => {
    try {
        const campaigns = await Campaign.find()
            .populate('createdBy', 'name email')
            .sort({ createdAt: -1 });
        
        res.json({
            success: true,
            count: campaigns.length,
            campaigns
        });
    } catch (error) {
        console.error('Get campaigns error:', error);
        res.status(500).json({ 
            success: false,
            message: 'Server error' 
        });
    }
});

// @route   POST /api/admin/campaigns
// @desc    Create a new campaign
// @access  Private/Admin
router.post('/campaigns', [auth, adminAuth], async (req, res) => {
    try {
        // Get userId from request (set by auth middleware or from body)
        const userId = req.userId || req.body.createdBy;
        
        // Validate required fields
        const { title, description, category, targetAmount, endDate } = req.body;
        
        if (!title || !description || !category || !targetAmount || !endDate) {
            return res.status(400).json({
                success: false,
                message: 'Please provide all required fields'
            });
        }

        // Create campaign
        const campaign = new Campaign({
            title,
            description,
            category,
            targetAmount: parseInt(targetAmount),
            endDate,
            createdBy: userId,
            currentAmount: 0,
            status: 'active'
        });
        
        await campaign.save();
        
        // Populate createdBy field
        await campaign.populate('createdBy', 'name email');
        
        res.status(201).json({
            success: true,
            message: 'Campaign created successfully',
            campaign
        });
    } catch (error) {
        console.error('Create campaign error:', error);
        res.status(500).json({ 
            success: false,
            message: 'Server error: ' + error.message
        });
    }
});

// @route   PUT /api/admin/campaigns/:id
// @desc    Update a campaign
// @access  Private/Admin
router.put('/campaigns/:id', [auth, adminAuth], async (req, res) => {
    try {
        const { title, description, category, targetAmount, endDate, status } = req.body;
        
        const updateData = {};
        if (title) updateData.title = title;
        if (description) updateData.description = description;
        if (category) updateData.category = category;
        if (targetAmount) updateData.targetAmount = parseInt(targetAmount);
        if (endDate) updateData.endDate = endDate;
        if (status) updateData.status = status;
        
        const campaign = await Campaign.findByIdAndUpdate(
            req.params.id,
            { $set: updateData },
            { new: true, runValidators: true }
        ).populate('createdBy', 'name email');
        
        if (!campaign) {
            return res.status(404).json({ 
                success: false,
                message: 'Campaign not found' 
            });
        }
        
        res.json({
            success: true,
            message: 'Campaign updated successfully',
            campaign
        });
    } catch (error) {
        console.error('Update campaign error:', error);
        res.status(500).json({ 
            success: false,
            message: 'Server error: ' + error.message
        });
    }
});

// @route   DELETE /api/admin/campaigns/:id
// @desc    Delete a campaign
// @access  Private/Admin
router.delete('/campaigns/:id', [auth, adminAuth], async (req, res) => {
    try {
        const campaign = await Campaign.findById(req.params.id);
        
        if (!campaign) {
            return res.status(404).json({ 
                success: false,
                message: 'Campaign not found' 
            });
        }
        
        // Delete associated donations
        await Donation.deleteMany({ campaign: req.params.id });
        
        // Delete the campaign
        await campaign.deleteOne();
        
        res.json({
            success: true,
            message: 'Campaign deleted successfully'
        });
    } catch (error) {
        console.error('Delete campaign error:', error);
        res.status(500).json({ 
            success: false,
            message: 'Server error: ' + error.message
        });
    }
});

// @route   GET /api/admin/donations
// @desc    Get all donations
// @access  Private/Admin
router.get('/donations', [auth, adminAuth], async (req, res) => {
    try {
        const donations = await Donation.find()
            .populate('user', 'name email')
            .populate('campaign', 'title')
            .sort({ donatedAt: -1 });
        
        res.json({
            success: true,
            count: donations.length,
            donations
        });
    } catch (error) {
        console.error('Get donations error:', error);
        res.status(500).json({ 
            success: false,
            message: 'Server error' 
        });
    }
});

module.exports = router;